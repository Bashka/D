<?php
namespace D\library\patterns\entity\SQL\builder\test;

use D\library\patterns\entity\SQL\builder\Insert;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class InsertTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var \D\library\patterns\entity\SQL\builder\Insert
   */
  private static $object;

  public static function setUpBeforeClass(){
    self::$object = Insert::getInstance();
  }
  /**
   * Должен формировать объектную SQL инструкцию Insert для указанной таблицы.
   * @covers D\library\patterns\entity\SQL\builder\Insert::table
   */
  public function testShouldCreateObject(){
    $o = self::$object->table('people')->get();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Insert', $o);
    $this->assertEquals('people', $o->getTable()->getTableName());
  }

  /**
   * Должен возвращать сформированную объектную SQL инструкцию PPHP\tools\patterns\database\query\Insert.
   * @covers D\library\patterns\entity\SQL\builder\Insert::get
   */
  public function testShouldReturnCreateObject(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Insert', self::$object->table('people')->get());
  }

  /**
   * Должен добавлять данные в инструкцию Insert.
   * @covers D\library\patterns\entity\SQL\builder\Insert::data
   */
  public function testShouldAddDataInObjectInsert(){
    $this->assertEquals(['1', 'ivan', '12345'], self::$object->table('people')->data(['OID' => '1', 'name' => 'ivan', 'phone' => '12345'])->get()->getValues());
  }

  /**
   * Должен возвращать SQL инструкцию Insert в виде строки.
   * @covers D\library\patterns\entity\SQL\builder\Insert::interpretation
   */
  public function testShouldReturnSQLString(){
    $this->assertEquals('INSERT INTO people (OID,name,phone) VALUES ("1","ivan","12345")', self::$object->table('people')->data(['OID' => '1', 'name' => 'ivan', 'phone' => '12345'])->interpretation('mysql'));
  }
}
