<?php
namespace D\library\resources\fileSystem\components\test;

use D\library\resources\fileSystem\io\FileReader;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class FileSeekIOTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен устанавливать позицию текущего байта.
   * @covers D\library\resources\fileSystem\io\FileSeekIO::setPosition
   */
  public function testShouldSetCurrentByte(){
    $d = fopen('file', 'rb');
    $o = new FileReader($d);
    $o->setPosition(5);
    $this->assertEquals(5, ftell($d));
  }

  /**
   * Возвращает позицию текущего байта.
   * @covers D\library\resources\fileSystem\io\FileSeekIO::getPosition
   */
  public function testShouldGetCurrentByte(){
    $d = fopen('file', 'rb');
    $o = new FileReader($d);
    fseek($d, 5);
    $this->assertEquals(5, $o->getPosition());
  }
}
