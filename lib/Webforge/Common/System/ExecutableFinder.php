<?php

namespace Webforge\Common\System;

use Symfony\Component\Process\ExecutableFinder AS SymfonyExecutableFinder;

class ExecutableFinder {

  private $finder;

  public function __construct(Array $executables, SymfonyExecutableFinder $finder = NULL) {
    $this->executables = $executables;
    $this->finder = $finder ?: new SymfonyExecutableFinder();
  }
  
  /**
   * @return File
   * @throws NoExecutableFoundException
   */
  public function getExecutable($name) {
    $cmd = $this->getConfigExecutable($name);

    if ($cmd === NULL) {
      $cmd = $this->finder->find($name);
    }
    
    if (empty($cmd)) {
      throw ExecutableNotFoundException::fromCommand($name);
    }
    
    $file = new File($cmd);
    
    if (!$file->exists()) {
      throw ExecutableNotFoundException::fromFile($file);
    }
    
    return $file;
  }

  /**
   * @return bool
   */
  public function findsExecutable($name) {
    try {
      $file = $this->getExecutable($name);
      return TRUE;
    
    } catch (ExecutableNotFoundException $e) {
      return FALSE;
    }
  }

  /**
   * @return String|NULL
   */
  protected function getConfigExecutable($name) {
    if (array_key_exists($name, $this->executables)) {
      return $this->executables[$name];
    }
  }
}
