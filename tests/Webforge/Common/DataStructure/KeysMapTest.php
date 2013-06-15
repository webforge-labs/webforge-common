<?php

namespace Webforge\Common\DataStructure;

class KeysMapTest extends \Webforge\Code\Test\Base {

  protected $throwMap;
  protected $map;
  
  public function setUp() {
    $this->chainClass = 'Webforge\Common\DataStructure\\KeysMap';
    parent::setUp();
    $this->keysException = 'Webforge\\Common\\DataStructure\\KeysNotFoundException';

    $this->throwMap = $this->cons(
      array('dataKey'=>'datahier','emptyKey'=>' ')
    );

    $this->map = $this->cons(
      array(
        'key1'=>array(
          'emptyString'=>' ',
          'notEmpty'=>'not empty string',
          'values'=>array(
            'v1'=>'value 1',
            'v2'=>'value 2'
          ),
          'integers'=>array(
            1=>1,
            0=>0
          )
        ),
        'key2'=>array('integer'=> 22, 'null'=>NULL))
    );
  }

  /**
   * @dataProvider provideGetsWithoutDefault
   */
  public function testGetReturnsKeysFromPath($path, $expectedKey) {
    $this->assertEquals($expectedKey, $this->map->get($path));
  }

  public static function provideGetsWithoutDefault() {
    $tests = array();
  
    $test = function(Array $path, $expectedGet) use (&$tests) {
      $tests[] = array(implode('.', $path), $expectedGet);
      $tests[] = array($path, $expectedGet);
    };

    // does return strings as value
    $test(
      array('key1', 'notEmpty'), 
      'not empty string'
    );
  
    // does return empty strings as values
    $test(
      array('key1', 'emptyString'), 
      ' '
    );

    // does return NULL as value
    $test(
      array('key2', 'null'),
      NULL
    );

    // does return intger 0 as value
    $test(
      array('key1', 'integers', 0),
      0
    );
    $test(
      array('key1', 'integers', 1),
      1
    );

    // does return full arrays
    $test(
      array('key2'),
      array('integer'=> 22, 'null'=>NULL)
    );

    // does return inner full arrays
    $test(
      array('key1', 'values'),
      array('v1'=> 'value 1', 'v2'=>'value 2')
    );

    // does return nested values
    $test(
      array('key1', 'values', 'v1'),
      'value 1'
    );

    return $tests;
  }


  public function testThrowsExceptionForNonExistantKEysByDefault() {
    $this->setExpectedException($this->keysException);
    $this->throwMap->get('undefined');
  }

  public function testThrowsExceptionForNonExistanceKeyIfDO_THROW_EXCEPTION_ConstantIsSetAsDefault() {
    $this->setExpectedException($this->keysException);
    $this->throwMap->get('undefined', KeysMap::DO_THROW_EXCEPTION);

  }

  /**
   * @dataProvider provideContains
   */
  public function testContainsReturnsIfKeysHaveAValue($path, $expectedResult) {
    $this->assertEquals($expectedResult, $this->map->contains($path), 'contains does not return correct bool');
  }

  public static function provideContains() {
    $tests = array();
  
    $test = function (Array $path, $expectedBool) use (&$tests) {
      $tests[] = array(implode('.', $path), $expectedBool);
      $tests[] = array($path, $expectedBool);
    };

    // all getting values without default are defined:
    foreach (self::provideGetsWithoutDefault() as $testArgs) {
      $tests[] = array($path = $testArgs[0], TRUE);
    }

    $test(
      array('undefined'),
      FALSE
    );

    // even if key1 is defined
    $test(
      array('key1', 'undefined'),
      FALSE
    );

    // even if key1 and key2 is defined
    $test(
      array('key1', 'values', 'undefined'),
      FALSE
    );
  
    return $tests;
  }

  public function testGetIsOnlyAllowedWithArraysOrStringPaths() {
    $this->setExpectedException('InvalidArgumentException');

    $this->map->get(7);
  }

  public function testGetIsOnlyAllowedWithArraysOrStringPaths_notObject() {
    $this->setExpectedException('InvalidArgumentException');

    $this->map->get((object) array('blubb'));
  }

  public function testGetIsNotAllowedWithNestedArrays() {
    $this->setExpectedException('InvalidArgumentException');

    $this->map->get(array('key1', array('notok'=>'becauseitsnested')));
  }

  public function testSetWithAnEmptyKeysPathIsNotAllowedIfValueIsNotAnArray() {
    $this->setExpectedException('InvalidArgumentException');
    
    /* value should be always an array here */
    $this->map->set($emptyKeys = array(), 'not an array');
  }

  public function testSetWithAnEmptyKeysPathISAllowedWhenValueAnArray() {
    $this->map->set($emptyKeys = array(), $replacement = array('this'=>'replaces'));

    $this->assertEquals($replacement, $this->map->toArray());
  }
  
  public function testSetChangesValueWithFullPath() {
    $this->map->set(
      $keys = array('key1', 'values', 'v1'), 
      $newValue = 'new v1'
    );

    $this->assertEquals($newValue, $this->map->get($keys));
  }
  
