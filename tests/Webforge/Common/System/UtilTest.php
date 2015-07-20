<?php

namespace Webforge\Common\System;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Webforge\Common\JS\JSONConverter;

class UtilTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\Util';
    parent::setUp();

    $this->echoBat = $this->getPackageDir('bin/')->getFile('echo.bat');
    $this->echoSh = $this->getPackageDir('bin/')->getFile('echo.sh');
  }
  
  public function testFindPHPBinaryReturnsAFileThatIsCallableLikeAPHPInterpreter() {
    $this->assertInstanceOf('Webforge\Common\System\File', $phpBin = Util::findPHPBinary());
    
    exec($cmd = $phpBin->getQuotedString().' -v', $output, $ret);
    
    $this->assertEquals(0, $ret, $cmd.' did not return 0');
    $v = mb_substr(PHP_VERSION, 0, 1);
    $this->assertStringStartsWith('PHP '.$v, $output[0], 'should find a PHP '.$v.' Interpreter');
  }

  public function testEscapeShellArgRegressionWithQuotedBackslashAtTheEnd() {
    if (Util::isWindows()) {
      $this->assertEquals('"D:\www\some\directory\\\\"', Util::escapeShellArg('D:\www\some\directory\\'));
    } else {
      $this->markTestSkipped('Undefined for unix');
    }
  }
}
