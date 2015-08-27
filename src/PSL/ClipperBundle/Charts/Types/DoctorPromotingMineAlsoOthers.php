<?php
/**
 * Machine Name      = DoctorPromotingMineAlsoOthers
 * Service Name      = clipper.chart.doctor_promoting_mine_also_others
 * Targeted Question = G003Q001
 * Targeted Template = ./src/PSL/ClipperBundle/Resources/views/Charts/doctor_promoting_mine_also_others.html.twig
 */
namespace PSL\ClipperBundle\Charts\Types;

use PSL\ClipperBundle\Entity\LimeSurveyResponse;
use PSL\ClipperBundle\Event\ChartEvent;
use PSL\ClipperBundle\Charts\Types\ChartType;

class DoctorPromotingMineAlsoOthers extends ChartType {

  private $respondent       = array();
  private $respondent_count = 0;
  private $promoting        = array();

  /**
   * Method call to return chart data.
   * @method dataTable
   *
   * @see  ChartType dataTable
   *
   * @param  ChartEvent $event
   *
   * @return array
   *     Google Chart array in Visualization format
   */
  public function dataTable(ChartEvent $event) {
    //prep other attributes
    parent::$decimal_point = 1;

    //extract respondent
    foreach ($event->getData() as $response) {
      //update @var $this->respondent
      $this->extractRespondent($response);
    }
    $this->respondent_count = count($this->respondent);

    //calculate each brands score
    foreach ($this->brands as $brand) {
      $this->calculateBrandScores($brand);
    }

    //sorting
    asort($this->promoting);

    //data formation
    $dataTable = array(
      'cols' => array(
        array(
          'label' => 'Promote brands',
          'type'  => 'string',
        ),
        array(
          'label' => '# of other brands promoted',
          'type'  => 'number',
        ),
        array(
          'type' => 'string',
          'p'    => array('role' => 'annotation'),
        ),
        array(
          'type' => 'string',
          'p'    => array('role' => 'style')
        ),
      ),
      'rows' => array(),
    );
    foreach ($this->promoting as $brand => $score) {
      $dataTable['rows'][] = array(
        'c' => array(
          array('v' => $brand),
          array('v' => $score),
          array('v' => $this->roundingUpValue($score, FALSE, TRUE)),
          array('v' => ''), //color will be set on template
        ),
      );
    }
    return $dataTable;
  }

  /**
   * Method to extract a respondent answer.
   * @method extractRespondent
   *
   * Process will populate
   * - @var $this->respondent
   *
   * Post-format:
   *   $this->respondent
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     TOKEN
   *       BRAND => ANSWER-VALUE
   *       BRAND => ANSWER-VALUE
   *     ...
   *
   * @param  LimeSurveyResponse $response
   *
   * @return void
   */
  private function extractRespondent(LimeSurveyResponse $response) {
    //getting respondent token
    $lstoken = $response->getLsToken();

    //getting answers
    $answers = $response->getResponseDecoded();
    $answers = $this->filterAnswersToQuestionMap($answers, 'int');

    //values assignments
    foreach ($this->brands as $brand) {
      //respondent overall
      if (!isset($this->respondent[$lstoken])) {
        $this->respondent[$lstoken] = array();
      }
      $this->respondent[$lstoken][$brand] = $answers[$brand];
    }
  }

  /**
   * Method to calculate each brand score.
   * @method calculateBrandScores
   *
   * The score doesn't count if respondent votes for the specific brand,
   * but get average in favor of other brands.
   *
   * Process will populate
   * - @var $this->promoting
   *
   * Post-format:
   *   $this->promoting
   *     BRAND
   *       SCORE-VALUE
   *     BRAND
   *       SCORE-VALUE
   *       SCORE-VALUE
   *     ...
   *
   * @param  string $brand
   *
   * @return void
   */
  private function calculateBrandScores($brand) {
    $score = 0;
    foreach ($this->respondent as $token => $brandsAnswer) {
      $promoting = ($brandsAnswer[$brand] > 0 ? 1 : 0);
      $allBrandCount = array_filter($brandsAnswer);
      $allBrandCount = count($allBrandCount);
      $score += ($allBrandCount - $promoting);
    }
    $this->promoting[$brand] = $this->roundingUpValue(($score / $this->respondent_count));
  }
}
