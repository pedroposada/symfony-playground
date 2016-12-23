<?php
/**
 * PHPUnit Test
 * Charts Component Tests; checking its dataTable raw structure & data-type.
 *
 * Charts type Assembler
 * src/PP/SampleBundle/Charts/Assembler.php
 *
 * Direct test command:
 * phpunit -c app src/PP/SampleBundle/Tests/Charts/ChartsAssemblerTest.php
 *
 * Note:
 * Test sequence must be based on chart map orders.
 * @see  src/PP/SampleBundle/Charts/SurveyChartMap.php
 */
namespace PP\SampleBundle\Tests\Charts;

use PP\SampleBundle\Tests\WebTestCase;

class ChartsAssemblerTest extends WebTestCase
{
  private static $order;
  private static $counter;
  private static $map;
  private static $assembler;

  private static $survey_type = 'nps_plus';

  public function setUp()
  {
    parent::setUp();

    self::$map = $this->container->get('survey_chart_map');
    self::$map = self::$map->map(self::$survey_type);
    self::$map = self::$map['machine_names'];

    self::$assembler = $this->container->get('chart_assembler');

    self::$order = $this->getLatestFirstQGroup(self::$survey_type, 'ORDER_COMPLETE', FALSE);
  }

  /**
   * Method to counter test request.
   * @method getCounter
   *
   * This making each test fixed to chart-map sequential-order.
   *
   * @return integer
   */
  private function getCounter()
  {
    if (!isset(self::$counter)) {
      self::$counter = -1;
    }
    self::$counter++;
    return self::$counter;
  }

  /**
   * Test chart 1: NPS
   * @method testChartNPS
   */
  public function testChartNPS()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $brands = self::$order->getFormDataByField('brands');

    //each brands have it's own data row
    $this->assertSame(count($data_table), count($brands));

