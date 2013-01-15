<?php

namespace Webforge\Common\System;

use Webforge\Common\String as S;
use Webforge\Common\Preg;
use Webforge\Common\ArrayUtil AS A;
use Webforge\Common\DateTime\DateTime;

/**
 *
 * translate API
 * Convention: every path has a trailing slash
 */
class Dir {
  
  const WITHOUT_TRAILINGSLASH = 0x000001;

  const SORT_ALPHABETICAL = 2;

  const ORDER_ASC = 1024;
  const ORDER_DESC = 2048;

  const RECURSIVE = 2;
  
  const RELATIVE = 'relative';
  
  const ASSERT_EXISTS       = 0x000010;
  const PARENT              = 0x000020;

  /**
   * The default chmod for new directories
   *
   * @var octal $mode
   */
  static $defaultMod = 0744;

  /**
   * Path of the directory
   * 
   * @var array all names of the subdirectories and the name itself
   */
  protected $path = array();

  /**
   * The name of the streamwrapper if path is wrapped
   * 
   * @var string 
   */
  protected $wrapper;

  /**
   * Globale ignores for directories
   * 
   * @param array
   * @see getContents()
   */
  public $ignores = array();

  /**
   * Create a new Instance of a directory
   *
   * directories do not have to exist
   * @param string|Dir $path
   */
  public function __construct($path = NULL) {
    if ($path instanceof Dir) {
      $path = $path->getPath();
    }
    
    if (isset($path)) {
      $this->setPath($path);
    }
  }


  /**
   * Returns a new directory with $path
   * 
   * @param string $path with trailing slash
   * @return Dir
   */
  public static function factory($path = NULL) {
    return new static($path);
  }
  
  /**
   * Returns a new directory with $path but $path does not have to be trailing slashed
   *
   * @param string $path does not need to have trailingslash
   */
  public static function factoryTS($path = NULL) {
    if (!isset($path)) {
      return new static(NULL);
    } else {
      return new static(ltrim($path, '\\/').DIRECTORY_SEPARATOR);
    }
  }
  
  /**
   * Creates a temporary Directory
   */
  public static function createTemporary() {
    $file = File::createTemporary();
    $tempname = $file->getName();
    $file->delete();
    
    $dir = $file->getDirectory()->sub($tempname.'/');
    $dir->make();
    return $dir;
  }
  
  /**
   * 
   * @param string $path mit trailin DIRECTORY_SEPERATOR
   */
  public function setPath($path) {
    $path = trim($path); // whitespace cleanup
    
    $lastChar = mb_substr($path,-1);
    if ($lastChar !== '\\' && $lastChar !== '/') {
      throw new Exception($path.' endet nicht mit einem (back-)slash.');
    }
    
    $pathArray = array();
    if (mb_strlen($path) > 0) {
      
      $path = $this->extractWrapper($path);

      // a / can only mean a \ on windows (UNLESS its wrapped!)
      if (DIRECTORY_SEPARATOR === '\\' && !$this->isWrapped()) {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      }
      
      $ds = $this->isWrapped() ? '/' : DIRECTORY_SEPARATOR;
      
      if (mb_strpos($path,$ds) !== FALSE) {
        $pathArray = explode($ds,$path);
      } else {
        $pathArray[] = $path;
      }

      /* leere elemente rausfiltern (safe) */
      $pathArray = array_filter($pathArray,create_function('$a','return (mb_strlen($a) > 0);'));
      $pathArray = array_merge($pathArray); // renumber
      $this->path = $pathArray;
    }
    
    return $this;
  }

  /**
   * @return $path (verkürzt um den wrapper)
   */
  public function extractWrapper($path) {
    $m = array();
    if (Preg::match($path, '|^([a-z\.0-9]+)://(.*)$|', $m) > 0) {
      $this->setWrapper($m[1]);
      $path = rtrim($m[2],'\\/').'/'; // cleanup trailing-backslash
      // noch gegen stream_get_wrappers(); validieren?
    }
    return $path;
  }
  
  public static function isWrappedPath($path) {
    return Preg::match($path, '|^([a-z\.0-9]+)://(.*)$|') > 0;
  }
  
  /**
   * @return string ohne :// dahinter
   */
  public function getWrapper() {
    return $this->wrapper;
  }
  
