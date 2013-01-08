<?php

namespace Webforge\Common;

use stdClass;

class UtilTest extends TestCase {
  
  /**
   * @dataProvider provideAllTypes
   */
  public function testTypeInfoAcceptance($typeSample) {
    $this->assertNotEmpty(Util::typeInfo($typeSample));
  }

  /**
   * @dataProvider provideAllTypes
   */
  public function testVarInfoAcceptance($typeSample) {
    $this->assertNotEmpty(Util::varInfo($typeSample));
  }
  
  public function provideAllTypes() {
    $tests = array();
    
    $tests[] = array(
      new stdClass
    );

    $tests[] = array(
      array('someValue')
    );
    
    $tests[] = array(
      'string'
    );
    
    $tests[] = array(
      7
    );
    
    $tests[] = array(
      0.17
    );
    
    $tests[] = array(
      new TestValueObject('v1', 'v2')
    );
    
    
    return $tests;
  }
  
}
?>