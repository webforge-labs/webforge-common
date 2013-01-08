<?php

namespace Webforge\Common;

use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase {
  
  // implement this correctly when we have a solution for the Code\Test\Base Class
  public function getFile($name) {
    return $GLOBALS['env']['root']->sub('tests/files/')->getFile($name);
  }
}
?>
