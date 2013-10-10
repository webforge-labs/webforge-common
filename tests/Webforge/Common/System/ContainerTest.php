<?php

namespace Webforge\Common\System;

use Mockery as m;

class ContainerTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\Container';
    parent::setUp();

    $this->config = m::mock(__NAMESPACE__.'\ContainerConfiguration');
    $this->container = new Container($this->config);
    
    $this->systemInterface = __NAMESPACE__.'\System';
  }

  public function testContainerReturnsASystemImplementation() {
    $this->assertInstanceOf($this->systemInterface, $this->container->getSystem());
  }

  public function testContainerCanHaveASystemInjected() {
    $system = $this->getMockForAbstractClass($this->systemInterface);

    $this->container->injectSystem($system);
    $this->assertSame($system, $this->container->getSystem());
  }

  public function testReturnsAConfiguredExecutableFinder() {
    $this->expectExecutableConfiguration();

    $this->assertInstanceOf(__NAMESPACE__.'\ExecutableFinder', $finder = $this->container->getExecutableFinder());

    $this->assertEquals(
      (string) $this->someBin, 
      (string) $finder->getExecutable('test-bin')
    );
  }

  protected function expectExecutableConfiguration() {
    $this->someBin = $this->getFile('fakeExecutableRar');
    $this->config
      ->shouldReceive('forExecutableFinder')
      ->once()
      ->andReturn(array('test-bin'=>(string) $this->someBin));
  }
}
