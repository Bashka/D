<?php
namespace D\library\patterns\entity\SQL\builder\test;

use D\library\patterns\entity\SQL\builder\Update;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class UpdateTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var \D\library\patterns\entity\SQL\builder\Update
   */
  private static $object;

  public static function setUpBeforeClass(){
    self::$object = Update::getInstance();
  }

  protected function setUp(){
    self::$object->clear();
  }

  /**
   * Должен формировать объектную SQL инструкцию Update для указанной таблицы.
   * @covers D\library\patterns\entity\SQL\builder\Update::table
   */
  public function testShouldCreateObject(){
    $o = self::$object->table('people')->get();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Update', $o);
    $this->assertEquals('people', $o->getTable()->getTableName());
  }

  /**
   * Должен возвращать сформированную объектную SQL инструкцию D\library\patterns\entity\SQL\operators\DML\Update.
   * @covers D\library\patterns\entity\SQL\builder\Update::get
   */
  public function testShouldReturnCreateObject(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Update', self::$object->table('people')->get());
  }

  /**
   * Должен добавлять данные в инструкцию Update.
   * @covers D\library\patterns\entity\SQL\builder\Update::data
   */
  public function testShouldAddDataInObjectInsert(){
    $this->assertEquals(['petr'], self::$object->table('people')->data(['name' => 'petr'])->get()->getValues());
  }

  /**
   * Должен возвращать SQL инструкцию Update в виде строки.
   * @covers D\library\patterns\entity\SQL\builder\Update::interpretation
   */
  public function testShouldReturnSQLString(){
    $this->assertEquals('UPDATE people SET name = "petr"', self::$object->table('people')->data(['name' => 'petr'])->interpretation('mysql'));
    $this->assertEquals('UPDATE people SET name = "petr" WHERE (name = "ivan")', self::$object->table('people')->data(['name' => 'petr'])->where('name', '=', 'ivan')->update->interpretation('mysql'));
  }

  /**
   * Должен возвращать объект класса \D\library\patterns\entity\SQL\builder\Where с указанным условием.
   * @covers D\library\patterns\entity\SQL\builder\Update::where
   */
  public function testShouldReturnObjectWhere(){
    $o = self::$object->table('people')->data(['name' => 'petr'])->where('name', '=', 'ivan');
    $this->assertInstanceOf('D\library\patterns\entity\SQL\builder\Where', $o);
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $l
     */
    $l = $o->last()->getCondition();
    $this->assertEquals('name', $l->getField()->getName());
  }

  /**
   * Должен добавлять свойство update, ссылающееся на фабрику, объекту класса \D\library\patterns\entity\SQL\builder\Where.
   * @covers D\library\patterns\entity\SQL\builder\Update::where
   */
  public function testShouldAddProperty(){
    $o = self::$object->table('people')->data(['name' => 'petr'])->where('name', '=', 'ivan');
    $this->assertTrue(isset($o->update));
    $this->assertInstanceOf('D\library\patterns\entity\SQL\builder\Update', $o->update);
  }

  /**
   * Должен выбрасывать исключение при вызове до метода table.
   * @covers D\library\patterns\entity\SQL\builder\Update::where
   */
  public function testShouldThrowExceptionIfCallBeforeTableMethod(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $o = self::$object->where('id', '>', '5');
  }
}
