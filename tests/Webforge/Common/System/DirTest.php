<?php

namespace Webforge\Common\System;

use PHPUnit_Framework_TestCase;

class DirTest extends PHPUnit_Framework_TestCase {
  
  protected $dir;
  
  public function setUp() {
    $this->dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
  }
  
  public function testThatTheFactoryReturnsADir() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factory(__DIR__.DIRECTORY_SEPARATOR));
  }

  public function testThatTheTSFactoryReturnsADir_andWorksWithoutTrailingSlash() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factoryTS(__DIR__));
  }
  
  public function testSubDir() {
    $sub = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $parent = new Dir(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
    
    $this->assertTrue($sub->isSubdirectoryOf($parent));
  }
  
  /**
   * @depends testSubDir
   */
  public function testMakeRelativeTo() {
    $base = Dir::factoryTS(__DIR__);
    
    $graph = $base->sub('lib/Psc/Graph/');
    $lib = $base->sub('lib/');
    
    $rel = clone $graph;
    $this->assertEquals(
      '.'.DIRECTORY_SEPARATOR.'Psc'.DIRECTORY_SEPARATOR.'Graph'.DIRECTORY_SEPARATOR,
      (string) $rel->makeRelativeTo($lib),
      sprintf("making '%s' relative to '%s' failed", $graph, $lib)
    );
    
    $eq = clone $graph;
    
    $this->assertEquals('.'.DIRECTORY_SEPARATOR,(string) $eq->makeRelativeTo($graph));
  }
  
  public function testMakeRelativeToException() {
    $sub = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $parent = new Dir(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
    
    $norel = clone $parent;
    $this->setExpectedException('Webforge\Common\System\Exception');
    $norel->makeRelativeTo($sub);
  }
  
  public function testDirectoryIsNotSubdirectoryOfSelf() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $self = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    
    $this->assertFalse($dir->isSubdirectoryOf($self));
  }
  
  public function testDirGetFile() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $file = __FILE__;
    $fname = basename($file);
    
    //$this->assertEquals('D:\www\psc-cms\Umsetzung\base\src\psc\readme.txt',
                        //(string) $dir->getFile('readme.txt'));

    $this->assertEquals($file, (string) $dir->getFile($fname));
    $this->assertEquals($file, (string) $dir->getFile(new File($fname)));
    $this->assertEquals($file, (string) $dir->getFile(new File(new Dir('.'.DIRECTORY_SEPARATOR),$fname)));

  /*
    das ist unexpected! ich will aber keinen test auf sowas machen..
    $this->assertEquals('D:\www\psc-cms\Umsetzung\base\readme.txt',
                        (string)  $dir->getFile('..\\..\\readme.txt'));
  */

    if (DIRECTORY_SEPARATOR === '\\') {
      $this->assertEquals(__DIR__.'\lib\docu\readme.txt',
                          (string) $dir->getFile(new File('.\lib\docu\readme.txt')));
      $this->assertEquals(__DIR__.'\lib\docu\readme.txt',
                          (string) $dir->getFile(new File(new Dir('.\lib\docu\\'),'readme.txt')));
                          
    } else {
      $this->assertEquals(__DIR__.'/lib/docu/readme.txt',
                          (string) $dir->getFile(new File('./lib/docu/readme.txt')));
      $this->assertEquals(__DIR__.'/lib/docu/readme.txt',
                          (string) $dir->getFile(new File(new Dir('./lib/docu/'),'readme.txt')));
      
    }
    

    $absoluteDir = __DIR__.DIRECTORY_SEPARATOR;
    $this->setExpectedException('InvalidArgumentException');
    $dir->getFile(new File(new Dir($absoluteDir),'readme.txt'));
  }
  
  public function testDirgetFiles() {
    $dir = Dir::factoryTS(__DIR__);
    
    if ($dir->exists()) {
      $files = $dir->getFiles('php');
      $this->assertNotEmpty($files);
      
      foreach ($files as $file) {
        if (isset($dirone)) {
          $this->assertFalse($dirone === $file->getDirectory(),'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
          $this->assertFalse($dirone === $dir,'Die Verzeichnisse der dateien von getFiles() müssen kopien des ursprünglichen objektes sein. keine Referenzen');
        }
        
        $dirone = $file->getDirectory();
        $this->assertFalse($file->getDirectory()->isRelative());
        
        $file->makeRelativeTo($dir);
        
        $this->assertTrue($file->isRelative());
      }
    } else {
      $this->markTestSkipped('ui dev für test nicht da');
    }
  }
  
  public function testIsRelative() {
    $base = Dir::factoryTS(__DIR__);
    $graph = $base->sub('psc/class/Graph//');
    $psc = $base->sub('psc/');
    
    $graph->makeRelativeTo($psc);
    
    $this->assertTrue($graph->isRelative());
  }
  
  
  public function testWrappedPaths() {
    if (DIRECTORY_SEPARATOR === '\\') {
      $abs = 'D:/path/does/not';
    } else {
      $abs = '/path/does/not';
    }
    $wrappedPath = 'phar://'.$abs.'/matter/my.phar.gz/i/am/wrapped/';
    
    $dir = new Dir($wrappedPath);
    $this->assertEquals($wrappedPath, (string) $dir);
    
    $this->assertTrue($dir->isWrapped());
    $this->assertEquals('phar', $dir->getWrapper());
    
    $dir->setWrapper('rar');
    $this->assertEquals('rar', $dir->getWrapper());
  }
  
  public function testWrappedExtract() {
    if (DIRECTORY_SEPARATOR === '\\') {
      $abs = 'D:/path/does/not';
    } else {
      $abs = '/path/does/not';
    }
    $fileString = 'phar://'.$abs.'/matter/my.phar.gz/i/am/wrapped/class.php';
    
    $dir = Dir::extract($fileString);
    $this->assertEquals('phar://'.$abs.'/matter/my.phar.gz/i/am/wrapped/', (string) $dir);
  }
  
  public function testIsEmpty() {
    $nonEx = Dir::factoryTS(__DIR__)->sub('blablabla/non/existent/');
    $this->assertTrue($nonEx->isEmpty());
    
    $temp = Dir::createTemporary();
    $this->assertTrue($temp->isEmpty());
    
    $f = $temp->getFile('blubb.txt');
    $f->writeContents('wurst');
    $this->assertFileExists((string) $f);
    $this->assertFalse($temp->isEmpty());
  }
  
  public function testGetMACTime_Acceptance() {
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getModifiedTime());
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getCreateTime());
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getAccessTime());
  }

  public function testCygiwnPathsAreTreatedCorrectly() {
    $path = '/cygdrive/D/www/psc-cms-js/git/';

    $this->assertTrue(Dir::isCygwinPath($path));

    $this->assertEquals(
      $path,
      (string) new Dir($path)
    );
  }
}
