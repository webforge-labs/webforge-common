<?php

namespace Webforge\Common\DataStructure;

use InvalidArgumentException;
use Webforge\Common\Util;

/**
 * A map from a variable number of keys to values
 * 
 */
class KeysMap {
  
  const DO_THROW_EXCEPTION = '__KeysMap::should_throw_exception';
  
  /**
   * @var array
   */
  protected $data;  
  
  public function __construct(Array &$data = NULL) {
    if (!isset($data)) {
      $this->data = array();
    } else {
      $this->data =& $data;
    }
  }
  
  
  /**
   * Returns the value for the specified keys
   * 
   * kann keine Defaults sondern nur return NULL oder eine Exception schmeissen
   * @param array|string when string the keys may be separated with .
   * @param mixed $default when set will be returned instead of an exception thrown
   */
  public function get($keys, $default = self::DO_THROW_EXCEPTION) {
    $keys = $this->resolveKeys($keys);
    $data = $this->data;
  
    foreach ($keys as $key) {
      if (is_array($key)) {
        throw new InvalidArgumentException('There are only strings as keys allowed. '.Util::varInfo($keys));
      }
      
      if (is_array($data) && array_key_exists($key, $data)) {
        $data = $data[$key];

      } elseif (func_num_args() > 1 && $default !== self::DO_THROW_EXCEPTION) {
        return $default;

      } else {
        throw KeysNotFoundException::fromKeys($keys);
      }
    }
    
    return $data;
  }

  /**
   * Puts a value with to specified keys
   * 
   * if $keys is empty than $value must be an array and will replace all root values
   * @chainable
   */
  public function set($keys, $value) {
    $keys = $this->resolveKeys($keys);
    $data =& $this->data;
    
    if ($keys === array()) {
      if (!is_array($value)) {
        throw new InvalidArgumentException('If keys are not provided the value does replace the array. But value is not an array '.Util::varInfo($value));
      }
      
      return $data = $value;
    }
    
    $lastKey = array_pop($keys);
    foreach ($keys as $key) {
       // !is_array is when a already set value is overriden by a deeoper path
       if (!is_array($data)) {
         $data = array();
       }

      if (!array_key_exists($key, $data)) { 
        $data[$key] = array();
      }

      $data =& $data[$key];
    }

    $data[$lastKey] = $value;
    
    return $this;
  }
  
  /**
   * Removes a value from specified keys
   *
   * childrens of the path will be removed, too!
   * @chainable
   */
  public function remove($keys) {
    $keys = $this->resolveKeys($keys);
    $data =& $this->data;
    
    if ($keys === array()) {
      return $this;
    }
    
    $lastKey = array_pop($keys);
    foreach ($keys as $key) {
      if (!is_array($data) || !array_key_exists($key, $data)) {
        return $this;
      }
      $data =& $data[$key];
    }

    if (is_array($data) && array_key_exists($lastKey, $data)) 
      unset($data[$lastKey]);
    
    return $this;
  }
  
  /**
   * Checks if a value from the specified keys is set
   * 
   * notice: NULL is also a legit value for a path of keys
   * @return bool
   */
  public function contains($keys) {
    try {
      $this->get($keys, $do = self::DO_THROW_EXCEPTION);
      
      return TRUE;
    } catch (KeysNotFoundException $e) {
      return FALSE;
    }
  }
  
  /**
   * Merges two Maps together
   */
  public function merge(KeysMap $otherMap, Array $fromKeys = array(), $toKeys = array()) {
    $ownData = $this->get($toKeys, array());
    $foreignData = $otherMap->get($fromKeys, array());

    $this->set($toKeys, array_replace_recursive($ownData, $foreignData));
    
    return $this;
  }
  
  /**
   * @return bool
   */
  public function isEmpty() {
    return count($this->data) == 0;
  }
  
  /**
   * Returns all Data as an array
   *
   * @return array
   */
  public function toArray() {
    return (array) $this->data;
  }

  /**
   * @return array
   */
  protected function resolveKeys($keys) {
    if (is_array($keys)) {
      return $keys;
    } elseif (is_string($keys)) {
      return explode('.', $keys);
    } else {
      throw new InvalidArgumentException('Parameter keys can only be a string (seperated with .) or an array of strings. Given: '.Util::varInfo($keys));
    }
  }
}
