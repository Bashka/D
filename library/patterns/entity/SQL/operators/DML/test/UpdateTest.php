<?php
namespace D\library\patterns\entity\SQL\operators\DML\test;

use D\library\patterns\entity\SQL\operators\DML\Update;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class UpdateTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять целевую таблицу.
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::__construct
   */
  public function testShouldSetTable(){
    $o = new Update(new Table('table'));
    $this->assertEquals('table', $o->getTable()->getTableName());
  }

  /**
   * Должен добавлять изменяемые данные.
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::addData
   */
  public function testShouldAddFieldAndValue(){
    $o = new Update(new Table('table'));
    $o->addData(new Field('name'), 'ivan');
    $this->assertEquals('name', $o->getFields()[0]->getName());
    $this->assertEquals('ivan', $o->getValues()[0]);
  }

  /**
   * В качестве значения могут выступать данные следующих типов: number, string, boolean, null.
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::addData
   */
  public function testValueShouldBeStringNumberBoolean(){
    (new Update(new Table('table')))->addData(new Field('name'), 'text');
    (new Update(new Table('table')))->addData(new Field('name'), 1);
    (new Update(new Table('table')))->addData(new Field('name'), 1.1);
    (new Update(new Table('table')))->addData(new Field('name'), true);
    (new Update(new Table('table')))->addData(new Field('name'));
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    (new Update(new Table('table')))->addData(new Field('name'), []);
  }

  /**
   * Должен определять условие отбора.
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::insertWhere
   */
  public function testShouldSetWhere(){
    $o = new Update(new Table('table'));
    $w = new Where(new LogicOperation(new Field('name'), '=', 'ivan'));
    $o->insertWhere($w);
    $this->assertEquals($w, $o->getWhere());
  }

  /**
   * Должен возвращать строку вида: UPDATE таблица SET поле = значение[, поле = значение]*[WHERE условие].
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::interpretation
   */
  public function testShouldInterpretation(){
    $o = new Update(new Table('table'));
    $o->addData(new Field('fieldA'), "1");
    $o->addData(new Field('fieldB'));
    $o->addData(new Field('fieldC'), "3");
    $this->assertEquals('UPDATE table SET fieldA = "1",fieldB = ?,fieldC = "3"', $o->interpretation());
    $o->insertWhere(new Where(new LogicOperation(new Field('fieldD'), '>', '5')));
    $this->assertEquals('UPDATE table SET fieldA = "1",fieldB = ?,fieldC = "3" WHERE (fieldD > "5")', $o->interpretation());
  }

  /**
   * Должен выбрасывать исключение в случае отсутствия изменяемых данных.
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::interpretation
   */
  public function testShouldThrowExceptionIfNotValues(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $o = new Update(new Table('table'));
    $o->interpretation();
  }

  /**
   * Должен восстанавливаться из строки вида: UPDATE таблица SET поле = значение[, поле = значение]*[WHERE условие].
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::reestablish
   */
  public function testShouldRestorableForString(){
    $o = Update::reestablish('UPDATE table SET fieldA = "1", fieldB = ?, fieldC = "3" WHERE (fieldD > "5")');
    $this->assertEquals('UPDATE table SET fieldA = "1",fieldB = ?,fieldC = "3" WHERE (fieldD > "5")', $o->interpretation());
    $this->assertEquals('table', $o->getTable()->getTableName());
  }

  /**
   * Допустимой строкой является строка вида: UPDATE таблица SET поле = значение[, поле = значение]*[WHERE условие].
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Update::isReestablish('UPDATE table SET fieldA = "1"'));
    $this->assertTrue(Update::isReestablish('UPDATE table SET fieldA = "1",fieldB = "2", fieldC = "3"'));
    $this->assertTrue(Update::isReestablish('UPDATE table SET fieldA = "1",fieldB = ?, fieldC = "3"'));
    $this->assertTrue(Update::isReestablish('UPDATE table SET table.fieldA = "1", table.fieldB = "2"'));
    $this->assertTrue(Update::isReestablish('UPDATE table
                                                   SET fieldA = "1", fieldB = "2", fieldC = "3"
                                                   WHERE (fieldD > "5")'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\Update::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Update::isReestablish('table SET fieldA = "1",fieldB = "2", fieldC = "3"'));
    $this->assertFalse(Update::isReestablish('UPDATE table SET fieldA = ??'));
    $this->assertFalse(Update::isReestablish('UPDATE table fieldA = "1",fieldB = "2", fieldC = "3"'));
    $this->assertFalse(Update::isReestablish('UPDATE table SET fieldA = "1" fieldB = "2"'));
    $this->assertFalse(Update::isReestablish('UPDATE table SET fieldA "1"'));
  }
}
