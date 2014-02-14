<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\AndMultiCondition;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\condition\OrMultiCondition;
use D\library\patterns\entity\SQL\operators\DML\components\Field;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class QueryConditionTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен добавлять добавлять условие в выражение.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition::addCondition
   */
  public function testShouldAddCondition(){
    $qc = new AndMultiCondition;
    $loName = new LogicOperation(new Field('name'), '=', 'ivan');
    $loOID = new LogicOperation(new Field('OID'), '<', '10');
    $qc->addCondition($loName);
    $qc->addCondition($loOID);
    $this->assertEquals($loName, $qc->getConditions()[0]);
    $this->assertEquals($loOID, $qc->getConditions()[1]);
  }

  /**
   * Должен возвращать строку вида: ((условие) оператор (условие)[ оператор (условие)]*).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition::interpretation
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\AndMultiCondition::interpretation
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\OrMultiCondition::interpretation
   */
  public function testShouldInterpretation(){
    $qc = new AndMultiCondition;
    $loName = new LogicOperation(new Field('name'), '=', 'ivan');
    $loOID = new LogicOperation(new Field('OID'), '<', '10');
    $qc->addCondition($loName);
    $qc->addCondition($loOID);
    $this->assertEquals('((name = "ivan") AND (OID < "10"))', $qc->interpretation());
  }

  /**
   * Должен выбрасывать исключение, если на номент вызова добавлено менее двух условий.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition::interpretation
   */
  public function testShouldThrowExceptionIfNotConditions(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $qc = new AndMultiCondition;
    $qc->interpretation();
  }

  /**
   * Должен восстанавливаться из строки вида: (условие) оператор (условие)[ оператор (условие)]*.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\AndMultiCondition $o
     */
    $o = AndMultiCondition::reestablish('((a = "a") AND (b = "b") AND (c = "c"))');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->getConditions()[0];
    $this->assertEquals('a', $c->getValue());
    $this->assertEquals('=', $c->getOperator());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\OrMultiCondition $o
     */
    $o = OrMultiCondition::reestablish('((a = "a") OR (b = "b") OR (c = "c"))');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->getConditions()[0];
    $this->assertEquals('a', $c->getValue());
    $this->assertEquals('=', $c->getOperator());
  }

  /**
   * Допустимой строкой является строка вида: (условие) оператор (условие)[ оператор (условие)]*.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(OrMultiCondition::isReestablish('((a = "a") OR (b = "b"))'));
    $this->assertTrue(AndMultiCondition::isReestablish('((a = "a") AND (b = "b") AND (c = "c"))'));
    $this->assertTrue(OrMultiCondition::isReestablish('((a = "a") OR (b = "b") OR (c = "c"))'));
    $this->assertTrue(OrMultiCondition::isReestablish('(
                                                                (
                                                                  (a = "a") AND
                                                                  (b = "b") AND
                                                                  (c = "c")
                                                                ) OR
                                                                (b = "b") OR
                                                                (c = "c")
                                                              )'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(AndMultiCondition::isReestablish('((a = "a") OR (b = "b"))'));
    $this->assertFalse(AndMultiCondition::isReestablish('((a = "a") (b = "b"))'));
    $this->assertFalse(AndMultiCondition::isReestablish('((a = "a"))'));
  }
}
