<?php

namespace Webforge\Common;

class PHPClassTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__ . '\\PHPClass';
    parent::setUp();

    $this->gClass = new PHPClass(__CLASS__);
  }

  public function testImplementsClassInterface() {
    $this->assertInstanceOf('Webforge\Common\ClassInterface', $this->gClass);
  }

  public function testNamespaceAndNameAndFQNAreSetFromFQNString() {
    $this->assertEquals(__CLASS__, $this->gClass->getFQN());
    $this->assertEquals('PHPClassTest', $this->gClass->getName());
    $this->assertEquals(__NAMESPACE__, $this->gClass->getNamespace());
  }

  public function testReflectionClassIsReturned() {
    $this->assertInstanceOf('ReflectionClass', $this->gClass->getReflection());
  }

  public function testReflectionIsCached() {
    $refl = $this->gClass->getReflection();
    $this->assertSame($refl, $this->gClass->getReflection());
  }

  public function testReflectionIsChangedWhenFQNIsChanged() {
    $refl = $this->gClass->getReflection();

    $this->gClass->setName('PHPClass');

    $this->assertNotSame($refl, $this->gClass->getReflection(), 'reflection should be refreshed fro setName');
  }
}
