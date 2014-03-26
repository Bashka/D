<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\Field;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class LogicOperationTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять условие.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::__construct
   */
  public function testShouldSetCondition(){
    $o = new LogicOperation(new Field('test'), '=', 1);
    $this->assertEquals('test', $o->getField()->getName());
    $this->assertEquals('=', $o->getOperator());
    $this->assertEquals(1, $o->getValue());
  }

  /**
   * В качестве оператора допустимо одно из следующих значений: =, !=, >=, <=, >, <.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::__construct
   */
  public function testOperatorMustBeMathOperator(){
    new LogicOperation(new Field('test'), '=', 1);
    new LogicOperation(new Field('test'), '!=', 1);
    new LogicOperation(new Field('test'), '>=', 1);
    new LogicOperation(new Field('test'), '<=', 1);
    new LogicOperation(new Field('test'), '>', 1);
    new LogicOperation(new Field('test'), '<', 1);
  }

  /**
   * Должен выбрасывать исключение при передаче в качестве оператора не допустимого значения.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::__construct
   */
  public function testShouldThrowExceptionIfBadOperator(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new LogicOperation(new Field('test'), '*', 1);
  }

  /**
   * В качестве значения могут выступать следующие типы: integer, float, boolean, string, Field, null.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::__construct
   */
  public function testValueMustBeIntegerFloatBooleanStringField(){
    new LogicOperation(new Field('fieldA'), '=', 'test');
    new LogicOperation(new Field('fieldA'), '=', 1);
    new LogicOperation(new Field('fieldA'), '=', 1.1);
    new LogicOperation(new Field('fieldA'), '=', true);
    new LogicOperation(new Field('fieldA'), '=', new Field('fieldB'));
    new LogicOperation(new Field('test'), '=');
  }

  /**
   * Должен выбрасывать исключение если в качестве значения передан неверный тип.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::__construct
   */
  public function testShouldThrowExceptionIfBadValue(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new LogicOperation(new Field('test'), '=', new \stdClass());
  }

  /**
   * Должен возвращать строку вида: (имяПоля оператор значение|имяПоля|?).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::interpretation
   */
  public function testShouldInterpretation(){
    $o = new LogicOperation(new Field('test'), '=', 1);
    $this->assertEquals('(test = "1")', $o->interpretation());
    $o = new LogicOperation(new Field('fieldA'), '=', new Field('fieldB'));
    $this->assertEquals('(fieldA = fieldB)', $o->interpretation());
    $o = new LogicOperation(new Field('test'), '=');
    $this->assertEquals('(test = ?)', $o->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: (имяПоля оператор значение|имяПоля).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = LogicOperation::reestablish('(field = "1")');
    $this->assertEquals('field', $l->getField()->getName());
    $this->assertEquals('=', $l->getOperator());
    $this->assertEquals('1', $l->getValue());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = LogicOperation::reestablish('(field = "Hello world")');
    $this->assertEquals('field', $l->getField()->getName());
    $this->assertEquals('=', $l->getOperator());
    $this->assertEquals('Hello world', $l->getValue());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = LogicOperation::reestablish('(field = ?)');
    $this->assertEquals('field', $l->getField()->getName());
    $this->assertEquals('=', $l->getOperator());
    $this->assertEquals(null, $l->getValue());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = LogicOperation::reestablish('(fieldA = fieldB)');
    $this->assertEquals('fieldA', $l->getField()->getName());
    $this->assertEquals('=', $l->getOperator());
    $this->assertEquals('fieldB', $l->getValue()->getName());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = LogicOperation::reestablish('(tableA.fieldA = tableB.fieldB)');
    $this->assertEquals('fieldA', $l->getField()->getName());
    $this->assertEquals('=', $l->getOperator());
    $this->assertEquals('tableB', $l->getValue()->getTable()->getTableName());
  }

  /**
   * Допустимой строкой является строка вида: имяПоля оператор значение|имяПоля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(LogicOperation::isReestablish('(test = "1")'));
    $this->assertTrue(LogicOperation::isReestablish('(test = test)'));
    $this->assertTrue(LogicOperation::isReestablish('(test = table.field)'));
    $this->assertTrue(LogicOperation::isReestablish('(table.field = "1")'));
    $this->assertTrue(LogicOperation::isReestablish('(table.field = test)'));
    $this->assertTrue(LogicOperation::isReestablish('(table.field = table.field)'));
    $this->assertTrue(LogicOperation::isReestablish('(test = "")'));
    $this->assertTrue(LogicOperation::isReestablish('(test = ?)'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(LogicOperation::isReestablish('test = "1"'));
    $this->assertFalse(LogicOperation::isReestablish('(= "1")'));
    $this->assertFalse(LogicOperation::isReestablish('(test "1")'));
    $this->assertFalse(LogicOperation::isReestablish('(test = ??)'));
  }
}