  /**
   * @param string der name des wrapper
   */
  public function setWrapper($wrapperName) {
    $this->wrapper = $wrapperName;
  }
  
  /**
   * Wraps the dir with the wrapper and converts windows paths
   */
  public function wrapWith($wrapperName) {
    $this->setWrapper($wrapperName);
    
    if ($this->getOS() === 'WINDOWS') {
      array_unshift($this->path, NULL); // dirty hack?
    }
    return $this;
  }

  /**
   * @return bool
   */
  public function isWrapped() {
    return $this->wrapper !== NULL;
  }

  /**
   * Wandelt relative Bezüge im Pfad des Verzeichnis in konkrete um
   *
   * löst z.b. sowas wie ./ oder ../ auf.<br />
   * Kann dafür benutzt werden das aktuelle Verzeichnis als Objekt zu erhalten:
   * <code>
   *   print Dir::factory('.')->resolvePath();
   * </code>
   * @uses PHP::getcwd()
   * @chainable
   */
  public function resolvePath() {
    if (count($this->path) == 0) {
      return $this;
    }
    
    if ($this->path[0] == '.' || $this->path[0] == '..') { 
      /* wir ermitteln das aktuelle working directory und fügen dieses vor unserem bisherigen Pfad hinzu
       * den . am anfang brauchen wir nicht machen, das wird nachher normalisiert
       */
      $cwd = self::factory(getcwd().DIRECTORY_SEPARATOR);
      $this->path = array_merge(
        $cwd->getPathArray(), 
        $this->path
      ); 
    }

    /* pfad normalisieren */
    $newPath = array();
    foreach ($this->path as $dir) {
      if ($dir !== '.') { // dir2/dir1/./dir4/dir3 den . ignorieren
        if ($dir == '..') {  // ../ auflösen dadurch, dass wir ein verzeichnis zurückgehen
          array_pop($newPath);
        } else {
          $newPath[] = $dir;
        }
      }
    }
        
    $this->path = $newPath;

    return $this;
  }

  /**
   * Löst einen Pfad zu einem absoluten auf
   *
   * der Pfad ist ein string mit forwardslashes. beginnt er mit . oder mit .. wird er relativ zum objekt-path gesehen und angehängt
   * ansonsten wird er absolut interpretiert.
   * @param string $path
   * @return Dir (neue instance)
   */
  public function expand($path) {
    if (mb_strpos($path, '.') === 0) {
      return $this->sub($path);
    } else {
      return self::factory($path);
    }
  }

  /**
   * Verkürzt einen Pfad in Bezug auf ein anderes Verzeichnis
   * 
   * Die Funktion kann z.b. dafür benutzt zu werden aus einem absoluten Verzeichnis ein Relatives zu machen.<br />
   * Die Umwandlung in ein relatives Verzeichnis geschieht in Bezug auf das angegebene Verzeichnis.<br />
   * Wenn das aktuelle Verzeichnis ein Unterverzeichnis des angegebenen ist, wird das Verzeichnis in ein relatives 
   * umgewandelt (sofern es das nicht schon war) und der Pfad bis zum angegeben Verzeichnis verkürzt.
   * @param Dir $dir das Verzeichnis zu welchem Bezug genommen werden soll
   */
  public function makeRelativeTo(Dir $dir) {
    $dir = clone $dir;
    $removePath = (string) $dir->resolvePath();
    $thisPath = (string) $this->resolvePath();
    
    if (!S::startsWith($thisPath,$removePath) || mb_strlen($thisPath) < mb_strlen($removePath))
      throw new Exception('Das Verzeichnis ('.$thisPath.')[1] muss in ('.$removePath.')[2] sein. Kann [1] nicht relatv zu [2] machen, da [2] zu lang ist. Vielleicht Argumente falsch rum?');

    if ($removePath == $thisPath) {
      $this->setPath('.'.DIRECTORY_SEPARATOR); // das Verzeichnis ist relativ gesehen zu sich selbst das aktuelle Verzeichnis
      return $this;
    }

    /* schneidet den zu entfernen pfad vom aktuellen ab */
    $this->path = array_slice($this->path, count($dir->getPathArray()));
    
    /* ./ hinzufügen */
    array_unshift($this->path,'.');

    return $this;
  }
  
