<?php

namespace Webforge\Common\JS;

use stdClass;

class JSONConverterTest extends \Webforge\Common\TestCase {
  
  protected $converter;
  
  public function setUp() {
    parent::setUp();
    $this->converter = new JSONConverter();
  }
  
  public function testConverterCreate_ReturnsAnInstance() {
    $this->assertInstanceOf('Webforge\Common\JS\JSONConverter', JSONConverter::create());
  }
  
  /**
   * @dataProvider data2json
   */
  public function testStringifyConvertsStructuresToJSOn($data) {
    $jsonEncoded = json_encode($data);
    
    $this->assertJsonStringEqualsJsonString(
      $jsonEncoded,
      $this->converter->stringify($data)
    );

    $this->assertJsonStringEqualsJsonString(
      $jsonEncoded,
      $this->converter->stringify($data, JSONConverter::PRETTY_PRINT)
    );

    $this->assertJsonStringEqualsJsonString(
      $jsonEncoded,
      $this->converter->prettyPrint($data)
    );
  }
  
  public static function data2json() {
    $tests = array();
    
    $tests[] = array(
      array(),
    );

    $tests[] = array(
      new stdClass,
    );
    
    return $tests;
  }
}
?>