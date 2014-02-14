<?php
namespace D\library\resources\fileSystem\components\test;

use D\library\resources\fileSystem\io\FileReader;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class FileClosedTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен закрывать открытый дескриптор файла.
   * @covers D\library\resources\fileSystem\io\FileClose::close
   */
  public function testShouldCloseDescriptor(){
    $d = fopen('file', 'rb');
    $o = new FileReader($d);
    $o->close();
    $this->assertFalse(is_resource($d));
  }

  /**
   * Должен возвращать true - если дескриптор файла закрыт, иначе - false.
   * @covers D\library\resources\fileSystem\io\FileClose::isClose
   */
  public function testShouldReturnTrueIfDescriptorClose(){
    $d = fopen('file', 'rb');
    $o = new FileReader($d);
    $this->assertFalse($o->isClose());
    $o->close();
    $this->assertTrue($o->isClose());
  }
}
