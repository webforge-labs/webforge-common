<?php

namespace Webforge\Common\System;

use Webforge\Process\System as SystemImplementation;

class Container {

  /**
   * @var Webforge\Common\System\System
   */
  protected $system;
  
  /**
   * @var Webforge\Common\System\ExecutableFinder
   */
  protected $executableFinder;
  

  public function __construct(ContainerConfiguration $configuration) {
    $this->configuration = $configuration;
  }
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

  /**
   * @return Webforge\Common\System\ExecutableFinder
   */
  public function getExecutableFinder() {
    if (!isset($this->executableFinder)) {
      $this->executableFinder = new ExecutableFinder($this->configuration->forExecutableFinder());
    }
    return $this->executableFinder;
  }

  /**
   * @param Webforge\Common\System\ExecutableFinder $executableFinder
   * @chainable
   */
  public function injectExecutableFinder(ExecutableFinder $executableFinder) {
    $this->executableFinder = $executableFinder;
    return $this;
  }
}