  /**
   * Gibt die URL zum Verzeichnis zurück
   *
   * das root Verzeichnis muss angegeben werden
   * URL hat keinen Trailingslash! aber einen slash davor
   */
  public function getURL(Dir $relativeDir = NULL) {
    if (!isset($relativeDir)) $relativeDir = new Dir('.'.DIRECTORY_SEPARATOR);
    
    $rel = clone $this;
    $rel->makeRelativeTo($relativeDir);
    $pa = $rel->getPathArray();
    unset($pa[0]);
    
    return '/'.implode('/',array_map('rawurlencode',$pa)); 
  }
  
  /**
   * Überprüft ob wir Unterverzeichnis eines anderen sind
   *
   * Gibt TRUE zurück wenn $this ein Unterverzeichnis von $parent ist
   * Gibt TRUE zurück wenn $this in $parent enthalten ist
   *
   * gibt FALSE zurück wenn $this und $parent gleich sind
   *
   * ansonsten False
   * @param Dir $parent das Oberverzeichnis zu überprüfen, wenn dies mit $this.equals() wird auch false zurückgegeben
   */
  public function isSubdirectoryOf(Dir $parent) {
    $parentPath = (string) $parent->resolvePath();
    $thisPath = (string) $this->resolvePath();
    
    return S::startsWith($thisPath,$parentPath) && mb_strlen($parentPath) < mb_strlen($thisPath); //das hintere schließt Gleichheit aus
  }

  /**
   * Fügt dem aktuellen Verzeichnis-Pfad ein Unterverzeichnis oder mehrere (einen Pfad) hinzu
   *
   * Wenn $dir der String ".." ist wird ins ParentDir gewechselt (sofern dies möglich ist)
   * Das angegebene Verzeichnis ist ein relatives Verzeichnis und dessen Pfad wird hinzugefügt
   *
   * wenn $dir eine File ist, wird das subverzeichnis angehängt und eine Referenz auf die Datei mit demselben Namen im Verzeichnis zurückgegeben, dabei muss das Verzeichnis von $file ein relatives Dir sein
   * 
   * $dir->append('subdir/');
   * $dir->append('./banane/tanne/apfel/');
   * @param string|Dir $dir das Verzeichnis muss relativ sein
   * @chainable
   */
  public function append($dir) {
    if ($dir == NULL) return $this;
    if ($dir == '..' && count($this->path) >= 1) {
      array_pop($this->path);
      // clearcache
      return $this;
    }
    
    if (!($dir instanceof \Psc\System\Dir)) {
      $dir = (string) $dir;
      if (!s::endsWith($dir,'/')) $dir .= '/';
      
      $dir = str_replace('/',DIRECTORY_SEPARATOR,$dir);
      $dir = new Dir($dir);
    }

    foreach ($dir->getPathArray() as $part) {
      if ($part == '.') continue;
      $this->path[] = $part;
    }
    return $this;
  }
  
  /**
   * Returns a copy of the instance from a subdirectory
   *
   * @param string $subDirUrl with / at the end and / inbetween (dont' use backslash!)
   * @return Dir
   */
  public function sub($subDirUrl) {
    $sub = clone $this;
    return $sub->append($subDirUrl);
  }
  
  /**
   * Returns a copy of the instance of the parent directory
   *
   * @return Dir
   */
  public function up() {
    $up = clone $this;
    return $up->append('..');
  }
  
  /**
   * Slices parts of the path out (modifies the state)
   *
   * @chainable
   */
  public function slice($start, $length = NULL) {
    if (func_num_args() == 1) {
      $this->path = array_slice($this->path, $start);
    } else {
      $this->path = array_slice($this->path, $start, $length);
    }
    return $this;
  }

  /**
   * Returns a new instance from this directory
   */
  public function clone_() {
    return clone $this;
  }
  
