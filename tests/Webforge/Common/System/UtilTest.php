<?php

namespace Webforge\Common\System;

class UtilTest extends \Webforge\Common\TestCase {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\Util';
    parent::setUp();
  }
  
  public function testFindPHPBinaryReturnsAFileThatIsCallableLikeAPHPInterpreter() {
    $this->assertInstanceOf('Webforge\Common\System\File', $phpBin = Util::findPHPBinary());
    
    exec($cmd = $phpBin->getQuotedString().' -v', $output, $ret);
    
    $this->assertEquals(0, $ret, $cmd.' did not return 0');
    $v = mb_substr(PHP_VERSION, 0, 1);
    $this->assertStringStartsWith('PHP '.$v, $output[0], 'should find a PHP '.$v.' Interpreter');
  }

  /**
   * @dataProvider provideEscapeShellArg
   */
  public function testEscapeShellArg($expectedArg, $arg, $os) {
    $this->assertEquals($expectedArg, 
      Util::escapeShellArg($arg, $os)
    );
  }
  
  public static function provideEscapeShellArg() {
    $tests = array();
  
    $test = function($arg, $expectedArg, $os) use (&$tests) {
      $tests[] = array($expectedArg, $arg, $os);
    };

    $os = Util::WINDOWS;
  
    $test('D:\\www\\webforge-console\\', '"D:\\www\\webforge-console\\\\"', $os);


    $os = Util::UNIX;
  
    return $tests;
  }
}
