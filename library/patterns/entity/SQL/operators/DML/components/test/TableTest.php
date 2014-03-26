<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\Table;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class TableTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен идентифицировать объект именем таблицы.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::__construct
   */
  public function testShouldInitObjectTableName(){
    $o = new Table('people');
    $this->assertEquals('people', $o->getTableName());
  }

  /**
   * Именем таблицы может быть только строка.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::__construct
   */
  public function testTableNameStringIs(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Table(1);
  }

  /**
   * Должен возвращать имя таблицы.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::getTableName
   */
  public function testShouldReturnTableName(){
    $o = new Table('people');
    $this->assertEquals('people', $o->getTableName());
  }

  /**
   * Должен возвращать строку вида: имяТаблицы.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::interpretation
   */
  public function testShouldInterpretationTableName(){
    $o = new Table('people');
    $this->assertEquals('people', $o->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: имяТаблицы.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Table $o
     */
    $o = Table::reestablish('people');
    $this->assertEquals('people', $o->interpretation());
  }

  /**
   * Допустимой строкой является строка вида: имяТаблицы.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Table::isReestablish('table'));
    $this->assertTrue(Table::isReestablish('table5'));
    $this->assertTrue(Table::isReestablish('table_test'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Table::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Table::isReestablish('1table'));
    $this->assertFalse(Table::isReestablish('tab le'));
    $this->assertFalse(Table::isReestablish('test+'));
  }
}