  /**
   * Gibt einen Array über die Verzeichnisse und Dateien im Verzeichnis zurück
   * 
   * Ignores:<br />
   * bei den Ignores gibt es ein Paar Dinge zu beachten: Es ist zu beachten, dass strings in echte Reguläre Ausdrücke umgewandelt werden. Die Delimiter für die Ausdrücke sind // 
   * Der reguläre Ausdruck wird mit ^ und $ ergänzt. D.h. gibt man als Array Eintrag '.svn' wird er umgewandelt in den Ausdruck '/^\.svn$/' besondere Zeichen werden gequotet
   * Wird der Delimiter / am Anfang und Ende angegeben, werden diese Modifikationen nicht gemacht<br />
   * Diese Ignore Funktion ist nicht mit Wildcards zu verwechseln (diese haben in Regulären Ausdrücken andere Funktionen).
   * 
   * Ignores von unserem Verzeichnis werden an die Unterverzeichnisse weitervererbt.
   *
   * Extensions: <br />
   * Wird extensions angegeben (als array oder string) werden nur Dateien (keine Verzeichnisse) mit dieser/n Endungen in den Array gepackt.
   * Ignores werden trotzdem angewandt.
   * 
   * @param array|string $extensions ein Array von Dateiendungen oder eine einzelne Dateiendung
   * @param array $ignores ein Array von Regulären Ausdrücken, die auf den Dateinamen/Verzeichnisnamen (ohne den kompletten Pfad) angewandt werden
   * @param int $sort eine Konstante die bestimmt, wie die Dateien in Verzeichnissen sortiert ausgegeben werden sollen
   * @return Array mit Dir und File
   */
  public function getContents($extensions = NULL, Array $ignores=NULL, $sort = NULL, $subDirs = NULL) {
    if (!$this->exists())
      throw new Exception('Verzeichnis existiert nicht: '.$this);
      
    if (!is_bool($subDirs))
      $subDirs = !isset($extensions); // subDirs werden per Default durchsucht wenn extensions nicht angegeben ist

    $handle = opendir((string) $this);
      
    if ($handle === FALSE) {
      throw new Exception('Fehler beim öffnen des Verzeichnisses mit opendir(). '.$this);
    }

    /* ignore Dirs schreiben */
    if (isset($this->ignores) || $ignores != NULL) {
      $ignores = array_merge($this->ignores, (array) $ignores);

      foreach ($ignores as $key=>$ignore) {
        if (!S::startsWith($ignore,'/') || !S::endsWith($ignore,'/'))
          $ignore = '/^'.$ignore.'$/';
          
        $ignores[$key] = $ignore;
      }

      $callBack = array('Webforge\Common\Preg','match');
    }
    
    $content = array();
    while (FALSE !== ($filename = readdir($handle))) {
      if ($filename != '.' && $filename != '..' && ! (isset($callBack) && count($ignores) > 0 && array_sum(array_map($callBack,array_fill(0,count($ignores),$filename),$ignores)) > 0)) {  // wenn keine ignore regel matched
          
        if (is_file($this->to_string().$filename)) {
          $file = new File(clone $this,$filename); // denn die unterverzeichnisse werden auch gecloned (wegen to_string)

          if (isset($extensions) && (is_string($extensions) && $file->getExtension() != ltrim($extensions,'.') || is_array($extensions) && !in_array($file->getExtension(), $extensions)))
            continue;

          $content[] = $file;
        }
          
        if (is_dir($this->to_string().$filename) && $subDirs) { // wenn extensions gesetzt ist, keine verzeichnisse, per default
          $directory = new Dir($this->to_string().$filename.$this->getDS());
          $directory->ignores = array_merge($directory->ignores,$ignores); // wir vererben unsere ignores

          $content[] = $directory;
        }
      }
    }
    closedir($handle);

    if ($sort !== NULL) {
        
      if ($sort & self::ORDER_ASC) {
        $order = 'asc';
      } elseif ($sort & self::ORDER_DESC) {
        $order = 'desc';
      } else {
        $order = 'asc';
      }

      /* alphabetisch sortieren */
      if ($sort & self::SORT_ALPHABETICAL) {
          
        if ($order == 'asc') {
          $function = create_function('$a,$b',
                                      'return strcasecmp($a->getName(),$b->getName()); ');
        } else {
          $function = create_function('$a,$b',
                                      'return strcasecmp($b->getName(),$a->getName()); ');
        }

        uasort($content, $function);
      }
    }

    return $content;
  }


