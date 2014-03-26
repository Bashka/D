<?php
namespace D\library\resources\fileSystem\components\special\csv\test;

use D\library\resources\fileSystem\components\special\csv\DSV;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class DSVTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен последовательно загружать записи из файла.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::load
   */
  public function testShouldLoadRows(){
    $object = new DSV('data.csv');
    $object->load();
    $this->assertEquals(5, $object->getIndex());
    $this->assertEquals(['Иванов', 'Виктор', 'Алексеевич', '1981-02-13'], $object->getRow(0));
    $this->assertEquals(5, $object->count());
  }

  /**
   * Должен возвращать число загруженых записей.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::count
   */
  public function testShouldReturnCountRows(){
    $object = new DSV('data.csv');
    $object->load();
    $this->assertEquals(5, $object->count());
  }

  /**
   * Должен возвращать текущую обрабатываемую строку.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::getCurrentRow
   */
  public function testShouldReturnCurrentRow(){
    $object = new DSV('data.csv');
    $object->load();
    $this->assertEquals(['Сидоров', 'Максим', 'Витальевич', '2002-08-23'], $object->getCurrentRow());
  }

  /**
   * Должен возвращать индекс текущей записи.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::getIndex
   */
  public function testShouldReturnCurrentIndex(){
    $object = new DSV('data.csv');
    $object->load();
    $this->assertEquals(5, $object->getIndex());
  }

  /**
   * Должен возвращать указанную запись.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::getRow
   */
  public function testShouldReturnRow(){
    $object = new DSV('data.csv');
    $object->load();
    $this->assertEquals(['Сидоров', 'Максим', 'Витальевич', '2002-08-23'], $object->getRow(4));
  }

  /**
   * Должен добавлять запись.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::setRow
   */
  public function testShouldAddRow(){
    $object = new DSV('data.csv');
    $object->load();
    $object->setRow(5, ['Тимофеев', 'Игорь', 'Вячеславович', '1971-01-20']);
    $this->assertEquals(['Тимофеев', 'Игорь', 'Вячеславович', '1971-01-20'], $object->getRow(5));
  }

  /**
   * Должен формировать файл в формате DSV.
   * @covers D\library\resources\fileSystem\components\special\csv\DSV::interpretation
   */
  public function testShouldReturnDSVData(){
    $object = new DSV('data.csv');
    $object->load();
    $this->assertEquals('Иванов;Виктор;Алексеевич;1981-02-13'."\r\n".'Глебов;Антон;Борисович;1992-07-01'."\r\n".'Иванов;Анатолий;Михайлович;1986-04-23'."\r\n".'Дмитриев;Михаил;Михайлович;1972-11-09'."\r\n".'Сидоров;Максим;Витальевич;2002-08-23', $object->interpretation());
  }
}
 
