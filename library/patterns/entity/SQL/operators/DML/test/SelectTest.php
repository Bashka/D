<?php
namespace D\library\patterns\entity\SQL\operators\DML\test;

use D\library\patterns\entity\SQL\operators\DML\Select;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\FieldAlias;
use D\library\patterns\entity\SQL\operators\DML\components\Join;
use D\library\patterns\entity\SQL\operators\DML\components\Limit;
use D\library\patterns\entity\SQL\operators\DML\components\OrderBy;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class SelectTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен добавлять в запрос поле.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addField
   */
  public function testShouldAddField(){
    $o = new Select;
    $f = new Field('f');
    $o->addField($f);
    $this->assertEquals($f, $o->getFields()[0]);
  }

  /**
   * Если указанное поле уже было добавлено, должен выбрасывать исключение.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addField
   */
  public function testShouldThrowExceptionIfFieldAdded(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $o = new Select;
    $f = new Field('f');
    $o->addField($f);
    $o->addField($f);
  }

  /**
   * Должен добавлять в запрос поле с псевдонимом.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addAliasField
   */
  public function testShouldAddAliasField(){
    $o = new Select;
    $f = new FieldAlias(new Field('f'), 'alias');
    $o->addAliasField($f);
    $this->assertEquals($f, $o->getFields()[0]);
  }

  /**
   * Если указанное поле с псевдонимом уже было добавлено, должен выбрасывать исключение.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addAliasField
   */
  public function testShouldThrowExceptionIfAliasFieldAdded(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $o = new Select;
    $f = new FieldAlias(new Field('f'), 'alias');
    $o->addAliasField($f);
    $o->addAliasField($f);
  }

  /**
   * Должен добавлять указанную таблицу в запрос.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addTable
   */
  public function testShouldAddTable(){
    $o = new Select;
    $t = new Table('table');
    $o->addTable($t);
    $this->assertEquals($t, $o->getTables()[0]);
  }

  /**
   * Если указанная таблица уже была добавлена, должен выбрасывать исключение.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addTable
   */
  public function testShouldThrowExceptionIfTableAdded(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $o = new Select;
    $t = new Table('table');
    $o->addTable($t);
    $o->addTable($t);
  }

  /**
   * Должен добавлять указанное объединение в запрос.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addJoin
   */
  public function testShouldAddJoin(){
    $o = new Select;
    $j = new Join(Join::INNER, new Table('table'), new LogicOperation(new Field('fieldA'), '=', new Field('fieldB')));
    $o->addJoin($j);
    $this->assertEquals($j, $o->getJoins()[0]);
  }

  /**
   * Если указанная таблица уже была добавлена, должен выбрасывать исключение.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addJoin
   */
  public function testShouldThrowExceptionIfJoinAdded(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $o = new Select;
    $j = new Join(Join::INNER, new Table('table'), new LogicOperation(new Field('fieldA'), '=', new Field('fieldB')));
    $o->addJoin($j);
    $o->addJoin($j);
  }

  /**
   * Должен устанавливать условие отбора.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::insertWhere
   */
  public function testShouldSetWhere(){
    $o = new Select;
    $w = new Where(new LogicOperation(new Field('fieldA'), '=', new Field('fieldB')));
    $o->insertWhere($w);
    $this->assertEquals($w, $o->getWhere());
  }

  /**
   * Должен устанавливать схему сортировки.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::insertOrderBy
   */
  public function testShouldSetOrderBy(){
    $o = new Select;
    $ob = new OrderBy();
    $ob->addField(new Field('fieldA'));
    $o->insertOrderBy($ob);
    $this->assertEquals($ob, $o->getOrderBy());
  }

  /**
   * Должен устанавливать порог запроса.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::insertLimit
   */
  public function testShouldSetLimit(){
    $o = new Select;
    $l = new Limit(10);
    $o->insertLimit($l);
    $this->assertEquals($l, $o->getLimit());
  }

  /**
   * Должен определять, что запрашивают все поля таблиц.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::addAllField
   */
  public function testShouldAddAllFields(){
    $o = new Select;
    $o->addAllField();
    $this->assertTrue($o->isAllFields());
  }

  /**
   * Должен возвращать строку вида: SELECT *|((имяПоля[ as псевдоним])|(имяТаблицы.имяПоля))+ FROM (имяТаблицы)+[ (объединение)+][ WHERE условиеОтбора][ ORDER BY схема][ LIMIT ограничение].
   * Результат может меняться в зависимости от требуемого SQL диалекта.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::interpretation
   */
  public function testShouldInterpretation(){
    // Подготовка компонентов.
    $tA = new Table('tableA');
    $tB = new Table('tableB');
    $tC = new Table('tableC');
    $fA = new Field('fieldA');
    $fB = new Field('fieldB');
    $fC = new Field('fieldC');
    $fA->setTable($tA);
    $fC->setTable($tC);
    $fA = new FieldAlias($fA, 'fieldAAlias');
    $ob = new OrderBy;
    $ob->addField($fB);
    $w = new Where(new LogicOperation($fB, '>', "0"));
    $j = new Join(Join::INNER, $tC, new LogicOperation($fC, '=', $fB));
    // Сбор Select инструкции.
    $o = new Select;
    $o->addTable($tA);
    $o->addTable($tB);
    $o->addAliasField($fA);
    $o->addField($fB);
    $o->addJoin($j);
    $o->insertOrderBy($ob);
    $o->insertWhere($w);
    $this->assertEquals('SELECT tableA.fieldA AS fieldAAlias,fieldB FROM tableA,tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) WHERE (fieldB > "0") ORDER BY fieldB ASC', $o->interpretation());
  }

  /**
   * Если в инструкции присутствует компонент Limit, то в качестве параметра должен быть передан требуемый SQL диалект.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::interpretation
   */
  public function testArgShouldBeStringIfLimitAdded(){
    $o = new Select;
    $o->addAllField();
    $o->addTable(new Table('tableA'));
    $o->insertLimit(new Limit(10));
    $o->interpretation('mysql');
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $o->interpretation();
  }

  /**
   * Должен восстанавливаться из строки вида: SELECT *|((имяПоля[ as псевдоним])|(имяТаблицы.имяПоля))+ FROM (имяТаблицы)+[ (объединение)+][ ORDER BY схема][ LIMIT ограничение][ WHERE условиеОтбора].
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::reestablish
   */
  public function testShouldRestorableForString(){
    $o = Select::reestablish('SELECT * FROM tableA');
    $this->assertEquals([], $o->getFields());
    $o = Select::reestablish('SELECT fieldA FROM tableA');
    $this->assertEquals('fieldA', $o->getFields()[0]->getName());
    $this->assertEquals('tableA', $o->getTables()[0]->getTableName());
    $o = Select::reestablish('SELECT fieldA AS fa FROM tableA');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\FieldAlias $a
     */
    $a = $o->getFields()[0];
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $f
     */
    $f = $a->getComponent();
    $this->assertEquals('fieldA', $f->getName());
    $this->assertEquals('fa', $o->getFields()[0]->getAlias());
    $o = Select::reestablish('SELECT fieldA, tableB.fieldB FROM tableA, tableB');
    $this->assertEquals('tableB', $o->getFields()[1]->getTable()->getTableName());
    $this->assertEquals('tableB', $o->getTables()[1]->getTableName());
    $o = Select::reestablish('SELECT fieldA, tableB.fieldB FROM tableA, tableB LIMIT 10');
    $this->assertEquals(10, $o->getLimit()->getCountRow());
    $o = Select::reestablish('SELECT fieldA, tableB.fieldB FROM tableA,tableB ORDER BY fieldA,tableB.fieldB DESC LIMIT 10');
    $this->assertEquals('DESC', $o->getOrderBy()->getSortedType());
    $this->assertEquals('fieldA', $o->getOrderBy()->getFields()[0]->getName());
    $o = Select::reestablish('SELECT fieldA, tableB.fieldB FROM tableA,tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ORDER BY fieldA,tableB.fieldB DESC LIMIT 10');
    $this->assertEquals('INNER', $o->getJoins()[0]->getType());
    $o = Select::reestablish('SELECT fieldA, tableB.fieldB FROM tableA,tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) LEFT JOIN tableD ON (tableD.id = fieldB) ORDER BY fieldA,tableB.fieldB DESC LIMIT 10');
    $this->assertEquals('tableD', $o->getJoins()[1]->getTable()->getTableName());
    $o = Select::reestablish('SELECT fieldA,tableB.fieldB FROM tableA,tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) LEFT JOIN tableD ON (tableD.id = fieldB) ORDER BY fieldA,tableB.fieldB ASC LIMIT 10 WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition $m
     */
    $m = $o->getWhere()->getCondition();
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = $m->getLeftOperand();
    $this->assertEquals('0', $l->getValue());
    $this->assertEquals('SELECT fieldA,tableB.fieldB FROM tableA,tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) LEFT JOIN tableD ON (tableD.id = fieldB) WHERE ((fieldB > "0") OR (tableA.fieldA < "10")) ORDER BY fieldA,tableB.fieldB ASC LIMIT 10', $o->interpretation('mysql'));
  }

  /**
   * Допустимой строкой является строка вида: SELECT *|((имяПоля[ AS псевдоним])|(имяТаблицы.имяПоля))+ FROM (имяТаблицы)+[ (объединение)+][ ORDER BY схема][ LIMIT ограничение][ WHERE условиеОтбора].
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Select::isReestablish('SELECT * FROM tableA'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA FROM tableA'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA AS fa FROM tableA'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB)'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) LEFT JOIN tableD ON (tableD.id = fieldB)'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ORDER BY fieldA,tableB.fieldB ASC WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))'));
    $this->assertTrue(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ORDER BY fieldA,tableB.fieldB DESC LIMIT 10 WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\Select::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Select::isReestablish('fieldA FROM tableA'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA tableA'));
    $this->assertFalse(Select::isReestablish('SELECTfieldA FROM tableA'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA FROMtableA'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA tableB.fieldB FROM tableA, tableB'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA tableB'));
    $this->assertFalse(Select::isReestablish('SELECT FROM tableA, tableB'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM '));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON(tableC.fieldC = fieldB)'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB JOIN tableC ON (tableC.fieldC = fieldB)'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON ()'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB), LEFT JOIN tableD ON(tableD.id = fieldB)'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) WHERE((fieldB > "0") OR (tableA.fieldA < "10"))'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ((fieldB > "0") OR (tableA.fieldA < "10"))'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) WHERE ()'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ORDER BY fieldA,tableB.fieldB WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ORDER BY fieldA tableB.fieldB WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))'));
    $this->assertFalse(Select::isReestablish('SELECT fieldA,tableB.fieldB FROM tableA, tableB INNER JOIN tableC ON (tableC.fieldC = fieldB) ORDER BY fieldA,tableB.fieldB LIMIT 0 WHERE ((fieldB > "0") OR (tableA.fieldA < "10"))'));
  }
}
