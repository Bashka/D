<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\OrderBy;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class OrderByTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять тип сортировки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::__construct
   */
  public function testShouldSetType(){
    $ob = new OrderBy();
    $this->assertEquals('ASC', $ob->getSortedType());
    $ob = new OrderBy('ASC');
    $this->assertEquals('ASC', $ob->getSortedType());
    $ob = new OrderBy('DESC');
    $this->assertEquals('DESC', $ob->getSortedType());
  }

  /**
   * Допустимыми типами являются: ASC или DESC.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::__construct
   */
  public function testTypeShouldBeASCorDESC(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new OrderBy('TEST');
  }

  /**
   * Должен добавлять поле сортировки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::addField
   */
  public function testShouldAddSortField(){
    $ob = new OrderBy();
    $ob->addField(new Field('OID'));
    $ob->addField(new Field('name'));
    $this->assertEquals('OID', $ob->getFields()[0]->getName());
    $this->assertEquals('name', $ob->getFields()[1]->getName());
  }

  /**
   * Должен возвращать строку вида: ORDER BY имяПоля[, имяПоля]* (ASC)|(DESC).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::interpretation
   */
  public function testShouldInterpretation(){
    $ob = new OrderBy();
    $ob->addField(new Field('testA'));
    $ob->addField(new Field('testB'));
    $this->assertEquals('ORDER BY testA,testB ASC', $ob->interpretation());
  }

  /**
   * Должен выбрасывать исключение в случае отсутствия хотя бы одного поля сортировки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::interpretation
   */
  public function testShouldThrowExceptionIfNotFields(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $ob = new OrderBy();
    $ob->interpretation();
  }

  /**
   * Должен восстанавливаться из строки вида: ORDER BY имяПоля[, имяПоля]* (ASC)|(DESC).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::reestablish
   */
  public function testShouldRestorableForString(){
    $ob = OrderBy::reestablish('ORDER BY fieldA,fieldB, fieldC DESC');
    $fields = $ob->getFields();
    $this->assertEquals('fieldA', $fields[0]->getName());
    $this->assertEquals('fieldC', $fields[2]->getName());
    $this->assertEquals('DESC', $ob->getSortedType());
    $ob = OrderBy::reestablish('ORDER BY table.fieldA, table.fieldB DESC');
    $fields = $ob->getFields();
    $this->assertEquals('table', $fields[0]->getTable()->getTableName());
  }

  /**
   * Допустимой строкой является строка вида: ORDER BY имяПоля[, имяПоля]* (ASC)|(DESC).
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(OrderBy::isReestablish('ORDER BY fieldA,fieldB, table.fieldC DESC'));
    $this->assertTrue(OrderBy::isReestablish('ORDER BY fieldA,fieldB, table.fieldC ASC'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\OrderBy::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(OrderBy::isReestablish('ORDER fieldA,fieldB, table.fieldC DESC'));
    $this->assertFalse(OrderBy::isReestablish('ORDER BY fieldA,fieldB, table.fieldC'));
    $this->assertFalse(OrderBy::isReestablish('ORDER BY fieldA,fieldB, table.fieldC DSC'));
    $this->assertFalse(OrderBy::isReestablish('ORDER BY fieldA,fieldB, table.fieldC AC'));
  }
}
