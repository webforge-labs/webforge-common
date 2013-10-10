<?php

namespace Webforge\Common\System;

class ContainerTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Common\\System\\Container';
    parent::setUp();

    $this->container = new Container();
    $this->systemInterface = 'Webforge\Common\System\System';
  }

  public function testContainerReturnsASystemImplementation() {
    $this->assertInstanceOf($this->systemInterface, $this->container->getSystem());
  }

  public function testContainerCanHaveASystemInjected() {
    $system = $this->getMockForAbstractClass($this->systemInterface);

    $this->container->injectSystem($system);
    $this->assertSame($system, $this->container->getSystem());
  }
}
