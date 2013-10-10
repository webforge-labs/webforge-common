<?php

namespace Webforge\Common\System;

use Webforge\Process\System as SystemImplementation;

class Container {

  /**
   * @var Webforge\Common\System\System
   */
  protected $system;
  
  /**
   * @return Webforge\Common\System\System
   */
  public function getSystem() {
    if (!isset($this->system)) {
      $this->system = new SystemImplementation($this);
    }
    return $this->system;
  }
  
  /**
   * @param Webforge\Common\System\System $system
   * @chainable
   */
  public function injectSystem(System $system) {
    $this->system = $system;
    return $this;
  }
}
