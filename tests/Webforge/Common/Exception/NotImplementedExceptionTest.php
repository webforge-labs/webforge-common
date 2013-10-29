<?php

namespace Webforge\Common\Exception;

class NotImplementedExceptionTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__ . '\\NotImplementedException';
    parent::setUp();
  }

  public function testFromStringConstruct() {
    $this->setExpectedException($this->chainClass);
    throw NotImplementedException::fromString('parameter #2');
  }
}
