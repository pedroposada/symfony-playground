<?php

namespace PSL\ClipperBundle\Charts;

final class SurveyChartMap
{


  /**
   * map surveys to charts (one to many)
   *  chart to question code (one to one)
   *  survey to chart types
   *
   * @param $survey_type string, unique name of the survey type
   * @return $map array of chart type to question code
   */
  public function map($survey_type)
  {
    $map = array(
      'nps_plus' => array(
        'net_promoters'                           => 'G003Q001',
        'devoted_doctor_to_brands'                => 'G003Q001',
        'doctor_promoting_brands'                 => 'G003Q001',
        'doctor_promoting_mine_also_others'       => 'G003Q001',
        'doctor_promoting_mine_also_others_table' => 'G003Q001',
        'detractors_promotes_these_brands'        => 'G003Q001',
        'promoters_prescribe_versus_detractors'   => 'G002Q001',
        'chart_types' => array(
          'net_promoters',
          'devoted_doctor_to_brands',
          'doctor_promoting_brands',
          'doctor_promoting_mine_also_others',
          'doctor_promoting_mine_also_others_table',
          'detractors_promotes_these_brands',
          'promoters_prescribe_versus_detractors',
        ),
      ),
    );

    return isset($map[$survey_type]) ? $map[$survey_type] : array();
  }
}
