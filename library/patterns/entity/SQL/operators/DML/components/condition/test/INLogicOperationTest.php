<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\Select;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class INLogicOperationTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять сравниваемое поле.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::__construct
   */
  public function testShouldSetField(){
    $f = new Field('test');
    $i = new INLogicOperation($f);
    $this->assertEquals($f, $i->getField());
  }

  /**
   * Должен добавлять значение в контрольный список.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::addValue
   */
  public function testShouldAddValueInList(){
    $i = new INLogicOperation(new Field('test'));
    $i->addValue(5);
    $this->assertEquals(5, $i->getValues()[0]);
  }

  /**
   * В качестве значения могут выступать следующие типы: integer, float, boolean, string, null.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::addValue
   */
  public function testValueCanBeIntegerFloatBooleanString(){
    $i = new INLogicOperation(new Field('test'));
    $i->addValue(5);
    $i->addValue('a');
    $i->addValue(true);
    $i->addValue(1.1);
    $i->addValue(null);
    $this->assertEquals(5, $i->getValues()[0]);
  }

  /**
   * Должен выбрасывать исключение если передан неверный тип.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::addValue
   */
  public function testShouldThrowExceptionIfBadValue(){
    $i = new INLogicOperation(new Field('test'));
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $i->addValue([1, 2, 3]);
  }

  /**
   * Должен определять инструкцию Select в качестве источника допустимых значений.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::setSelectQuery
   */
  public function testShouldSetSelect(){
    $i = new INLogicOperation(new Field('test'));
    $s = new Select();
    $i->setSelectQuery($s);
    $this->assertEquals($s, $i->getSelectQuery());
  }

  /**
   * Должен возвращать строку вида: имяПоля IN ((значение[, значение]*)|(selectИнструкция)).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::interpretation
   */
  public function testShouldInterpretation(){
    $i = new INLogicOperation(new Field('test'));
    $i->addValue(5);
    $i->addValue('a');
    $i->addValue();
    $i->addValue(true);
    $i->addValue(1.1);
    $i->addValue();
    $this->assertEquals('(test IN ("5","a",?,"true","1.1",?))', $i->interpretation());
    $i = new INLogicOperation(new Field('test'));
    $s = new Select();
    $s->addAllField();
    $s->addTable(new Table('table'));
    $i->setSelectQuery($s);
    $this->assertEquals('(test IN (SELECT * FROM table))', $i->interpretation());
  }

  /**
   * Должен выбрасывать исключение в случае отсутствия хотя бы одного допустимого значения.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::interpretation
   */
  public function testShouldThrowExceptionIfNotValues(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $i = new INLogicOperation(new Field('test'));
    $i->interpretation();
  }

  /**
   * Инструкция Select имеет больший приоритет.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::interpretation
   */
  public function testShouldResetValues(){
    $i = new INLogicOperation(new Field('test'));
    $i->addValue(5);
    $s = new Select;
    $s->addTable(new Table('test'));
    $s->addAllField();
    $i->setSelectQuery($s);
    $this->assertEquals('(test IN (SELECT * FROM test))', $i->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: имяПоля IN ((значение[, значение]*)).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::reestablish
   */
  public function testShouldRestorableForString(){
    $o = INLogicOperation::reestablish('(table.field IN ("a", "b", ?, "1"))');
    $this->assertEquals('field', $o->getField()->getName());
    $this->assertEquals('a', $o->getValues()[0]);
    $this->assertEquals(null, $o->getValues()[2]);
  }

  /**
   * Допустимой строкой является строка вида: имяПоля IN ((значение[, значение]*)).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(INLogicOperation::isReestablish('(field IN ("aa"))'));
    $this->assertTrue(INLogicOperation::isReestablish('(field IN ("a",?,"b",?))'));
    $this->assertTrue(INLogicOperation::isReestablish('(table.field IN ("a","b", "1"))'));
    $this->assertTrue(INLogicOperation::isReestablish('(table.field IN
                                                              ("a",
                                                              "b",
                                                              "1"))'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(INLogicOperation::isReestablish('field IN ("a")'));
    $this->assertFalse(INLogicOperation::isReestablish('(field IN ("a",??))'));
    $this->assertFalse(INLogicOperation::isReestablish('(field ("a"))'));
    $this->assertFalse(INLogicOperation::isReestablish('(field IN "a")'));
    $this->assertFalse(INLogicOperation::isReestablish('(field IN ("a" "b"))'));
  }
}
