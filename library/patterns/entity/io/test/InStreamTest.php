<?php
namespace D\library\patterns\entity\io\test;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class InStreamTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var InStreamMock
   */
  protected $object;

  protected function setUp(){
    $this->object = new InStreamMock(null);
  }

  /**
   * Должен возвращать текущий байт из потока.
   * @covers D\library\patterns\entity\io\test\InStreamMock::read
   */
  public function testShouldReturnByte(){
    $this->assertEquals('F', $this->object->read());
    $this->object->setPoint(InStreamMock::LENGTH - 1);
    $this->assertEquals('g', $this->object->read());
  }

  /**
   * Должен возвращать пустую строку когда поток закончен.
   * @covers D\library\patterns\entity\io\test\InStreamMock::read
   */
  public function testShouldReturnEmptyStringForEndStream(){
    $this->object->setPoint(InStreamMock::LENGTH);
    $this->assertEquals('', $this->object->read());
  }

  /**
   * Должен считывать указанное число байт.
   * @covers D\library\patterns\entity\io\InStream::readString
   */
  public function testShouldReadString(){
    $this->assertEquals('First', $this->object->readString(5));
  }

  /**
   * Должен продолжать чтение от текущей позиции.
   * @covers D\library\patterns\entity\io\InStream::readString
   */
  public function testShouldContinueRead(){
    $this->assertEquals('First', $this->object->readString(5));
    $this->assertEquals(" string\r\nВ", $this->object->readString(11));
  }

  /**
   * Должен считывать оставшиеся байты, если в потоке не хватает строки запрошенной длины.
   * @covers D\library\patterns\entity\io\InStream::readString
   */
  public function testShouldReadTailForSmallStream(){
    $this->assertEquals("First string\r\nВторая строка\r\nLast string", $this->object->readString(InStreamMock::LENGTH + 5));
  }

  /**
   * Должен возвращать пустую строку, если производится попытка читать законченный поток.
   * @covers D\library\patterns\entity\io\InStream::readString
   */
  public function testShouldReturnEmptyStringIfStreamFinished(){
    $this->object->setPoint(InStreamMock::LENGTH);
    $this->assertEquals('', $this->object->readString(5));
  }

  /** Должен считывать строку до первого вхождения символа перевода строки (разделителя).
   * @covers D\library\patterns\entity\io\InStream::readLine
   */
  public function testShouldReadLine(){
    $this->assertEquals('First string ', $this->object->readLine());
    $this->assertEquals('Вторая строка' . "\r", $this->object->readLine("\n"));
  }

  /** При достижении конца потока, должен возвращать считанную строку.
   * @covers D\library\patterns\entity\io\InStream::readLine
   */
  public function testShouldReturnTailForSmallStream(){
    $this->object->setPoint(InStreamMock::LENGTH - 6);
    $this->assertEquals('string', $this->object->readLine());
  }

  /** Должен возвращать пустую строку, если производится попытка читать законченный поток.
   * @covers D\library\patterns\entity\io\InStream::readLine
   */
  public function testShouldReturnEmptyLineIfStreamFinished(){
    $this->object->setPoint(InStreamMock::LENGTH);
    $this->assertEquals('', $this->object->readLine());
  }

  /** Должен возвращать пустую строку, если текущим символом в потоке является разделитель строк.
   * @covers D\library\patterns\entity\io\InStream::readLine
   */
  public function testShouldReturnEmptyLineIfCurrentSymbolEOL(){
    $this->object->setPoint(13);
    $this->assertEquals('', $this->object->readLine("\r\n"));
  }

  /** Должен возвращать оставшееся содержимое потока.
   * @covers D\library\patterns\entity\io\InStream::readAll
   */
  public function testShouldReturnTailStream(){
    $this->object->setPoint(5);
    $this->assertEquals(" string\r\nВторая строка\r\nLast string", $this->object->readAll());
  }

  /** Должен возвращать пустую строку, если производится попытка читать законченный поток.
   * @covers D\library\patterns\entity\io\InStream::readAll
   */
  public function testShouldReturnEmptyIfStreamFinished(){
    $this->object->setPoint(InStreamMock::LENGTH);
    $this->assertEquals('', $this->object->readAll());
  }
}
