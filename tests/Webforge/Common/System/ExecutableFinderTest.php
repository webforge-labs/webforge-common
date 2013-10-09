<?php

namespace Webforge\Common\System;

use Webforge\Configuration\Configuration;

class ExecutableFinderTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\ExecutableFinder';
    parent::setUp();
    $this->notFoundException = 'Webforge\\Common\\System\\ExecutableNotFoundException';

    $this->rar = (string) $this->getFile('fakeExecutableRar');
    
    $config = array(
      'rar'=>$this->rar,
      'undefined'=>'/this/path/does/notexists/rar'
    );

    $this->finder = new ExecutableFinder($config);
  }

  public function testFindsAnExecutableWhichIsIntheConfig() {
    $rarBin = $this->finder->getExecutable('rar');
    
    $this->assertInstanceOf('Webforge\Common\System\File', $rarBin);
    $this->assertFileEquals($this->rar, $rarBin);
  }

  public function testFindsExecutableReturnsBool() {
    $this->assertFalse($this->finder->findsExecutable('undefined'));
  }

  public function testDoesNotFindExecutableWhichIsInConfigButDoesNotExist() {
    $this->setExpectedException($this->notFoundException);
    $this->finder->getExecutable('undefined');
  }

  public function testDoesNotFindExecutableWhichIsntExistingAndNotInConfig() {
    $this->setExpectedException($this->notFoundException);
    $this->finder->getExecutable('bla-bla-undefined');
  }
}
