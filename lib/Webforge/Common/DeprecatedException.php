<?php

namespace Webforge\Common;

class DeprecatedException extends Exception {

  public static function fromClassMethod($class, $method) {
    return new static(sprintf('The classmethod %s::%s is deprecated', $class, $method));
  }

  public static function fromClassMethodParam($class, $method, $paramNum, $msg) {
    return new static(sprintf('The parameter #%d from classmethod %s::%s is deprecated: %s', $paramNum, $class, $method, $msg));
  }
}
