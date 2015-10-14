<?php

namespace PSL\ClipperBundle\Charts;

final class SurveyChartMap
{


  /**
   * map surveys to charts (one to many)
   *  chart to question code
   *  - string; (one to one) use string - will do partial search of answers' key
   *  - array; (many) will do string exact comparision to answers' key
   *
   *  survey to chart types
   *
   * @param $survey_machine_name string, unique name of the survey type
   * @return $map array of chart type to question code
   */
  public function map($survey_machine_name)
  {
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
        'PPDBrandMessages'  => array(
          'G0010Q001',
          'G0011Q001',
          'G0012Q001',
          'G0013Q001',
          'G0014Q001',
          'G0015Q001',
        ),
        //NPS:008; this based number of brands
        'DNA'               => array(
          'G004Q001',
          'G005Q001',
          'G006Q001',
          'G007Q001',
          'G008Q001',
          'G009Q001'
        ),
      ), //nps_plus
    );

    //register machine_names
    array_walk($map, function(&$charts, $type) {
      $machine_names = array_keys($charts);
      $charts['machine_names'] = $machine_names;
    });

    return isset($map[$survey_machine_name]) ? $map[$survey_machine_name] : array();
  }
}
