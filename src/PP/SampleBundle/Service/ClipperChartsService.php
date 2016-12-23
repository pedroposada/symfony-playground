<?php
/**
 * PP/SampleBundle/Utils/ClipperChartsService.php
 *
 * Clipper Chart Helper Class
 * This is the class is a General helper to Clipper Chart.
 * 
 * @uses  PP/SampleBundle/Controller/ChartsController.php
 * @uses  PP/SampleBundle/Controller/DownloadsController.php
 *
 * @version 1.0
 * @date 2015-10-09
 **/
namespace PP\SampleBundle\Service;

use \Exception;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

// special requirement
use PP\SampleBundle\Charts\Types\PromVsDetrPromote;

class ClipperChartsService {
  private $container;
  private $em;

  //string : Order ID / FirstQGroup ID
  private $order_id;
  //object : selected FirstQGroup entity
  private $fqg;

  //string : Survey type
  private $survey_type;

  // array : Associative array of drilldown filters
  private $drilldown;
  
  // array : Filter Drilldown to specifc charts only
  private $drilldown_on_specific_chart = FALSE;
  

  // array : List of Survey fields in return
  private $return_survey_fields = array();
  // array : List of Survey attributes in return
  private $return_charts_extra = array();
  // array : List of Survey custom attributes in return
  private $return_charts_custom = array();

  // array : Compile field
  private $fields = array();
  // array : Compile charts
  private $charts = array();
  
  // array : ChartEvent
  private $chEvent;

  private static $customTokenEnclosure = '%%';
  private static $chartEventQueryMap = array(
    'brands'        => 'getBrands',
    'countFiltered' => 'getCountFiltered',
    'countTotal'    => 'getCountTotal',
    'datatable'     => 'getDataTable',
    'drilldown'     => 'getDrillDown',
    'filter'        => 'getFilters',
    'titleLong'     => 'getTitleLong',
  );
  
  private static $js_charttype_postfix = '_Chart';
  /**
   * Cache
   */
  public $use_cache_assember = FALSE;
  public $use_cache_chart = FALSE;

  public function __construct(ContainerInterface $container) 
  {
    $this->container = $container;
    $this->em = $this->container->get('doctrine')->getManager();
  }
  
  /**
   * Method to set order by order-id.
   * @method setOrderID
   *
   * @param  string $order_id
   */
  public function setOrderID($order_id) 
  {
    $fqg = $this->em->getRepository('PPSampleBundle:FirstQGroup')->find($order_id);
    if (empty($fqg)) {
      throw new Exception("FQG with id [{$order_id}] not found");
    }
    $this->order_id = $order_id;
    $this->fqg      = $fqg;
    
    $survey_type = $this->fqg->getFormDataByField('survey_type');
    $survey_type = reset($survey_type);
    if (empty($survey_type)) {
      throw new Exception("FQG with id [{$order_id}] have no Survey type");
    }
    $this->survey_type = $survey_type;
  }
  
  /**
   * Method to set chart Drilldown.
   * @method setDrillDown
   *
   * @param  array $drilldown
   * @param  boolean $apply_to_specific_chart
   */
  public function setDrillDown($drilldown = array(), $apply_to_specific_chart = FALSE) 
  {
    //sanitize drilldown
    $drilldown = array_merge(
      array(
        'country'   => array(),
        'countries' => array(),
        'region'    => array(),
        'specialty' => array(),
        'brand'     => array(),
      ),
      $drilldown
    );
    
    $this->drilldown = (array) $drilldown;
    if (!empty($apply_to_specific_chart)) {
      $this->drilldown_on_specific_chart = (array) $apply_to_specific_chart;      
    }
  }
  
  /**
   * Method to set returning chart's Fields.
   * @method setReturnFields
   *
   * @param  array $return_survey_fields
   */
  public function setReturnFields($return_survey_fields = array())
  {
    $this->return_survey_fields = (array) $return_survey_fields;
  }
  
  /**
   * Method to set returning chart's Extras.
   * @method setReturnChartExtras
   *
   * @param  array $return_charts_extra
   */
  public function setReturnChartExtras($return_charts_extra = array())
  {
    $this->return_charts_extra  = (array) $return_charts_extra;
  }
  
