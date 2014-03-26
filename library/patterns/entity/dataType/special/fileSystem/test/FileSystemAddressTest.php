<?php
namespace D\library\patterns\entity\dataType\special\fileSystem\test;

use D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class FileSystemAddressTest extends \PHPUnit_Framework_TestCase{
  /**
   * @covers D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress::isReestablish
   */
  public function testIsReestablish(){
    $this->assertFalse(FileSystemAddress::isReestablish(''));
    $this->assertTrue(FileSystemAddress::isReestablish('a'));
    $this->assertTrue(FileSystemAddress::isReestablish('a/b'));
    $this->assertTrue(FileSystemAddress::isReestablish('a/b/c/'));
    $this->assertTrue(FileSystemAddress::isReestablish('/a'));
    $this->assertFalse(FileSystemAddress::isReestablish('a//b'));
    $this->assertFalse(FileSystemAddress::isReestablish('a///b'));
    $this->assertFalse(FileSystemAddress::isReestablish('a//'));
    $this->assertFalse(FileSystemAddress::isReestablish('//a'));
    $this->assertFalse(FileSystemAddress::isReestablish('*'));
    $this->assertFalse(FileSystemAddress::isReestablish(':'));
    $this->assertFalse(FileSystemAddress::isReestablish('?'));
    $this->assertFalse(FileSystemAddress::isReestablish('"'));
    $this->assertFalse(FileSystemAddress::isReestablish('<'));
    $this->assertFalse(FileSystemAddress::isReestablish('>'));
    $this->assertFalse(FileSystemAddress::isReestablish('|'));
    $this->assertFalse(FileSystemAddress::isReestablish("\0"));
    $this->assertFalse(FileSystemAddress::isReestablish('\\'));
  }

  /**
   * @covers D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress::reestablish
   * @covers D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress::isRoot
   */
  public function testReestablish(){
    $o = FileSystemAddress::reestablish('a/b/c/');
    $this->assertEquals('a/b/c/', $o->getVal());
    $this->assertEquals(false, $o->isRoot());
    $o = FileSystemAddress::reestablish('/a/b/c');
    $this->assertEquals(true, $o->isRoot());
  }
}
