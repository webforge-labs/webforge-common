<?php

namespace Webforge\Common\System;

use PHPUnit_Framework_TestCase;
use SplFileInfo;

/**
 * @covers Webforge\Common\System\Dir
 */
class DirTest extends PHPUnit_Framework_TestCase {
  
  protected $dir;
  protected $absolutePath, $relativePath;
  
  public function setUp() {
    $this->dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);

    if (DIRECTORY_SEPARATOR === '\\') {
      $absolutePath = 'D:\\path\\for\\absolute\\';
    } else {
      $this->absolutePath = '/path/for/absolute/';
    }

    $this->relativePath = 'path'.DIRECTORY_SEPARATOR.'for'.DIRECTORY_SEPARATOR.'relative'.DIRECTORY_SEPARATOR;
  }
  
  public function testThatTheFactoryReturnsADir() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factory(__DIR__.DIRECTORY_SEPARATOR));
  }

  public function testThatTheTSFactoryReturnsADir_andWorksWithoutTrailingSlash() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factoryTS(__DIR__));
  }

  public function testFactoryTSCanHaveAnEmptyPath() {
    $this->assertInstanceOf('Webforge\Common\System\Dir', Dir::factoryTS());
  }

  /**
   * @dataProvider providePathsWithoutTrailingSlash
   */
  public function testFactoryDoesNotLikeDirectoriesWithoutSlash($erroneous) {
    $this->setExpectedException('InvalidArgumentException');

    new Dir($erroneous);
  }

  public static function providePathsWithoutTrailingSlash() {
    return Array(
      array('/var/local/missing/trail'),
      array('D:\www\missing\trail')
    );
  }

  public function testConstructWithDirAsParamWillCloneDirectory() {
    $dir = new Dir($this->dir);

    $this->assertEquals((string) $this->dir, (string) $dir);
    $this->assertNotSame($this->dir, $dir);
  }
  
  /**
   * @dataProvider provideDifferentPaths
   */
  public function testgetOSPathReturnsPathForGivenOS($path, $expectedPath, $os) {
    $dir = new Dir($path);

    $this->assertEquals(
      $expectedPath,
      $dir->getOSPath($os)
    );
  }

  public static function provideDifferentPaths() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('vfs:///project/src/', 'vfs:///project/src/', Dir::WINDOWS);
    $test('vfs:///project/src/', 'vfs:///project/src/', Dir::UNIX);

    $test('phar:///root/path/x.phar/src/', 'phar:///root/path/x.phar/src/', Dir::WINDOWS);
    $test('phar:///root/path/x.phar/src/', 'phar:///root/path/x.phar/src/', Dir::UNIX);

    $test('/var/local/www/', '/var/local/www/', Dir::UNIX);
    
    $test('D:\www\webforge\\', 'D:\www\webforge\\', Dir::WINDOWS);
    $test('D:\www\webforge\\', '/D:/www/webforge/', Dir::UNIX);
    $test('C:\\', 'C:\\', Dir::WINDOWS);
    $test('C:\\', '/C:/', Dir::UNIX);

    $test('.\its\relative\\','.\its\relative\\', Dir::WINDOWS);
    $test('.\its\relative\\','./its/relative/', Dir::UNIX); 

    $test('./its/relative/', './its/relative/', Dir::UNIX);
    $test('./its/relative/', '.\its\relative\\', Dir::WINDOWS);
    
    $test('its/relative/', 'its/relative/', Dir::UNIX);
    $test('its/relative/', 'its\relative\\', Dir::WINDOWS);

    $test('its\relative\\', 'its\relative\\', Dir::WINDOWS);
    $test('its\relative\\', 'its/relative/', Dir::UNIX);

    $test('/cygdrive/c/', '/cygdrive/c/', Dir::UNIX);
    $test('/cygdrive/c/', '/cygdrive/c/', Dir::WINDOWS);

    $test('/cygdrive/c/with/longer/path/', '/cygdrive/c/with/longer/path/', Dir::UNIX);
    $test('/cygdrive/c/with/longer/path/', '/cygdrive/c/with/longer/path/', Dir::WINDOWS);

    $test('/cygdrive/c/with/bad\\path/', '/cygdrive/c/with/bad/path/', Dir::UNIX);
    $test('/cygdrive/c/with/bad\\path/', '/cygdrive/c/with/bad/path/', Dir::WINDOWS);

    $test('/cygdrive/c/with/okay\\ path/', '/cygdrive/c/with/okay\\ path/', Dir::UNIX);
    $test('/cygdrive/c/with/okay\\ path/', '/cygdrive/c/with/okay\\ path/', Dir::WINDOWS);
    
    $test('\\\\psc-host\shared\www\webforge\\', '\\\\psc-host\shared\www\webforge\\', Dir::WINDOWS);
    $test('\\\\psc-host\\', '\\\\psc-host\\', Dir::WINDOWS);

    // edge cases with exception?
    //$test('/var/local/www/', 'var\local\www\\', Dir::WINDOWS);
    //$test('\\\\psc-host\shared\www\webforge\\', '???', Dir::UNIX);
  
    return $tests;
  }

  /**
   * @dataProvider provideAbsoluteOrRelative
   */
  public function testAbsoluteOrRelative($path, $isAbsolute) {
    $dir = new Dir($path);
    if ($isAbsolute) {
      $this->assertTrue($dir->isAbsolute(), $path.' ->isAbsolute');
      $this->assertFalse($dir->isRelative(), $path.' ->isNotRelative');
    } else {
      $this->assertFalse($dir->isAbsolute(), $path.' ->isNotAbsolute');
      $this->assertTrue($dir->isRelative(), $path.' ->isRelative');
    }
  }
  

  /**
   * @dataProvider provideAbsoluteOrRelative
   */
  public function testIsAbsolutePath($path, $isAbsolute) {
    $this->assertEquals($isAbsolute, Dir::isAbsolutePath($path), '::isAbsolutePath('.$path.')');
  }

  public static function provideAbsoluteOrRelative() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $absolute = TRUE;
    $relative = FALSE;
    $test('vfs:///project/src/', $absolute);
    $test('phar:///root/path/x.phar/src/', $absolute);
    $test('/var/local/www/', $absolute);
    $test('D:\www\webforge\\', $absolute);
    $test('C:\\', $absolute);
    $test('\\\\host\path\to\location\\', $absolute);

    $test('.\its\relative\\', $relative);
    $test('./its/relative/', $relative);
    $test('../../its/relative/', $relative);
    $test('its/relative/', $relative);
    $test('its\relative\\', $relative);
  
    return $tests;
  }

  public function testUnixRegressionForAbsolutePath_factoryTSDoesLtrimInsteadofRtrim() {
    $dir = Dir::factoryTS('/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/');

    $this->assertEquals('/var/local/www/tiptoi.pegasus.ps-webforge.net/base/src/', (string) $dir);
  }

  public function testisSubDirectoryOf() {
    $sub = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $parent = new Dir(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
    
    $this->assertTrue($sub->isSubdirectoryOf($parent));
  }

  public function testDirectoryIsNotSubdirectoryOfSelf() {
    $dir = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    $self = new Dir(__DIR__.DIRECTORY_SEPARATOR);
    
    $this->assertFalse($dir->isSubdirectoryOf($self));
  }
  
  public function testGetMACTime_Acceptance() {
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getModifiedTime());
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getCreateTime());
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', $this->dir->getAccessTime());
  }

  public function testCygwinPathsAreTreatedCorrectly() {
    $path = '/cygdrive/D/www/psc-cms-js/git/';

    $this->assertTrue(Dir::isCygwinPath($path));

    $this->assertEquals(
      $path,
      (string) new Dir($path)
    );
  }

  /**
   * @dataProvider provideFixToUnixPath
   */
  public function testFixToUnixPath($actualPath, $expectedPath) {
    $this->assertEquals($expectedPath, Dir::fixToUnixPath($actualPath));
  }
  
  public static function provideFixToUnixPath() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('/var/local\www/', '/var/local/www/');
    $test('\var/local\www/', '/var/local/www/');
    $test('/var/local/www\\', '/var/local/www/');

    $test('/var/with\\ space/', '/var/with\\ space/');
    $test('/var/with\\\\ backslash/', '/var/with\\\\ backslash/');
  
    return $tests;
  }


  /**
   * @dataProvider provideCreateFromURL
   */
  public function testCreateFromURL($url, $expectedPath, $root = NULL) {
    $root = $root ?: new Dir('D:\www\\');

    $this->assertEquals(
      $expectedPath,
      (string) Dir::createFromURL($url, $root)->resolvePath()->getOSPath(Dir::WINDOWS)
    );
  }
  
  public static function provideCreateFromURL() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };

    $root = 'D:\www\\';
  
    $test('something/relative', $root.'something\relative\\');
    $test('something/relative/which/./resolves', $root.'something\relative\\which\\resolves\\');
    $test('something/relative/which/../resolved', $root.'something\relative\resolved\\');

    $test('/', $root);
    $test('./', $root);

    return $tests;
  }

  public function testCreateFromURLUsesCWDAsDefault() {
    $this->assertEquals(
      getcwd().DIRECTORY_SEPARATOR.'in'.DIRECTORY_SEPARATOR.'cwd'.DIRECTORY_SEPARATOR,
      (string) Dir::createFromURL('in/cwd/')
    );
  }
}
 