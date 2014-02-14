<?php
namespace PPHP\tests\tools\classes\standard\fileSystem\io;

use D\library\resources\fileSystem\io\FileWriter;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class FileWriterTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var FileWriter
   */
  protected $object;

  /**
   * @var resource Дескриптор файла для теста.
   */
  protected $descriptor;

  protected function setUp(){
    $this->descriptor = fopen('file', 'r+b');
    $this->object = new FileWriter($this->descriptor);
  }

  protected function tearDown(){
    fclose($this->descriptor);
    $d = fopen('file', 'w');
    fwrite($d, 'Test data' . "\n" . 'Тестовые данные');
    fclose($d);
  }

  /**
   * Должен записывать указанный пакет байт в поток.
   * @covers D\library\resources\fileSystem\io\FileWriter::write
   */
  public function testShouldWritePackageByte(){
    $this->object->write('Hello');
    $this->assertEquals('Hellodata' . "\n" . 'Тестовые данные', file_get_contents('file'));
  }

  /**
   * Должен возвращать число записанных байт.
   * @covers D\library\resources\fileSystem\io\FileWriter::write
   */
  public function testShouldReturnRecordedByte(){
    $this->assertEquals(5, $this->object->write('Hello'));
  }

  /**
   * В качестве пакета байт может выступать только тип string.
   * @covers D\library\resources\fileSystem\io\FileWriter::write
   */
  public function testPackageByteShouldBeString(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $this->object->write(1);
  }

  /**
   * Должен удалять все содержимое потока.
   * @covers D\library\resources\fileSystem\io\FileWriter::clear
   */
  public function testShouldClearStream(){
    $this->assertTrue($this->object->clean());
    $this->assertEquals('', file_get_contents('file'));
  }
}
