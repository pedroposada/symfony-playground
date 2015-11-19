<?php

namespace PSL\ClipperBundle\Charts;

final class SurveyChartMap
{

  
  /**
   * map surveys to charts (one to many)
   *  chart to question code
   *  - string; (one to one) use string - will do partial search of answers' key
   *  - array; (many) will do string exact comparison to answers' key
   *
   *  survey to chart types
   *
   * @param string $survey_machine_name 
   *    unique name of the survey type
   *    
   * @param boolean|array $responses_qcode
   *    without qCode reference, dynamic brands reference will note working,
   *    - this mean the ChartMachine name will return an empty array.
   *    - elsewhere with the reference, it will extract all based on given qCode.
   * 
   * @return $map array of chart type to question code
   */
  public function map($survey_machine_name, $responses_qcode = FALSE)
  {
    static $cache_no_qcode;
    static $cache_with_qcode;
    
    if ((empty($responses_qcode)) && (isset($cache_no_qcode[$survey_machine_name]))) {
      return $cache_no_qcode[$survey_machine_name];
    }
    elseif ((!empty($responses_qcode)) && (isset($cache_with_qcode[$survey_machine_name]))) {
      return $cache_with_qcode[$survey_machine_name];
    }
            
    $map = array(
      'nps_plus' => array(
        //NPS:001
        'NPS'               => 'G003Q001',
        //NPS:002
        'Loyalty'           => 'G003Q001',
        //NPS:003
        'DoctorsPromote'    => 'G003Q001',
        //NPS:004
        'PromotersPromote'  => 'G003Q001',        
        //NPS:005
        'DetractorsPromote' => 'G003Q001',
        //NPS:006
        'PromVsDetrPromote' => 'G002Q001',
        //NPS:007; this based number of brands
        'PPDBrandMessages'  => '^BRANDASSCG([0-9]{3})Q001',
        //NPS:008; this based number of brands
        'DNA'               => '^BRANDDNAG([0-9]{3})Q001',
        //Extra for export
        //-> Chart 4 / Table 4
        'PromotersPromoteMean'     => 'G003Q001',
        //-> Chart X / Table 9-16
        'PPDBrandMessagesByBrands' => '^BRANDASSCG([0-9]{3})Q001',
      ), //nps_plus
    );
    
    if (!isset($map[$survey_machine_name])) {
      return array();
    }    
    $map = $map[$survey_machine_name];        
    // register machine_names    
    $map['machine_names'] = array_keys($map);
    
    // set cache
    $cache_no_qcode[$survey_machine_name] = $map;
    if (empty($responses_qcode)) {
      return $map;
    }
    
    // clean up $responses_qcode
    $responses_qcode = preg_replace("/\[(.*)\]/", "$2", $responses_qcode);
    $responses_qcode = array_filter($responses_qcode);
    $responses_qcode = array_unique($responses_qcode);
    
    // search subs
    foreach ($map as $machine_name => $qcode) {
      if (!is_string($qcode) || (is_string($qcode) && (strpos($qcode, '^') === FALSE))) {
        continue;
      }
      $set = preg_grep('/' . $qcode . '/', $responses_qcode);
      $map[$machine_name] = $set;
    }

    // set cache
    $cache_with_qcode[$survey_machine_name] = $map;
    
    return $map;
  }
}
