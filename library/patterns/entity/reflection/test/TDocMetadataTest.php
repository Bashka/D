<?php
namespace D\library\patterns\entity\reflection\test;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class TDocMetadataTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен возвращать массив метаданных из блока документации.
   * @covers D\library\patterns\structure\metadata\TDocMetadata.php::getAllMetadata
   */
  public function testShouldReturnMetadata(){
    $o = new DescribedMock;
    DescribedMock::$doc = '/**
   * Text
   * @tag value
   * $My\\Metadata value
   */';
    $this->assertEquals(['My\Metadata' => 'value'], $o->getAllMetadata());
  }

  /**
   * Должен возвращать пустую строку в качестве значения массива, если анотация не имеет значения.
   * @covers D\library\patterns\structure\metadata\TDocMetadata.php::getAllMetadata
   */
  public function testShouldReturnEmptyStringIfMetadataEmpty(){
    $o = new DescribedMock;
    DescribedMock::$doc = '/**
   * Text
   * @tag value
   * $My\\Metadata
   */';
    $this->assertEquals(['My\Metadata' => ''], $o->getAllMetadata());
  }

  /**
   * Должен возвращать пустой массив, если блок документации не содержит метаданных.
   * @covers D\library\patterns\structure\metadata\TDocMetadata.php::getAllMetadata
   */
  public function testShouldEmptyArrayIfMetadataNotExists(){
    $o = new DescribedMock;
    DescribedMock::$doc = '/**
   * Text
   * @tag value
   */';
    $this->assertEquals([], $o->getAllMetadata());
  }

  /**
   * Должен возвращать значение метаданных.
   * @covers D\library\patterns\structure\metadata\TDocMetadata.php::getMetadata
   */
  public function testShouldReturnMetadataValue(){
    $o = new DescribedMock;
    DescribedMock::$doc = '/**
   * $My\\Metadata value
   */';
    $this->assertEquals('value', $o->getMetadata('My\\Metadata'));
  }

  /**
   * Должен выбрасывать исключение, если запрашиваемые метаданные не установлены.
   * @covers D\library\patterns\structure\metadata\TDocMetadata.php::getMetadata
   */
  public function testShouldThrowExceptionIfMetadataNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $o = new DescribedMock;
    DescribedMock::$doc = '/**
   * Test
   */';
    $o->getMetadata('My\\Metadata');
  }

  /**
   * Должен возвращать true - если указанные метаданные установлены, иначе - false.
   * @covers D\library\patterns\structure\metadata\TDocMetadata.php::hasMetadata
   */
  public function testShouldReturnTrueIfMetadataExists(){
    $o = new DescribedMock;
    DescribedMock::$doc = '/**
   * $My\\Metadata value
   */';
    $this->assertTrue($o->hasMetadata('My\\Metadata'));
  }
}