  public function testRegressionWeirdComparisonWithInt0AndStringBug() {
    $map = $this->cons(array());
    
    $this->assertEquals(0, $map->get(array('noneexistant'), 0));
  }

  /**
   * @dataProvider provideGetsWithDefault
   */
  public function testGetWithDefaultValueGiven($keys, $default, $expected) {
    $this->assertEquals($expected, $this->map->get($keys, $default));
  }

  public static function provideGetsWithDefault() {
    $tests = array();
  
    $test = function (Array $path, $default, $expected) use (&$tests) {
      $tests[] = array(implode('.', $path), $default, $expected);
      $tests[] = array($path, $default, $expected);
    };

    // all getting values with default return the value because their keys are defined
    foreach (self::provideGetsWithoutDefault() as $testArgs) {
      $tests[] = array($path = $testArgs[0], $default = FALSE, $expectedValue = $testArgs[1]);
    }

    $test(
      array('undefined'),
      'defaultValue',
      'defaultValue'
    );

    $test(
      array('undefined'),
      array('defaultArray'),
      array('defaultArray')
    );
  
    $test(
      array('key1', 'undefined'),
      'defaultValue',
      'defaultValue'
    );
  
    $test(
      array('key1', 'values', 'undefined'),
      FALSE,
      FALSE
    );

    return $tests;
  }

  /**
   * @dataProvider provideTestRemove
   */
  public function testRemove($mapData, $keys, $existing = TRUE) {
    $map = $this->cons($mapData);

    if ($existing) {
      $this->assertTrue($map->contains($keys));
    } else {
      $this->assertFalse($map->contains($keys));
    }

    $this->assertChainable($map->remove($keys));
    $this->assertFalse($map->contains($keys));
  }
  
  public static function provideTestRemove() {
    $tests = array();
    
    $ref = array(
      'keys'=>array('key1'=>' ','key2'=>'leer'),'key3'=>'    ',
      'keys2'=>array('key1'=>' ','key2'=> ' ','key3'=>array('key4'=>'nix'))
    );
    
    // existing in array
    $tests[] = array($ref, array('keys','key1'), TRUE );
    $tests[] = array($ref, array('keys','key2'), TRUE );
    $tests[] = array($ref, array('keys2','key3','key4'), TRUE );
    
    // non existing
    $tests[] = array($ref, array('undefined'), FALSE );
    $tests[] = array($ref, array('keys','undefined'), FALSE );
    $tests[] = array($ref, array('keys','key2','undefined'), FALSE );
   
    return $tests;    
  }

  public function cloningReturnsACopyOftheStructure() {
    $cloned = clone $this->map;

    $this->assertEquals(
      $this->map->toArray(),
      $cloned->toArray()
    );
  }

  protected function getMergeMaps() {
    /* Project Paths */
    $conf['projects']['root'] = 'D:\www\\';
    $conf['projects']['tiptoi']['root'] = 'D:\www\RvtiptoiCMS\Umsetzung\\';
    $conf['projects']['SerienLoader']['root'] = 'D:\www\serien-loader\Umsetzung\\';

    /* Environment */
    $conf['defaults']['system']['timezone'] = 'Europe/Berlin';
    $conf['defaults']['system']['chmod'] = 0644;
    $conf['defaults']['i18n']['language'] = 'de';

    $hostMap = $this->cons($conf);
    
    $pconf['system']['timezone'] = 'Europe/London';
    $pconf['projects']['SerienLoader']['root'] = 'D:\www\nothere';

    $projectMap = $this->cons($pconf);

    return array($hostMap, $projectMap);
  }
  
  public function testMergeWithNoParamsMergesRoot() {
    list($hostMap, $projectMap) = $this->getMergeMaps();

    $mergedMap = clone $hostMap;    
    $mergedMap->merge($projectMap);
    
    $this->assertEquals('Europe/Berlin',$mergedMap->get('defaults.system.timezone'));
    $this->assertEquals('Europe/London',$mergedMap->get('system.timezone'));
    $this->assertFalse($mergedMap->contains('i18n.language'));
  }

  public function testMergeWithFromKeysMergesStructuresOfOtherArrayWithTheFromKeysValues() {
    list($hostMap, $projectMap) = $this->getMergeMaps();

    // with fromKeys
    $mergedMap = $this->cons(array());
    $mergedMap->merge($hostMap, array('defaults'));
    $mergedMap->merge($projectMap);
    
    $this->assertEquals('Europe/London', $mergedMap->get('system.timezone'));
    $this->assertEquals('D:\www\nothere', $mergedMap->get('projects.SerienLoader.root'));
  }

  public function testMergeWithToKeysMergesStructuresOfOtherArrayToTheToKeysValues() {
    list($hostMap, $projectMap) = $this->getMergeMaps();

    $mergedMap = $this->cons(array());

    // with toKeys
    $mergedMap->merge($hostMap, array('defaults'), array('projectdefaults'));

    $this->assertEquals('Europe/Berlin',$mergedMap->get('projectdefaults.system.timezone'));
  }

  public function testIsEmpty() {
    $map = $this->cons(array());

    $this->assertTrue($map->isEmpty());
    $this->assertFalse($this->map->isEmpty());
  }

  protected function cons(Array $data) {
    $map = new KeysMap($data);
    return $map;
  }
}
