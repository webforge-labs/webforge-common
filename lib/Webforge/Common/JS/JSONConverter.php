<?php

namespace Webforge\Common\JS;

class JSONConverter {
  
  const EMPTY_ALLOWED = 0x000001;
  const PRETTY_PRINT =  0x000002;

  public static function create() {
    return new static();
  }
    
  public function stringify($data, $flags = 0) {
    $json = json_encode($data);
    
    if ($flags === TRUE || $flags & self::PRETTY_PRINT) {
      $json = Helper::reformatJSON($json);
    }
    
    return $json;
  }
  
  public function prettyPrint($json) {
    return Helper::reformatJSON($json);
  }
  
  public function parse($json, $flags = self::EMPTY_ALLOWED) {
    $json_errors = array(
      JSON_ERROR_NONE => 'Es ist kein Fehler zuvor aufgetreten, aber der Array ist leer. Es kann mit dem 2ten Parameter TRUE umgangen werden, dass der Array überprüft wird',
      JSON_ERROR_DEPTH => 'Die maximale Stacktiefe wurde erreicht',
      JSON_ERROR_CTRL_CHAR => 'Steuerzeichenfehler, möglicherweise fehlerhaft kodiert',
      JSON_ERROR_SYNTAX => 'Syntax Error',
    );
    
    $data = json_decode($json);

    if (($flags & self::EMPTY_ALLOWED) && (is_array($data) || is_object($data))) {
      return $data;
    }
    
    if (empty($data)) {
      throw new JSONParsingException(
        sprintf("JSON Parse Error: %s für JSON-String: '%s' ", $json_errors[json_last_error()], \Psc\String::cut($json,100,'...'))
      );
    }
    
    return $data;
  }
  
  public function parseFile(\Psc\System\File $file, $flags = self::EMPTY_ALLOWED) {
    return self::parse($file->getContents(), $flags);
  }
}
?>