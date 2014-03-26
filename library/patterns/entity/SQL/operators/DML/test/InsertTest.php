<?php
namespace D\library\patterns\entity\SQL\operators\DML\test;

use D\library\patterns\entity\SQL\operators\DML\Insert;
use D\library\patterns\entity\SQL\operators\DML\Select;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class InsertTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять целевую таблицу.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::__construct
   */
  public function testShouldSetTable(){
    $o = new Insert(new Table('table'));
    $this->assertEquals('table', $o->getTable()->getTableName());
  }

  /**
   * Должен добавлять значению полю.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::addData
   */
  public function testShouldAddFieldAndValue(){
    $o = new Insert(new Table('table'));
    $o->addData(new Field('name'), 'ivan');
    $this->assertEquals('name', $o->getFields()[0]->getName());
    $this->assertEquals('ivan', $o->getValues()[0]);
  }

  /**
   * В качестве значения могут выступать данные следующих типов: number, string, boolean, null.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::addData
   */
  public function testValueShouldBeStringNumberBoolean(){
    (new Insert(new Table('table')))->addData(new Field('name'), 'text');
    (new Insert(new Table('table')))->addData(new Field('name'), 1);
    (new Insert(new Table('table')))->addData(new Field('name'), 1.1);
    (new Insert(new Table('table')))->addData(new Field('name'), true);
    (new Insert(new Table('table')))->addData(new Field('name'));
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    (new Insert(new Table('table')))->addData(new Field('name'), []);
  }

  /**
   * Должен устанавливать в качестве значения вложенный запрос.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::setSelect
   */
  public function testShouldSetInsertedSelect(){
    $s = new Select;
    $s->addAllField();
    $s->addTable(new Table('people'));
    $o = new Insert(new Table('table'));
    $o->setSelect($s);
    $this->assertEquals($s, $o->getSelect());
  }

  /**
   * Должен возвращать строку вида: INSERT INTO таблица (поле[, поле]*) VALUES (значение[, значение]) - если установлены константные данные.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::interpretation
   */
  public function testShouldInterpretationIfConstValues(){
    $o = new Insert(new Table('table'));
    $o->addData(new Field('fieldA'), "1");
    $o->addData(new Field('fieldB'), "2");
    $o->addData(new Field('fieldC'), "3");
    $o->addData(new Field('fieldD'));
    $this->assertEquals('INSERT INTO table (fieldA,fieldB,fieldC,fieldD) VALUES ("1","2","3",?)', $o->interpretation());
  }

  /**
   * Должен возвращать строку вида: INSERT INTO таблица инструкцияSelect - если установлен вложеный запрос.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::interpretation
   */
  public function testShouldInterpretationIfInsertedSelect(){
    $s = new Select;
    $s->addAllField();
    $s->addTable(new Table('people'));
    $o = new Insert(new Table('table'));
    $o->setSelect($s);
    $this->assertEquals('INSERT INTO table SELECT * FROM people', $o->interpretation());
  }

  /**
   * Вложенный запрос имеет больший приоритет.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::interpretation
   */
  public function testInsertedSelectShouldBeFirst(){
    $o = new Insert(new Table('table'));
    $o->addData(new Field('fieldA'), "1");
    $s = new Select;
    $s->addAllField();
    $s->addTable(new Table('people'));
    $o->setSelect($s);
    $this->assertEquals('INSERT INTO table SELECT * FROM people', $o->interpretation());
  }

  /**
   * Должен выбрасывать исключение в случае отсутствия константных данных или вложенного запроса.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::interpretation
   */
  public function testShouldThrowExceptionIfNotValues(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $o = new Insert(new Table('table'));
    $o->interpretation();
  }

  /**
   * Должен восстанавливаться из строки вида: INSERT INTO таблица (поле[, поле]*) VALUES (значение[, значение]).
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::reestablish
   */
  public function testShouldRestorableForString(){
    $o = Insert::reestablish('INSERT INTO table (fieldA, fieldB, fieldC) VALUES ("1", ?, "3")');
    $this->assertEquals('INSERT INTO table (fieldA,fieldB,fieldC) VALUES ("1",?,"3")', $o->interpretation());
    $this->assertEquals('table', $o->getTable()->getTableName());
  }

  /**
   * Допустимой строкой является строка вида: INSERT INTO таблица (поле[, поле]*) VALUES (значение[, значение]).
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Insert::isReestablish('INSERT INTO table (fieldA) VALUES ("1")'));
    $this->assertTrue(Insert::isReestablish('INSERT INTO table (fieldA,fieldB, fieldC) VALUES ("1","2","3")'));
    $this->assertTrue(Insert::isReestablish('INSERT INTO table (fieldA,fieldB, fieldC) VALUES ("1",?,"3")'));
    $this->assertTrue(Insert::isReestablish('INSERT INTO table (table.fieldA, TABLE.fieldB) VALUES ("1","2")'));
    $this->assertTrue(Insert::isReestablish('INSERT INTO table
                                                          (fieldA,fieldB, fieldC)
                                                   VALUES ("1",     "2",      "3")'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\Insert::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Insert::isReestablish('INSERT INTO table (fieldA,fieldB, fieldC) VALUES ("1",??,"3")'));
    $this->assertFalse(Insert::isReestablish('INSERT table (fieldA,fieldB, fieldC) VALUES ("1","2","3")'));
    $this->assertFalse(Insert::isReestablish('INTO table (fieldA,fieldB, fieldC) VALUES ("1","2","3")'));
    $this->assertFalse(Insert::isReestablish('INSERT INTO table (fieldA,fieldB, fieldC) ("1","2","3")'));
    $this->assertFalse(Insert::isReestablish('INSERT INTO table fieldA,fieldB, fieldC VALUES ("1","2","3")'));
    $this->assertFalse(Insert::isReestablish('INSERT INTO table (fieldA,fieldB, fieldC) VALUES "1","2","3"'));
    $this->assertFalse(Insert::isReestablish('INSERT INTO table (fieldA,fieldB) VALUES ("1" "2")'));
    $this->assertFalse(Insert::isReestablish('INSERT INTO table (fieldA fieldB) VALUES ("1","2")'));
  }
}