  /**
   * Gibt alle Dateien (auch in Unterverzeichnissen) zurück
   * 
   * für andere Parameter siehe getContents()
   * @param bool $subdirs wenn TRUE wird auch in Subverzeichnissen gesucht
   * @see getContents()
   */
  public function getFiles($extensions = NULL, Array $ignores = NULL, $subdirs = TRUE) {
    if (is_string($extensions) && mb_strpos($extensions,'.') === 0)
      $extensions = mb_substr($extensions,1);
    /* wir starten eine Breitensuche (BFS) auf dem Verzeichnis */
    
    $files = array();
    $dirs = array(); // Verzeichnisse die schon besucht wurden
    $queue = array($this);

    while (count($queue) > 0) {
      $elem = array_pop($queue);

      /* dies machen wir deshalb, da wenn extension gesetzt ist, keine verzeichnisse gesetzt werden */
      foreach($elem->getContents(NULL,$ignores) as $item) {
        if ($item instanceof Dir && !in_array((string) $item, $dirs)) { // ist das verzeichnis schon besucht worden?

          if ($subdirs) // wenn nicht wird hier nichts der queue hinzugefügt und wir bearbeiten kein unterverzeichnis
            array_unshift($queue,$item);

          /* besucht markieren */
          $dirs[] = (string) $item;
        }
      }
      
      foreach($elem->getContents($extensions,$ignores) as $item) {
        if ($item instanceof File) {
          $files[] = $item;
        }
      }
    }
    
    return $files;
  }

  /**
   * Gibt alle Unterverzeichnisse (auch in Unterverzeichnissen) zurück
   * 
   * für andere Parameter siehe getContents()
   * @param bool $subdirs wenn TRUE wird auch in Subverzeichnissen gesucht, sonst werden nur verzeichnisse der ebene 1 ausgegeben
   * @see getContents()
   */
  public function getDirectories(Array $ignores = NULL, $subdirs = TRUE) {
    /* wir starten eine Breitensuche (BFS) auf dem Verzeichnis */
    
    $dirs = array(); // Verzeichnisse die schon besucht wurden
    $queue = array($this);

    while (count($queue) > 0) {
      $elem = array_pop($queue);

      /* dies machen wir deshalb, da wenn extension gesetzt ist, keine verzeichnisse gesetzt werden */
      foreach($elem->getContents(NULL,$ignores) as $item) {
        if ($item instanceof Dir && !array_key_exists((string) $item, $dirs)) { // ist das verzeichnis schon besucht worden?

          if ($subdirs) // wenn nicht wird hier nichts der queue hinzugefügt und wir bearbeiten kein unterverzeichnis
            array_unshift($queue,$item);

          /* besucht markieren */
          $dirs[(string) $item] = $item;
        }
      }
    }
    
    return $dirs;
  }

