<?php
/**
 * Main Clipper Controller
 */

namespace PSL\ClipperBundle\Controller;

use \stdClass;
use \Exception;
use \DateTime;
use \DateTimeZone;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\VarDumper;
use Symfony\Component\Config\FileLocator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Doctrine\Common\Collections\ArrayCollection;

// custom


/**
 * Rest Controller for Clipper
 */
class ChartsController extends FOSRestController
{
  private static $js_charttype_postfix = '_Chart';
  
  /**
   * /clipper/charts
   *
   * @param ParamFetcher $paramFetcher
   *
   * @QueryParam(name="order_id", default="(empty)", description="FirstQGroup UUID")
   * @QueryParam(name="params", default="(empty)", description="Array of optional filters")
   */
  public function chartsAction(ParamFetcher $paramFetcher)
  {
    $charts = new ArrayCollection();
    $order_id = $paramFetcher->get('order_id');
    $em = $this->container->get('doctrine')->getManager();
    $survey_type = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id)->getFormDataByField('survey_type');
    $survey_type = reset($survey_type);
    $map = $this->container->get('survey_chart_map')->map($survey_type);
    $assembler = $this->container->get('chart_assembler');    
    
    foreach ($map['machine_names'] as $machine_name) {
      try {
        $placeholders = array(
          'dataTable' => $assembler->getChartDataTable($order_id, $machine_name, $survey_type),
          'chartDivId' => uniqid(),
        );
        $charts->add($this->container->get('twig')->render("PSLClipperBundle:Charts:{$machine_name}.html.twig", $placeholders));
      } catch (Exception $e) {
        // Do something, maybe?
      }
    }
    
