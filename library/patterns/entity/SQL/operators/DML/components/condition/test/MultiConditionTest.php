<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition;
use D\library\patterns\entity\SQL\operators\DML\components\Field;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class MultiConditionTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять все компоненты логического выражения.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition::__construct
   */
  public function testShouldSetAllConditionAndOperator(){
    $loName = new LogicOperation(new Field('name'), '=', 'ivan');
    $loOID = new LogicOperation(new Field('OID'), '<', '10');
    $o = new MultiCondition($loName, 'AND', $loOID);
    $this->assertEquals($loName, $o->getLeftOperand());
    $this->assertEquals($loOID, $o->getRightOperand());
    $this->assertEquals('AND', $o->getLogicOperator());
  }

  /**
   * В качестве оператора может выступать только одно из следующих значений: AND или OR.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition::__construct
   */
  public function testOperatorShouldBeANDorOR(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new MultiCondition(new LogicOperation(new Field('name'), '=', 'ivan'), 'TEST', new LogicOperation(new Field('OID'), '<', '10'));
  }

  /**
   * Должен возвращать строку вида: ((условие) оператор (условие)).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition::interpretation
   */
  public function testShouldInterpretation(){
    $loName = new LogicOperation(new Field('name'), '=', 'ivan');
    $loOID = new LogicOperation(new Field('OID'), '<', '10');
    $o = new MultiCondition($loName, 'AND', $loOID);
    $this->assertEquals('((name = "ivan") AND (OID < "10"))', $o->interpretation());
    $o = new MultiCondition($o, 'OR', new LogicOperation(new Field('c'), '=', 'c'));
    $this->assertEquals('(((name = "ivan") AND (OID < "10")) OR (c = "c"))', $o->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: ((условие) оператор (условие)).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition $o
     */
    $o = MultiCondition::reestablish('((a = "a") AND (b = "b"))');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->getLeftOperand();
    $this->assertEquals('a', $c->getValue());
    $this->assertEquals('=', $c->getOperator());
    $this->assertEquals('AND', $o->getLogicOperator());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition $o
     */
    $o = MultiCondition::reestablish('((a = "a") OR (b = "b"))');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->getLeftOperand();
    $this->assertEquals('a', $c->getValue());
    $this->assertEquals('=', $c->getOperator());
    $this->assertEquals('OR', $o->getLogicOperator());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition $o
     */
    $o = MultiCondition::reestablish('(((fieldA = "1") AND (fieldB = "2")) OR (fieldC = "3"))');
    $this->assertEquals('OR', $o->getLogicOperator());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->getRightOperand();
    $this->assertEquals('fieldC', $c->getField()->getName());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition $c
     */
    $c = $o->getLeftOperand();
    $c = $c->getLeftOperand();
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $this->assertEquals('1', $c->getValue());
  }

  /**
   * Допустимой строкой является строка вида: ((условие) оператор (условие)).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(MultiCondition::isReestablish('((fieldA = "1") AND (fieldB = "2"))'));
    $this->assertTrue(MultiCondition::isReestablish('(((fieldA = "1") AND (fieldB = "2")) OR (fieldC = "3"))'));
    $this->assertTrue(MultiCondition::isReestablish('((table.fieldA = "1")
                                                            AND (fieldB = "2"))'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(MultiCondition::isReestablish('((fieldA = "1"))'));
    $this->assertFalse(MultiCondition::isReestablish('((fieldA = "1") (fieldB = "2"))'));
    $this->assertFalse(MultiCondition::isReestablish('((fieldA = "1") AND fieldB = "2")'));
    $this->assertFalse(MultiCondition::isReestablish('(fieldA = "1") AND (fieldB = "2")'));
  }
}
