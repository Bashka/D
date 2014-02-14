<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Join;
use D\library\patterns\entity\SQL\operators\DML\components\Table;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class JoinTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен поределять тип, целевую таблицу и условие объединения.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Join::__construct
   */
  public function testShouldSetJoinComponents(){
    $o = new Join(Join::INNER, new Table('table'), new LogicOperation(new Field('fieldA'), '=', new Field('fieldB')));
    $this->assertEquals(Join::INNER, $o->getType());
    $this->assertEquals('table', $o->getTable()->getTableName());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->getCondition();
    $this->assertEquals('fieldB', $c->getValue()->getName());
  }

  /**
   * В качестве типа может быть только: CROSS, INNER, LEFT, RIGHT, FULL.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Join::__construct
   */
  public function testTestShouldBeCROSSorINNERorLEFTorRIGHTorFULL(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Join('TEST', new Table('table'), new LogicOperation(new Field('fieldA'), '=', new Field('fieldB')));
  }

  /**
   * Должен возвращать строку вида: тип JOIN таблица ON условие.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Join::interpretation
   */
  public function testShouldInterpretation(){
    $o = new Join(Join::INNER, new Table('table'), new LogicOperation(new Field('fieldA'), '=', new Field('fieldB')));
    $this->assertEquals('INNER JOIN table ON (fieldA = fieldB)', $o->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: тип JOIN таблица ON условие
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Join::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Join $j
     */
    $j = Join::reestablish('INNER JOIN table ON (fieldA = fieldB)');
    $this->assertEquals(Join::INNER, $j->getType());
    $this->assertEquals('table', $j->getTable()->getTableName());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $j->getCondition();
    $this->assertEquals('fieldB', $c->getValue()->getName());
  }

  /**
   * Допустимой строкой является строка вида: тип JOIN таблица ON условие
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Join::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Join::isReestablish('INNER JOIN table ON (fieldA = fieldB)'));
    $this->assertTrue(Join::isReestablish('LEFT JOIN table ON (table.fieldA = table.fieldB)'));
    $this->assertTrue(Join::isReestablish('LEFT JOIN table
                                                 ON (table.fieldA = table.fieldB)'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Join::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Join::isReestablish('X JOIN table ON (fieldA = fieldB)'));
    $this->assertFalse(Join::isReestablish('CROSI JOIN table ON (fieldA = fieldB)'));
    $this->assertFalse(Join::isReestablish('INNER J table ON (fieldA = fieldB)'));
    $this->assertFalse(Join::isReestablish('INNER JOIN table (fieldA = fieldB)'));
    $this->assertFalse(Join::isReestablish('INNER JOIN table ON fieldA = fieldB'));
  }
}