    return $this->render("PSLClipperBundle:Charts:charts.html.twig", array('charts' => $charts));
  }

  /**
   * Present google charts
   * /clipper/charts/react
   *
   * @param ParamFetcher $paramFetcher
   *
   * @QueryParam(name="order_id", default="(empty)", description="FirstQGroup UUID")
   */
  public function chartsReactAction(ParamFetcher $paramFetcher)
  {
    $order_id = $paramFetcher->get('order_id');
    
    return $this->render("PSLClipperBundle:Charts:charts-react.html.twig", array(
      'order_id' => $order_id,
    ));
  }
  
  /**
   * @ApiDoc(
   *   resource=true,
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     204 = "No Content for the parameters passed"
   *   },
   *  description="json to render all charts",
   *  filters={
   *    {"name"="order_id", "dataType"="string"},
   *    {"name"="chartmachinename", "dataType"="string"},
   *    {"name"="country", "dataType"="string"},
   *    {"name"="region", "dataType"="string"},
   *    {"name"="specialty", "dataType"="string"},
   *  }
   * )
   * 
   * /clipper/charts/reacts
   *
   * @param Request $request the request object
   *
   * @return \Symfony\Component\BrowserKit\Response
   */
  public function postReactAction(Request $request)
  {
    $content = null;
    $code = 200;
    
    try {
      $order_id = $request->request->get('order_id');
      $chartmachinename = $request->request->get('chartmachinename', '');
      $filters = array(
        'country'   => $request->request->get('country', ''),
        'region'    => $request->request->get('region', ''),
        'specialty' => $request->request->get('specialty', ''),
      );
      $charts = new ArrayCollection();
      $em = $this->container->get('doctrine')->getManager();
      $fqg = $em->getRepository('PSLClipperBundle:FirstQGroup')->find($order_id);
      if (!$fqg) {
        throw new Exception("FQG with id [{$order_id}] not found");
      }
      
      $survey_type = current($fqg->getFormDataByField('survey_type'));
      $map = $this->container->get('survey_chart_map')->map($survey_type);
      $assembler = $this->container->get('chart_assembler');

      foreach ($map['machine_names'] as $index => $machine_name) {
        $drilldown = $chartmachinename == $machine_name ? $filters : array();
        $chEvent = $assembler->getChartEvent($order_id, $machine_name, $survey_type, $drilldown);
        $chart = array(
          'chartmachinename' => $machine_name,
          'charttype'        => $machine_name . self::$js_charttype_postfix,
          'drilldown'        => $chEvent->getDrillDown(),
          'filter'           => $chEvent->getFilters(),
          'countTotal'       => $chEvent->getCountTotal(),
          'countFiltered'    => $chEvent->getCountFiltered(),
          'datatable'        => $chEvent->getDataTable(),
          'header'           => 'Maecenas faucibus mollis interdum.',
          'footer'           => 'Cras mattis consectetur purus sit amet fermentum.',
          'titleLong'        => $chEvent->getTitleLong(),
        );
        $charts->add($chart);
      }

      // calculate "Estimated responses at completion" or global quota
      $quotas = $this->container->get('quota_map')->lookupMultiple($fqg->getFormDataByField('markets'), $fqg->getFormDataByField('specialties'));

      $first = $charts->first();
      $content['meta'] = array(
        "projectTitle"      => current($fqg->getFormDataByField('name_full')), 
        "totalResponses"    => $first['countTotal'],
        "quota"             => array_sum($quotas),
        "finalReportReady"  => "2015-10-13 9:00pm EST",
        "introduction"      => "Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.",
        "introImage"        => "/images/nps-calculation.png",
        "conclusion"        => "Sed posuere consectetur est at lobortis.",
        "reportDescriptionTitle" => "NPS - Why it's important and how it's calculated.",
        "reportDescription" => "<ul><li>NPS is a customer loyalty metric developed by (and a registered trademark of) Fred Reichheld, Bain & Company, and Satmetrix. It was introduced by Reichheld in his 2003 Harvard Business Review article \"One Number You Need to Grow\"</li><li>NPS gauges the overall satisfaction and loyalty to a brand</li><li>It is derived by asking one quantitative question: “How likely are you to recommend this brand to a colleague” It is asked on an 11 point scale from 0 (not at all likely) to 10 (extremely likely)</li><li>Based on their rating, customers are then classified into 3 categories:<ul><li>those scoring 0 – 6 are \"detractors\"</li><li>those scoring 7 – 8 are \"passives\"</li><li>those scoring 9-10 are \"promoters\"</li></ul></li><li>NPS is calculated as the difference between the percentage of “promoters” and “detractors” (please see next slide for calculation)</li><li>NPS is expressed as an absolute number lying between -100 (everybody is a detractor) and +100 (everybody is a promoter)</li><li>If you have for example 25% Promoters, 55% Passives and 20% Detractors, the NPS will be +5. A positive NPS (>0) is generally considered as good</li><li>Benefits of using NPS are simplicity, ease of use, quick follow up and can be an indicator of a brands future growth.</li></ul>",
        "appendix" => array(
          array(
            "appendixTitle" => "Loyalty Score - What is it and why it's important",
            "appendixContent" => "<ul><li>The loyalty scores on the following slide are calculated as follows:</li><li>They are derived from the recommendation question, measured on a scale from 0-10<ul><li>a 1 is awarded to all brands which score a 0 - 6 on the recommendation scale</li><li>a 2 is awarded to all brands which score a 7 - 8 on the recommendation scale</li><li>a 3 is awarded to all brands which score a 9 - 10 on the recommendation scale</li><li>Brands which score a 9 or a 10 are awarded additional points. This is due to the idea that loyalty diffuses when a doctor scores multiple brands high on the recommendation scale. So in order to compensate for multiple loyalty, as well as the 3 points awarded as mentioned above, they are awarded up to a further 2 additional points, dependent upon how many other brands are also scored 9 or 10 by that doctor on the recommendation scale.</li><li>If a doctor scores only one brand a 9 or a 10, then we add 2 points divided by 1 ie 2 points to the initial 3. In this case the brand scores 3+2 ie 5</li><li>If a doctor scores only one other brand a 9 or a 10 then we add another 2 points divided by 2 ie 1 point to the initial 3. In this case the brand scores 3+1 ie 4</li><li>and so on for each additional brand promoted</li></ul></li><li>The loyalty score therefore adds insight to the NPS suite.</li><li>This is important as it indicates both the doctors willingness to recommend drugs to colleagues as well as the extent to which this is exclusive to one brand or multiple brands. In effect, a measure of brand loyalty.</li><li>The loyalty scores can range from 1 to 5. Brands with low scores, particularly under 3.0 , will have low loyalty amongst the doctors, and are therefore vulnerable to switching.</li><li>Brands with high scores, especially over 4.0 have high loyalty and are less vulnerable to brand switching</li></ul>"
          ),
        ),
      );  
      $content['charts'] = $charts;
    }
    catch(Exception $e) {
      $content = "{$e->getMessage()} - File [{$e->getFile()}] - Line [{$e->getLine()}]";
      $code = 204;
    }
    
    return new Response($content, $code);
  }
}
