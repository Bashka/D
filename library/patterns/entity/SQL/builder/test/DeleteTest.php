<?php
namespace D\library\patterns\entity\SQL\builder\test;

use D\library\patterns\entity\SQL\builder\Delete;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class DeleteTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var \D\library\patterns\entity\SQL\builder\Delete
   */
  private static $object;

  public static function setUpBeforeClass(){
    self::$object = Delete::getInstance();
  }

  protected function setUp(){
    self::$object->clear();
  }

  /**
   * Должен формировать объектную SQL инструкцию Delete для указанной таблицы.
   * @covers D\library\patterns\entity\SQL\builder\Delete::table
   */
  public function testShouldCreateObject(){
    $o = self::$object->table('people')->get();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Delete', $o);
    $this->assertEquals('people', $o->getTable()->getTableName());
  }

  /**
   * Должен возвращать сформированную объектную SQL инструкцию D\library\patterns\entity\SQL\operators\DML\Delete.
   * @covers D\library\patterns\entity\SQL\builder\Delete::get
   */
  public function testShouldReturnCreateObject(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Delete', self::$object->table('people')->get());
  }

  /**
   * Должен возвращать SQL инструкцию Delete в виде строки.
   * @covers D\library\patterns\entity\SQL\builder\Delete::interpretation
   */
  public function testShouldReturnSQLString(){
    $this->assertEquals('DELETE FROM people', self::$object->table('people')->interpretation('mysql'));
    $this->assertEquals('DELETE FROM people WHERE (id > "5")', self::$object->table('people')->where('id', '>', '5')->delete->interpretation('mysql'));
  }

  /**
   * Должен возвращать объект класса D\library\patterns\entity\SQL\builder\Where с указанным условием.
   * @covers D\library\patterns\entity\SQL\builder\Delete::where
   */
  public function testShouldReturnObjectWhere(){
    $o = self::$object->table('people')->where('id', '>', '5');
    $this->assertInstanceOf('D\library\patterns\entity\SQL\builder\Where', $o);
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $o->last()->getCondition();
    $this->assertEquals('id', $c->getField()->getName());
  }

  /**
   * Должен добавлять свойство delete, ссылающееся на фабрику, объекту класса D\library\patterns\entity\SQL\builder\Where.
   * @covers D\library\patterns\entity\SQL\builder\Delete::where
   */
  public function testShouldAddProperty(){
    $o = self::$object->table('people')->where('id', '>', '5');
    $this->assertTrue(isset($o->delete));
    $this->assertInstanceOf('D\library\patterns\entity\SQL\builder\Delete', $o->delete);
  }

  /**
   * Должен выбрасывать исключение при вызове до метода table.
   * @covers D\library\patterns\entity\SQL\builder\Delete::where
   */
  public function testShouldThrowExceptionIfCallBeforeTableMethod(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $o = self::$object->where('id', '>', '5');
  }
}
