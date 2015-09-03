<?php

namespace PSL\ClipperBundle\Charts;

final class SurveyChartMap
{


  /**
   * map surveys to charts (one to many)
   *  chart to question code
   *  - string; (one to one) use string - will do partial search of answers' key
   *  - array; (many) will do string exact comparision od answers' key
   *
   *  survey to chart types
   *
   * @param $survey_type string, unique name of the survey type
   * @return $map array of chart type to question code
   */
  public function map($survey_type)
  {
    $map = array(
      'nps_plus' => array(
        //NPS:001
        'net_promoters'                           => 'G003Q001',
        //NPS:002
        'devoted_doctor_to_brands'                => 'G003Q001',
        //NPS:003
        'doctor_promoting_brands'                 => 'G003Q001',
        //NPS:004
        'doctor_promoting_mine_also_others'       => 'G003Q001',
        //NPS:005
        'doctor_promoting_mine_also_others_table' => 'G003Q001',
        //NPS:006
        'detractors_promotes_these_brands'        => 'G003Q001',
        //NPS:007
        'promoters_prescribe_versus_detractors'   => 'G002Q001',
        //NPS:008; this based number of brands
        'associate_categories_importance'         => array(
          'G0010Q001',
          'G0011Q001',
          'G0012Q001',
          'G0013Q001',
          'G0014Q001',
          'G0015Q001',
        ),
        //NPS:009; this based number of brands
        'what_they_say'                           => array(
          'G004Q001',
          'G005Q001',
          'G006Q001',
          'G007Q001',
          'G008Q001',
          'G009Q001'
        ),
      ), //nps_plus
    );

    //register chart_types
    array_walk($map, function(&$charts, $type) {
      $machine_names = array_keys($charts);
      $charts['chart_types'] = $machine_names;
    });

    return isset($map[$survey_type]) ? $map[$survey_type] : array();
  }
}
