<?php

namespace Webforge\Common\System;

class ExecutableNotFoundException extends \Webforge\Common\Exception\FileNotFoundException {

  public static function fromFile(File $file, $msg = NULL, $code = 0) {
    return parent::fromFile($file, 'Executable was found in config but %s does not exist');
  }

  public static function fromCommand($command) {
    return new static('Command '.$command.' cannot be found on system');
  }
}
