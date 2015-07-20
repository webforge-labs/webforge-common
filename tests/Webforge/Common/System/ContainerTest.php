<?php

namespace Webforge\Common\System;

use Mockery as m;

class ContainerTest extends \Webforge\Common\TestCase {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\Container';
    parent::setUp();

    $this->config = m::mock(__NAMESPACE__.'\ContainerConfiguration');
    $this->config->shouldReceive('forExecutableFinder')->andReturn(array())->byDefault();
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

  public function testStaticCreatorCreatesADefaultConfigurationContainer() {
    $this->assertInstanceOf($this->chainClass, $defaultContainer = Container::createDefault());

    $this->assertInstanceOf(__NAMESPACE__.'\ExecutableFinder', $defaultContainer->getExecutableFinder());
    $this->assertInstanceOf($this->systemInterface, $defaultContainer->getSystem());
  }

  protected function expectExecutableConfiguration() {
    $this->someBin = $this->getFile('fakeExecutableRar');
    $this->config
      ->shouldReceive('forExecutableFinder')
      ->once()
      ->andReturn(array('test-bin'=>(string) $this->someBin));
  }
}