  /**
   * Setzt die Zugriffsrechte des Verzeichnisses
   * 
   * Z.b. $file->chmod(0644);  für // u+rw g+rw a+r
   * @param octal $mode 
   * @param int $flags
   * @chainable
   */
  public function chmod($mode, $flags = NULL) {
    $ret = chmod((string) $this,$mode);
    
    if ($ret === FALSE)
      throw new Exception('chmod für '.$this.' auf '.$mode.' nicht möglich');
      

    if ($flags & self::RECURSIVE) {
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->chmod($mode, $flags);
        }
      }
    }
    
    return $this;
  }

  /**
   * Löscht das Verzeichnis rekursiv
   * 
   * @chainable
   */
  public function delete() {
    if ($this->exists()) {
      
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->delete(); // rekursiver aufruf für Dir
        }
      }
      
      @rmdir((string) $this); // selbst löschen
    }

    return $this;
  }


  /**
   * Löscht die Inhalt des Verzeichnis rekursiv
   * 
   * @chainable
   */
  public function wipe() {
    if ($this->exists()) {
      foreach ($this->getContents() as $item) {
        if (is_object($item) && ($item instanceof File || $item instanceof Dir)) {
          $item->delete(); // rekursiver aufruf für Dir
        }
      }
    }

    return $this;
  }

  /**
   * Copies all Files *in* $this to $destination
   * 
   * @param Dir $destination
   * @chainable
   */
  public function copy(Dir $destination, $extensions = NULL, $ignores = NULL, $subDirs = NULL) {
    if ((string) $destination == (string) $this)
      throw new Exception('Kann nicht kopieren: Zielverzeichnis und Quellverzeichns sind gleich.');

    if (!$destination->exists())
      $destination->create();

    foreach ($this->getContents($extensions, $ignores, NULL, $subDirs) as $item) {
      if ($item instanceof File) {
        $destFile = clone $item;
        $destFile->setDirectory($destination);
        $item->copy($destFile); 
      }
        
      if ($item instanceof Dir) {
        $relativeDir = clone $item; 
        $relativeDir->makeRelativeTo($this);
        
        $destDir = clone $destination;
        $destDir->append($relativeDir); // path/to/destination/unterverzeichnis
        $item->copy($destDir); //rekursiver Aufruf
      }
    }
    return $this;
  }



  /**
   * Moves the directory and changes its internal state
   * 
   * @param Dir $destination
   * @chainable
   */
  public function move(Dir $destination) {
    $ret = @rename((string) $this,(string) $destination);

    $errInfo = 'Kann Verzeichnis '.$this.' nicht nach '.$destination.' verschieben / umbenennen.';
    
    if (!$ret) {
      if ($destination->exists())
        throw new Exception($errInfo.' Das Zielverzeichnis existiert.');

      if (!$this->exists())
        throw new Exception($errInfo.' Das Quellverzeichnis existiert nicht.');
      else 
        throw new Exception($errInfo);
    }


    /* wir übernehmen die Pfade von $destination */
    $this->path = $destination->getPathArray();
    return $this;
  }

  /**
   * Creates the full path to the directory, if it does not exist
   * 
   * @chainable
   */
  public function create() {
    $this->make(self::PARENT | self::ASSERT_EXISTS);
    return $this;
  }

  /**
   * Creates the Directory
   * 
   * @param bitmap $options self::PARENT to create the full path of the directory
   * @chainable
   */
  public function make($options=NULL) {
    if (is_int($options)) {
      $parent = ($options & self::PARENT) == self::PARENT;
      $assert = ($options & self::ASSERT_EXISTS) == self::ASSERT_EXISTS;
    } else {
      // legacy option
      $parent = (mb_strpos($options,'-p') !== FALSE);
      $assert = FALSE;
    }
      
    if (!$this->exists()) {
      $ret = @mkdir((string) $this, self::$defaultMod, $parent);
      if ($ret == FALSE) {
        throw new Exception('Fehler beim erstellen des Verzeichnisses: '.$this);
      }
    } else {
      if (!$assert) {
        throw new Exception('Verzeichnis '.$this.' kann nicht erstellt werden, da es schon existiert');
      }
    }

    return $this;
  }


  /**
   * Copy all files from this dir into another
   *
   * wenn flat = TRUE ist, werden auch Unterverzeichnisse durchsucht. dies "flatted" die Files dann into $destination
   */
  public function copyFiles($extension, Dir $destination, $flat = FALSE, Array $ignores = NULL) {
    foreach ($this->getFiles($extension, $ignores, $flat) as $f) {
      $f->copy(new File($destination, $f->getName()));
    }
    return $this;
  }

  /**
   * Überprüft ob eine bestimmte Datei im Verzeichnis liegt (und gibt diese zurück)
   * 
   * Wird ein File Objekt übergebeben wird der Name der Datei überprüft.
   * wenn $file ein relatives Verzeichnis hat wird die datei in dem passenden relativen Subverzeichnis zurückgegebeben
   * wenn die Datei nicht existiert, passiert nichts
   * gibt immer eine Datei zurück
   * ist die Datei absolut wird eine InvalidArgumentException geworfen
   * 
   * @param string|File $file
   * @param const $relative
   * @return File
   */
  public function getFile($file) {
    if ($file instanceof File) {
      $fileName = $file->getName();
      $fileDir = $file->getDirectory();
      
      if ($fileDir->isRelative()) {
        $dir = $this->clone_()->append($file->getDirectory());
      } else {
        throw new \InvalidArgumentException('Wenn eine Datei übergeben wird, darf diese nicht absolut sein');
      }
      
    } elseif(is_string($file) && mb_strpos($file, '/') !== FALSE) {
      return File::createFromURL($file, $this);
    } else {
      $fileName = $file;
      $dir = $this;
    }

    $file = new File($fileName,$dir);
    
    return $file;
  }

  /**
   * 
   * @return bool
   */
  public function exists() {
    if (count($this->path) == 0) return FALSE;
    return is_dir((string) $this);
  }
  
  /**
   * Is the directory empty?
   *
   * a directory is empty, if it has no files or directories in it
   * a directory is empty, if it does not exist
   * @return bool
   */
  public function isEmpty() {
    return !$this->exists() || count($this->getContents()) === 0;
  }

  /**
   * @return bool
   */
  public function isWriteable() {
    if (count($this->path) == 0) return FALSE;
    return is_writable((string) $this);
  }

  /**
   * @return bool
   */
  public function isReadable() {
    if (count($this->path) == 0) return FALSE;
    return is_readable((string) $this);
  }
  
  /**
   * @return bool
   */
  public function isRelative() {
    return count($this->path) > 0 && $this->path[0] == '.';
  }

  /**
   * @return bool
   */
  public static function isAbsolutePath($path) {
    return   mb_strpos($path, '/') === 0 // unix
          || mb_strpos($path, ':') === 1 // windows C:\ etc
          || self::isWrappedPath($path); // phar:// ...
  }
  
  /**
   * Gibt den Pfad als String zurück
   * 
   * je nach Betriebssystem wird ein UNIX oder Windows Pfad zurückgegeben. <br />
   * jedes Verzeichnis ohne Trailingslash zurückgegeben.
   * @return string
   */
  public function getPath() {
    $pathString = implode($this->path, $this->getDS());
    
    if (!$this->isRelative() && $this->getOS() == 'UNIX') {
      $pathString = '/'.$pathString;
      // auch wrapped unix pfade fangen mit / davor an
    }
    
    if ($this->isWrapped()) {
      $pathString = $this->getWrapper().'://'.$pathString;
    }

    return $pathString.$this->getDS();
  }
  
  /**
   * Is the path to the other directory the same?
   * @return bool
   */
  public function equals(Dir $dir) {
    return $this->getPath() === $dir->getPath();
  }

  /**
   * @return array
   */
  public function getPathArray() {
    return $this->path;
  }

  /**
   * Returns the basename of the directory
   * 
   * @return string
   */
  public function getName() {
    if (count($this->path) > 0)
      return $this->path[count($this->path)-1];
  }
  
  /**
   * @return DateTime
   */
  public function getModifiedTime() {
    return new Datetime(filemtime((string) $this));
  }

  /**
   * @return Psc\DateTime\DateTime
   */
  public function getAccessTime() {
    return new Datetime(fileatime((string) $this));
  }

  /**
   * @return Psc\DateTime\DateTime
   */
  public function getCreateTime() {
    return new Datetime(filectime((string) $this));
  }

  /**
   * Returns the DirectorySeperator
   * 
   * @return \ oder / (bei isWrapped() true ist dies immer /
   */
  public function getDS() {
    return $this->isWrapped() ? '/' : DIRECTORY_SEPARATOR; // siehe auch bei setPath()
  }

  /**
   * 
   * @return string WINDOWS|UNIX
   */
  public function getOS() {
    if (mb_substr(PHP_OS, 0, 3) == 'WIN') {
      $os = 'WINDOWS';
    } else {
      $os = 'UNIX';
    }
    return $os;
  }


  public function to_string() {
    return $this->getPath();
  }

  public function __toString() {
    return $this->getPath();
  }
  
  public function getQuotedString($flags = 0) {
    $str = (string) $this;
    
    if ($flags & self::WITHOUT_TRAILINGSLASH) {
      $str = mb_substr($str, 0, -1);
    }
    
    if (mb_strpos($str, ' ') !== FALSE) {
      return escapeshellarg($str);
    }
    
    return $str;
  }

  /**
   * Entfernt (sofern vorhanden) den Trailingslash aus einer Pfadangabe
   * @return string die Pfadangabe ohne den Trailingslash
   */
  public static function unTrailSlash($path) {
    $unSlPath = $path;
    if (S::ends_with($path,'/') || S::ends_with($path,'\\')) {
      $unSlPath = mb_substr($path,0,-1);
    }
    return $unSlPath;
  }


  /**
   * Extrahiert das Verzeichnis aus einer Angabe zu einer Datei
   * 
   * @param string $string der zu untersuchende string
   * @return Dir
   */
  public static function extract($string) {
    if (mb_strlen($string) == 0) {
      throw new Exception('String ist leer, kann kein Verzeichnis extrahieren');
    }
    
    $path = dirname($string).DIRECTORY_SEPARATOR;
    try {
      $dir = new Dir($path);
    } catch (Exception $e) {
      throw new Exception('kann kein Verzeichnis aus dem extrahierten Verzeichnis "'.$path.'" erstellen: '.$e->getMessage());
    }
    
    return $dir;
  }

}
?>