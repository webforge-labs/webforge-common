<?php

namespace Webforge\Common\System;

use Symfony\Component\Process\Process;
use Webforge\Common\JS\JSONConverter;

class UtilTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\Util';
    parent::setUp();

    $this->echoBat = $this->getPackageDir('bin/')->getFile('echo.bat');
    $this->echoSh = FALSE;
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
    $test('myargument', '"myargument"', $os);
    $test('my"argument', '"my\"argument"', $os);
    $test("my'argument", '"my\'argument"', $os);

    $os = Util::UNIX;
  
    return $tests;
  }

  /**
   * @dataProvider provideShellEscapingAcceptance
   */
  public function estEscapeShellArgAcceptance($expectedArg, $providedArg, $os) {
    if ($os === Util::UNIX) {
      if (!$this->echoSh) {
        return $this->markTestSkipped('runs only on windows, yet. create a echo sh!');
      }

      $bin = $this->echoSh;
    } else {
      $bin = $this->echoBat;
    }

    $cmdLine = $bin.' '.Util::escapeShellArg($providedArg);

    $process = new Process(NULL, array('definedenv', 'this is an defined env value'));

    $this->assertEquals(0, $process->run(), ' process did not run correctly '.$process->getCommandLine());
    $this->assertEquals(array($expectedArg), JSONConverter::create()->parse($process->getOutput()));
  }

  public static function provideShellEscapingAcceptance() {
    $tests = array();

    $test = function($arg, $expectedArg, $os) use (&$tests) {
      $tests[] = array($expectedArg, $arg, $os);
    };

    $os = Util::WINDOWS;
    
    $test('myargument', 'myargument', $os);
    $test('my"argument', 'my"argument', $os);
    $test("my'argument", "my'argument", $os);
    $test('format="%h%d%Y"', 'format="%h%d%Y"', $os); // this is an argument in an argument encoded
    $test('%h%d%Y', '%h%d%Y', $os);
    $test('this is an defined env value', '%definedenv%', $os);

    // you have not a chance to escape it
    // this test is NOT used yet, i still didnt find out how to escape % sign here to make it literal
    //array('%this is an defined env value%'), array('%%defined%%')
    
    return $tests;
  }
}
