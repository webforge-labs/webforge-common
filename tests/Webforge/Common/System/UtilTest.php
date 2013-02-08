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
}
?>