  /**
   * Method to set returning chart's Customs.
   * @method setReturnChartCustoms
   *
   * @param  array $return_charts_custom
   */
  public function setReturnChartCustoms($return_charts_custom = array())
  {
    $this->return_charts_custom = (array) $return_charts_custom;
  }
  
  /**
   * Method to set Cache on Chart assembler level, defaulting to No.
   * @method setCacheUsageOnAssembler
   *
   * @param  boolean $yes
   */
  public function setCacheUsageOnAssembler($yes) {
    
    $this->use_cache_assember = (!empty($yes));
  }
  
  /**
   * Method to set Cache on Chart Type level, defaulting to No.
   * @method setCacheUsageOnChart
   *
   * @param  boolean $yes
   */
  public function setCacheUsageOnChart($yes) {
    
    $this->use_cache_chart = (!empty($yes));
  }
  
  /**
   * Method to get selected order survey type.
   * @method getSurveyType
   *
   * @return string
   */
  public function getSurveyType() 
  {
    if (empty($this->order_id)) {
      throw new Exception("FQG id is undefined");
    }
    return $this->survey_type;
  }

  /**
   * Method to return Chart complete array set based on sets vars.
   * @method getCharts
   *
   * @return array
   */
  public function getCharts()
  {
    // sanitize properties
    $this->prepProperties();
    $this->charts = new ArrayCollection();
    
    // get map
    $map = $this->container->get('survey_chart_map')->map($this->survey_type);
    
    // get assembler
    $assembler = $this->container->get('chart_assembler');
    // apply cache setting
    $assembler->use_cache = $this->use_cache_assember;
    $assembler->setCacheUsage($this->use_cache_chart);
        
    // loop to each charts
    foreach ($map['machine_names'] as $index => $machine_name) {
      // drilldown
      $drilldown = array();
      // get drill down if:
      if (
        // not applied to specific chart, OR
        ($this->drilldown_on_specific_chart === FALSE) 
        ||
        (
          // specific chart and only to current slide
          (!empty($this->drilldown_on_specific_chart)) 
          && 
          (in_array($machine_name, $this->drilldown_on_specific_chart))
        )
      ) {
        $drilldown = $this->drilldown;
      }
      
      // get chart data
      $this->chEvent = $assembler->getChartEvent($this->order_id, $machine_name, $this->survey_type, $drilldown);
      $chart = array('chartmachinename' => $machine_name);
      
      // get chart data; default - extra
      $this->getChartExtraData($chart);
      
      // get chart data; custom
      $this->getChartCustomData($chart, $machine_name);
            
      $this->charts->add($chart);
    } // foreach $map['machine_names'];
    
    // return data
    return array(
      'fields' => $this->fields,
      'charts' => $this->charts,
    );
  }
  
  /**
   * Method to return order Quota.
   * @method getQuotas
   *
   * @return array
   */
  public function getQuotas()
  {
    $markets     = $this->fqg->getFormDataByField('markets');
    $specialties = $this->fqg->getFormDataByField('specialties');
    // calculate "Estimated responses at completion" or global quota
    return $this->container->get('quota_map')->lookupMultiple($markets, $specialties);
  }
  
  /**
   * Method to prepare order survey properties, sanitized to preset vars.
   * @method prepProperties
   *
   * @return void
   */
  private function prepProperties() 
  {
    //defaulting fields
    if (empty($this->return_survey_fields)) {
      $this->return_survey_fields = array(
        'survey_type',
      );
    }

    //defaulting attributes within chart
    if (empty($this->return_charts_extra)) {
      $this->return_charts_extra = array(
        'chartmachinename',
        'drilldown',
        'filter',
        'datatable',
      );
    }
    
    if (!empty($this->return_survey_fields)) {
      foreach ($this->return_survey_fields as $field) {
        $data = $this->fqg->getFormDataByField($field);
        $data = reset($data);
        $this->fields[$field] = $data;
        if ($field == 'survey_type') {
          $this->survey_type = $data;
        }
      }
    }

    if (empty($this->survey_type)) {
      $data = $this->fqg->getFormDataByField('survey_type');
      $data = reset($data);
      $this->survey_type = $data;
    }
  }
  
