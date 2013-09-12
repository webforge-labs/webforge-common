<?php

namespace Webforge\Common\System;

/**
 * A System that is able to execute commands
 * 
 * it has an own set of Environment Variables
 * it runs in a current working directory
 * it has a operating system
 * 
 * it can create processes with process() which than can be run()
 * it can simply exec() a process and return its output
 */
interface ExecutionSystem {

  const WINDOWS = Util::WINDOWS;
  const UNIX = Util::UNIX;

  /**
   * Executes a process
   * 
   * use Symfony\Component\Process\Process;
   * 
   * $system->exec('ls -la', function ($type, $buffer) {
   *   if (Process::ERR === $type) {
   *     echo 'ERR > '.$buffer;
   *   } else {
   *     echo 'OUT > '.$buffer;
   *   }
   * });
   * 
   * or pass other options inbetween:
   * 
   * $system->exec("grep 'something'", array('stdin'=>'this might be something. Or something?'), function ($type, $buffer) {
   *   if (Process::ERR === $type) {
   *     echo 'ERR > '.$buffer;
   *   } else {
   *     echo 'OUT > '.$buffer;
   *   }
   * });
   * 
   * @param string $commandLine the full commandline needs to be quoted for the current operating system
   * @return integer exitcode
   * 
   */
  public function exec($commandline, $options = NULL, $runCallback = NULL);

  /**
   * Builds a process
   * 
   * options:
   * 
   *   'stdin' => string, a plain string to pipe into the command
   *   'env' => array, an array of env vars to be defined
   *   ''
   * 
   * @param array|object $options se above
   * @return Symfony\Component\Process\Process
   */
  public function process($commandline, $options = NULL);


  /**
   * @return self::WINDOWS or self::UNIX
   */
  public function getOperatingSystem();
}
