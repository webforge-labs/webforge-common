<?php

namespace Webforge\Common\System;

use Symfony\Component\Process\PhpExecutableFinder;
use Webforge\Common\String as S;

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
   * it does not strip multibytes (on windows)
   * it does not replace " with ' ' on windows
   * it does not replace % with ' ' on windows
   *
   * you got still no chance to give the literal argument %defined%  if the env variabled "defined" is set.
   *
   * for unix the default escapeshellarg is used (it does strip multibytes)
   *
   * as the php escapeshellarg, on windows " is used and ' is used
   * @return string
   */
  public static function escapeShellArg($arg, $escapeFor = NULL) {
    // ported: PHPAPI char *php_escape_shell_arg(char *str)
    if (!isset($escapeFor)) $escapeFor = self::isWindows() ? self::WINDOWS : self::UNIX;

    if ($escapeFor === self::WINDOWS) {
      $q = '"';
      $bs = '\\';

      $escapedArg = $q.str_replace($q, $bs.$q, $arg).$q;

      if (S::endsWith($escapedArg, $bs.$q)) {
        $escapedArg = substr_replace($escapedArg, $bs.$bs.$q, -2);
      }

      return $escapedArg;
    
    } else {
/*
 * char* arg is the to copied string
 *
   case '\'':
    arg[y++] = '\'';
    arg[y++] = '\\';
    arg[y++] = '\'';

    that looks weird to me: escape ' with '\'
    e.g.: he said it isn't his fault
         'he said it isn'\'t his fault'
    
    well.. they will know..
*/
      // this will strap multibytes(!)
      return escapeshellarg($arg);
      //$arg = str_replace("'", "'\\'", $arg);
    }
  }
}