    //each row much has this key & value
    $test = array(
      'brand'      => 'is_string',
      'base'       => 'is_numeric',
      'detractors' => 'is_numeric',
      'passives'   => 'is_numeric',
      'promoters'  => 'is_numeric',
      'score'      => 'is_numeric',
    );
    foreach ($data_table as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type: '{$data[$key]}'.");
      }
    }
  }

  /**
   * Test chart 2: Loyalty
   * @method testChartLoyalty
   */
  public function testChartLoyalty()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $brands = self::$order->getFormDataByField('brands');

    //data_table in 3 main structure
    $test = array(
      'mean'   => 'is_numeric',
      'base'   => 'is_numeric',
      'brands' => 'is_array',
    );
    foreach ($test as $key => $type) {
      $this->assertArrayHasKey($key, $data_table, "'{$key}' key is missing.");
      $this->assertTrue($type($data_table[$key]), "'{$key}' value having wrong data type.");
    }

    //each brands have it's own data row
    $this->assertSame(count($data_table['brands']), count($brands));

    //each row much has this key & value
    $test = array(
      'brand'   => 'is_string',
      'base'    => 'is_numeric',
      'loyalty' => 'is_numeric',
    );
    foreach ($data_table['brands'] as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type: '{$data[$key]}'.");
      }
    }
  }

  /**
   * Test chart 3: DoctorsPromote
   * @method testChartDoctorsPromote
   */
  public function testChartDoctorsPromote()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);

    $test = array(
      array('satisfied',  ''),
      array('exclusive',  'satisfied'),
      array('shared'   ,  'satisfied'),
      array('satisfied',  ''),
    );
    foreach ($test as $ind => $set) {
      list($nm, $dt) = $set;
      $dt = (empty($dt) ? $data_table : $data_table[$dt]);
      $this->assertArrayHasKey($nm, $dt);
      $this->assertArrayHasKey('amount', $dt[$nm]);
      $this->assertTrue(is_numeric($dt[$nm]['amount']));
    };
    $this->assertTrue(is_numeric($data_table['base']));
  }

  /**
   * Test chart 4: PromotersPromote
   * @method testChartPromotersPromote
   */
  public function testChartPromotersPromote()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $brands = self::$order->getFormDataByField('brands');

    //each brands have it's own data row
    $this->assertSame(count($data_table), count($brands));

    //each row much has this key & value
    $test = array(
      'brand'       => 'is_string',
      'base'        => 'is_numeric',
      'competitors' => 'array|object', // object being use to represent in object for JS output
    );
    foreach ($data_table as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        if ($type == 'array|object') {
         $type = 'is_' . gettype($data[$key]);
        }
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type.");
      }
    }
  }

  /**
   * Test chart 5: DetractorsPromote
   * @method testChartDetractorsPromote
   */
  public function testChartDetractorsPromote()
  {
    //have same structure as PromotersPromote chart;
    $this->testChartPromotersPromote();
  }

  /**
   * Test chart 6: PromVsDetrPromote
   * @method testChartPromVsDetrPromote
   */
  public function testChartPromVsDetrPromote()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $brands = self::$order->getFormDataByField('brands');

    //each brands have it's own data row
    $this->assertSame(count($data_table), count($brands));

    //each row much has this key & value
    $test = array(
      'brand'            => 'is_string',
      'detractors'       => 'is_numeric',
      'detractors_count' => 'is_numeric',
      'passives'         => 'is_numeric',
      'passives_count'   => 'is_numeric',
      'promoters'        => 'is_numeric',
      'promoters_count'  => 'is_numeric',
      'diff'             => 'is_numeric',
    );
    foreach ($data_table as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type.");
      }
    }
  }

  /**
   * Test chart 7: PPDBrandMessages
   * @method testChartPPDBrandMessages
   */
  public function testChartPPDBrandMessages()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $attributes = self::$order->getFormDataByField('attributes');

    //each brands have it's own data row
    $this->assertSame(count($data_table), count($attributes));

    //each row much has this key & value
    $test = array(
      'message'          => 'is_string',
      'detractors'       => 'is_numeric',
      'detractors_count' => 'is_numeric',
      'passives'         => 'is_numeric',
      'passives_count'   => 'is_numeric',
      'promoters'        => 'is_numeric',
      'promoters_count'  => 'is_numeric',
      'lcl'              => 'is_numeric',
      'hcl'              => 'is_numeric',
    );
    foreach ($data_table as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type.");
      }
    }
  }

  /**
   * Test chart 8: DNA
   * @method testChartDNA
   */
  public function testChartDNA()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $brands = self::$order->getFormDataByField('brands');

    //each brands have it's own data row
    $this->assertSame(count($data_table), count($brands));

    //each row much has this key & value
    $test = array(
      'brand'            => 'is_string',
      'detractors'       => 'is_array',
      'passives'         => 'is_array',
      'promoters'        => 'is_array',
    );
    foreach ($data_table as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type.");
      }
    }
  }

  /**
   * Test chart 9: PromotersPromoteMean
   * @method testChartPromotersPromoteMean
   *
   * This chart only being use on Download version.
   */
  public function testChartPromotersPromoteMean()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);
    $brands = self::$order->getFormDataByField('brands');

    $this->assertArrayHasKey('brands', $data_table);

    //each brands have it's own data row
    $this->assertSame(count($data_table['brands']), count($brands));

    //each row much has this key & value
    $test = array(
      'base' => 'is_numeric',
      'mean' => 'is_numeric',
    );
    foreach ($data_table['brands'] as $brand => $data) {
      $this->assertTrue(is_string($brand), "brands '{$brand}' is expected as string.");
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type.");
      }
    }

    $this->assertArrayHasKey('overall', $data_table);

    $test = array(
      'base' => 'is_numeric',
      'mean' => 'is_numeric',
    );
    foreach ($data_table['brands'] as $data) {
      foreach ($test as $key => $type) {
        $this->assertArrayHasKey($key, $data, "'{$key}' key is missing.");
        $this->assertTrue($type($data[$key]), "'{$key}' value having wrong data type.");
      }
    }
  }

  /**
   * Test chart 10: PPDBrandMessagesByBrands
   * @method testChartPPDBrandMessagesByBrands
   *
   * This chart only being use on Download version.
   */
  public function testChartPPDBrandMessagesByBrands()
  {
    $data_table = self::$assembler->getChartDataTable(self::$order->getId(), self::$map[$this->getCounter()], self::$survey_type);

    $questions = self::$order->getFormDataByField('attributes');
    $this->assertArrayHasKey('questions', $data_table);
    $this->assertEquals($data_table['questions'], $questions);

    $brands = self::$order->getFormDataByField('brands');
    $this->assertArrayHasKey('brands', $data_table);
    $this->assertSame(count($data_table['brands']), count($brands));

    $test = array(
      'base' => 'is_numeric',
      'perc' => 'is_numeric',
    );
    //each brands have list of attributes based on number of question
    foreach ($data_table['brands'] as $brand => $data) {
      $this->assertTrue(is_string($brand), "brands '{$brand}' is expected as string.");
      $this->assertSame(count($data), count($questions));
      //each question have set of each net promoter categories
      foreach ($data as $type_dt) {
        foreach ($type_dt as $cat => $cat_set) {
          $this->assertTrue(is_string($cat), "Category '{$cat}' is expected as string.");
          if ($cat == 'diff') {
            $this->assertTrue(is_numeric($cat_set), "Diff value having wrong data type.");
            continue;
          }
          foreach ($test as $key => $ts) {
            if (!is_array($cat_set)) {
              continue;
            }
            $this->assertArrayHasKey($key, $cat_set, "'{$key}' key is missing.");
            $this->assertTrue($ts($cat_set[$key]), "'{$key}' value having wrong data type.");
          }
        }
      }
    }
  }
}