  /**
   * Method to get chart Extra Data, as per preset var.
   * @method getChartExtraData
   * 
   * @var $this->return_charts_extra
   *
   * @param  array &$chart
   *
   * @return void
   */
  private function getChartExtraData(&$chart)
  {
    if (empty($this->return_charts_extra)) {
      return;
    }
    
    // special requirement
    if ($this->survey_type == 'nps_plus') {
      switch ($chart['chartmachinename']) {
        case 'PromVsDetrPromote':
          if (PromVsDetrPromote::$ignore_brand_other === FALSE) {
            break;
          }
          $brands = $this->chEvent->getBrands();
          $brands[] = PromVsDetrPromote::$brand_other;
          $this->chEvent->setBrands($brands);
          break;
      } // switch
    }
    
    foreach ($this->return_charts_extra as $key) {
      if (isset(self::$chartEventQueryMap[$key])) {
        $f = self::$chartEventQueryMap[$key];
        $chart[$key] = $this->chEvent->$f();
      }
    }
  }
  
  /**
   * Method to get chart Custom Data, as per preset var.
   * @method getChartCustomData
   * 
   * @var $this->return_charts_custom
   *
   * @param  array &$chart
   * @param  string $machine_name
   *
   * @return void
   */
  private function getChartCustomData(&$chart, $machine_name)
  {
    if (empty($this->return_charts_custom)) {
      return;
    }
    
    foreach ($this->return_charts_custom as $key => $mod) {
      $mock_name = self::$customTokenEnclosure . 'machine_name' . self::$customTokenEnclosure;
      if (strpos($mod, $mock_name) !== FALSE) {
        $chart[$key] = str_replace($mock_name, $machine_name, $mod);
        continue; // foreach
      }
      
      $rex = preg_quote(self::$customTokenEnclosure, '/');
      $rex = '/' . $rex . '(.*)' . $rex . '/';
      $mods = array();
      preg_match($rex, $mod, $mods);
      $mods = end($mods);
      if (isset(self::$chartEventQueryMap[$mods])) {
        $mock_name = self::$customTokenEnclosure . $mods . self::$customTokenEnclosure;
        $func = self::$chartEventQueryMap[$mods];
        $data = $this->chEvent->$func();
        $data = str_replace($mock_name, $data, $mod);
        $chart[$key] = $data;
      }
      else {
        $chart[$key] = $mod;
      }      
    } // foreach
  }
  
  /**
   * data tables and metadata for charts 
   * 
   * @param int $order_id
   * @param array $filters
   *   array(
   *     'country'   => '',
   *     'region'    => '',
   *     'specialty' => '',
   *     'brand'     => '',
   *   );
   * @param string $machinename
   * @param \PP\SampleBundle\Service\ClipperChartsService $charts_helper
   * 
   * @return array $datable
   **/
  public function getDataStructure($order_id, $filters = array(), $chartmachinename = '')
  {
    $content = array();
    
    $this->setOrderID($order_id);
    $this->setDrillDown($filters, $chartmachinename);
    $this->setReturnFields(array(
      'survey_type',
      'name_full',
    ));
    $this->setReturnChartExtras(array(
      'chartmachinename',
      'drilldown',
      'filter',
      'countTotal',
      'countFiltered',
      'datatable',
      'titleLong',
    ));
    $this->setReturnChartCustoms(array(
      'charttype' => '%%machine_name%%' . self::$js_charttype_postfix,
      'header'    => 'Maecenas faucibus mollis interdum.',
      'footer'    => 'Cras mattis consectetur purus sit amet fermentum.',
    ));
    // process charts & field required
    $content = $this->getCharts();
    $this->survey_type = $content['fields']['survey_type'];
    // calculate "Estimated responses at completion" or global quota
    $quotas = $this->getQuotas();
    $first = $content['charts']->first();
    
    $content['meta'] = array(
      "projectTitle"      => $content['fields']['name_full'], 
      "totalResponses"    => $first['countTotal'],
      "quota"             => array_sum($quotas),
      /**
       * @todo: do not hardcode these properties 
       **/
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
    unset($content['fields']);
    
    return $content;
  }
}