<?php

namespace Webforge\Common;

use IteratorAggregate;
use ArrayIterator;

class TraversableCollection implements IteratorAggregate {

  protected $elements;

  public function __construct($elements) {
    $this->elements = $elements;
  }

  public function getIterator() {
    return new ArrayIterator($this->elements);
  }
}