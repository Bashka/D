<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class FieldTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен идентифицировать объект именем поля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::__construct
   */
  public function testShouldInitObjectFieldName(){
    $o = new Field('name');
    $this->assertEquals('name', $o->getName());
  }

  /**
   * Именем поля может быть только строка.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::__construct
   */
  public function testFieldNameStringIs(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Field(1);
  }

  /**
   * Должен возвращать имя поля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::getName
   */
  public function testShouldReturnFieldName(){
    $o = new Field('name');
    $this->assertEquals('name', $o->getName());
  }

  /**
   * Должен устанавливать целевую таблицу.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::setTable
   */
  public function testShouldSetTable(){
    $o = new Field('name');
    $o->setTable(new Table('people'));
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Table', $o->getTable());
    $this->assertEquals('people', $o->getTable()->getTableName());
  }

  /**
   * Должен заменять целевую таблицу при повторном вызове.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::setTable
   */
  public function testShouldResetTable(){
    $o = new Field('name');
    $o->setTable(new Table('people'));
    $o->setTable(new Table('student'));
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Table', $o->getTable());
    $this->assertEquals('student', $o->getTable()->getTableName());
  }

  /**
   * Должен возвращать целевую таблицу, если она установлена.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::getTable
   */
  public function testShouldReturnTableIfTableSet(){
    $o = new Field('name');
    $o->setTable(new Table('people'));
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Table', $o->getTable());
  }

  /**
   * Должен возвращать null, если целевая таблица не установлена.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::getTable
   */
  public function testShouldReturnNullIfTableSet(){
    $o = new Field('name');
    $this->assertEquals(null, $o->getTable());
  }

  /**
   * Если целевая таблица установлена, должен возвращать строку вида: имяТаблицы.имяПоля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::interpretation
   */
  public function testShouldInterpretationTableAndFieldIfTableSet(){
    $o = new Field('name');
    $o->setTable(new Table('people'));
    $this->assertEquals('people.name', $o->interpretation());
  }

  /**
   * Если целевая таблица не установлена, должен возвращать строку вида: имяПоля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::interpretation
   */
  public function testShouldInterpretationFieldIfTableEmpty(){
    $o = new Field('name');
    $this->assertEquals('name', $o->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: имяПоля - и вида: имяТаблицы.имяПоля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::reestablish
   */
  public function testShouldRestorableForString(){
    $f = Field::reestablish('test');
    $this->assertEquals('test', $f->getName());
    $f = Field::reestablish('table.field');
    $this->assertEquals('field', $f->getName());
    $this->assertEquals('table', $f->getTable()->getTableName());
  }

  /**
   * Допустимой строкой является строка вида: имяПоля - и вида: имяТаблицы.имяПоля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Field::isReestablish('field'));
    $this->assertTrue(Field::isReestablish('table.field'));
    $this->assertTrue(Field::isReestablish('field5_'));
    $this->assertTrue(Field::isReestablish('_field'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Field::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Field::isReestablish('1field'));
    $this->assertFalse(Field::isReestablish('field+'));
    $this->assertFalse(Field::isReestablish('tableA.tableB.field'));
    $this->assertFalse(Field::isReestablish('1table.field'));
  }
}
