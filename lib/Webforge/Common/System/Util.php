<?php

namespace Webforge\Common\System;

use Symfony\Component\Process\PhpExecutableFinder;

class Util {
  
  /**
   * Returns if the real physical Engine where PHP runs is a Windows-System
   * @return bool
   */
  public static function isWindows() {
    return substr(PHP_OS, 0, 3) == 'WIN';
  }
  
  /**
   * @return Webforge\Common\System\File
   */
  public static function findPHPBinary() {
    $finder = new PhpExecutableFinder();
    return new File($finder->find());
  }
}
?>