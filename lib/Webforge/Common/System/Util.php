<?php

namespace Webforge\Common\System;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;
use Webforge\Common\Preg;

class Util {

  const WINDOWS = 'windows';
  const UNIX = 'unix';

  protected $executableFinder;

  public function __construct(ExecutableFinder $executableFinder) {
    $this->executableFinder = $executableFinder;
  }

  /**
   * Finds a system configurable command
   * 
   * you can put into your host-config:
   * 
   * executables.$name = /full/path/to/command/with/$name
   * @return File to the binary
   */
  public function findCommand($name) {
    return $this->executableFinder->getExecutable($name);
  }
  
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

  /**
   * This escapes shell arguments on windows correctly
   * 
   * @return string
   */
  public static function escapeShellArg($arg, $escapeFor = NULL) {
    $escaped = \Symfony\Component\Process\ProcessUtils::escapeArgument($arg);

    return Preg::replace($escaped, '~(?<!\\\\)\\\\"$~', '\\\\\\"');
  }
}
