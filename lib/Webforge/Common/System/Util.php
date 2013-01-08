<?php

namespace Webforge\Common\System;

class Util {
  
  /**
   * Returns if the real physical Engine where PHP runs is a Windows-System
   * @return bool
   */
  public static function isWindows() {
    return substr(PHP_OS, 0, 3) == 'WIN';
  }
  
}
?>