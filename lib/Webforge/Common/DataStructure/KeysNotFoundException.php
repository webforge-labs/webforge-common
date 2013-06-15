<?php

namespace Webforge\Common\DataStructure;

use Webforge\Common\Util;

class KeysNotFoundException extends \Webforge\Common\Exception {

  protected $keys;

  public static function fromKeys(Array $keys) {
    $e = new static(sprintf('The keys (%s) cannot be found in datastructure.', Util::varInfo($keys))); // implode throws notice in php 5.4
    $e->setKeys($keys);

    return $e;
  }

  /**
   * @return array
   */
  public function getKeys() {
    return $this->keys;
  }
  
  /**
   * @param array keys
   * @chainable
   */
  public function setKeys(Array $keys) {
    $this->keys = $keys;
    return $this;
  }
}
