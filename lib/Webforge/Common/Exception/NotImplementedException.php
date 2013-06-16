<?php

namespace Webforge\Common\Exception;

class NotImplementedException {

  public static function fromString($that) {
    return new static(sprintf("Behaviour for '%s' is not implemented, yet", $that));
  }
}
