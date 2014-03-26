<?php
namespace PPHP\tests\tools\classes\standard\fileSystem\io;

use D\library\resources\fileSystem\io\FileReader;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class FileReaderTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var FileReader
   */
  protected $object;

  /**
   * @var resource Дескриптор файла для теста.
   */
  protected $descriptor;

  protected function setUp(){
    $this->descriptor = fopen('file', 'rb');
    $this->object = new FileReader($this->descriptor);
  }

  protected function tearDown(){
    fclose($this->descriptor);
  }

  /**
   * Должен возвращать текущий байт из потока.
   * @covers D\library\resources\fileSystem\io\FileReader::read
   */
  public function testShouldReturnByte(){
    $this->assertEquals('T', $this->object->read());
  }

  /**
   * Должен сдвигать указатель текущего байта на единицу.
   * @covers D\library\resources\fileSystem\io\FileReader::read
   */
  public function testShouldSetNextByte(){
    $this->object->read();
    $this->assertEquals('e', $this->object->read());
  }

  /**
   * Должен возвращать пустую строку, когда достигнут конец потока.
   * @covers D\library\resources\fileSystem\io\FileReader::read
   */
  public function testShouldReturnEmptyStringForEndStream(){
    fseek($this->descriptor, 39); // Установка указателя на последний символ.
    $this->assertEquals('', $this->object->read());
  }
}
