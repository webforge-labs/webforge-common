<?php

namespace Webforge\Common\DateTime;

/**
 *
 *
   $bench = new TimeBenchmark();
   sleep(1);
   $bench->end();

   $this->assertGreaterThanOrEqual( 1, $bench->getTime(TimeBenchmark::SECONDS));
 */
class TimeBenchmark extends \Psc\Object {
  
  const DONT_START = FALSE;
  const MICROSECONDS = 'µs';
  const MILLISECONDS = 'ms';
  const SECONDS = 's';
  
  /**
   * @var list($seconds, $microseconds)
   */
  protected $start = NULL;

  /**
   * @var list($seconds, $microseconds)
   */
  protected $end = NULL;
  
  public function __construct($start = TRUE) {
    if ($start) $this->start();  
  }
  
  public function start() {
    $t = gettimeofday();
    $this->start = array((int) $t['sec'], (int) $t['usec']);
    return $this;
  }
  
  
  public function end($format = NULL) {
    $t = gettimeofday();
    $this->end = array((int) $t['sec'], (int) $t['usec']);
    return $this;
  }
  
  public function stop($format = NULL) {
    return $this->end($format);
  }
  
  public function __toString() {
    if (isset($this->end)) {
      $seconds = $this->getTime(self::SECONDS);
      return 'measured time: '.Time::formatSpan($seconds, '%H:%I:%S.%n');
    }
    
    return '';
  }
  
  /**
   * Gibt die verganene Zeit des Benchmarks zurück
   *
   * Ist vorher nicht end() aufgerufen worden, wird die aktuelle Zeit als Timestamp benutzt und der Benchmark läuft weiter
   * @return float
   */
  public function getTime($format = self::MILLISECONDS) {
    $end = (isset($this->end)) ? $this->end : microtime();
    
    list ($endSeconds, $endMicro) = $end;          
    list ($startSeconds, $startMicro) = $this->start;
    
    $microseconds = ($endSeconds - $startSeconds) * pow(10,6) + abs($endMicro - $startMicro);
    
    if ($format == self::MICROSECONDS) {
      return $microseconds;
    } elseif ($format == self::MILLISECONDS) {
      return $microseconds / pow(10,3);
    } elseif ($format == self::SECONDS) {
      return $microseconds / pow(10,6);
    } else {
      throw new \Psc\Exception('Falscher Parameter für format');
    }
  }
  
  /**
   * Gibt die vergangene Zeit zurück und beendet den Benchmark
   * 
   */
  public function getTimeEnd($format = self::MILLISECONDS) {
    $this->end();
    return $this->getTime($format);
  }
}

?>