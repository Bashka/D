<?php
namespace D\library\patterns\entity\SQL\builder\test;

use D\library\patterns\entity\SQL\builder\Select;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class SelectTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var \D\library\patterns\entity\SQL\builder\Select
   */
  private static $object;

  public static function setUpBeforeClass(){
    self::$object = Select::getInstance();
  }

  protected function setUp(){
    self::$object->clear();
  }


  /**
   * Должен формировать объектную SQL инструкцию D\library\patterns\entity\SQL\operators\DML\components\Select.
   * @covers D\library\patterns\entity\SQL\builder\Select::fields
   */
  public function testShouldCreateObject(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Select', self::$object->fields()->get());
  }

  /**
   * Если параметр не передан, должен устанавливать все поля.
   * @covers D\library\patterns\entity\SQL\builder\Select::fields
   */
  public function testShouldAddAllFieldsIfNoArgs(){
    $this->assertTrue(self::$object->fields()->get()->isAllFields());
  }

  /**
   * Если параметр передан, должен устанавливать перечисленные в нем поля.
   * @covers D\library\patterns\entity\SQL\builder\Select::fields
   */
  public function testShouldAddFieldsIfArgs(){
    $this->assertEquals('id', self::$object->fields(['id'])->get()->getFields()[0]->getName());
  }

  /**
   * Если в качестве параметра передан ассоциативный массив, должен устанавливать целевые таблицы.
   * @covers D\library\patterns\entity\SQL\builder\Select::fields
   */
  public function testShouldAddFieldTables(){
    $this->assertEquals('people', self::$object->fields(['people' => 'id'])->get()->getFields()[0]->getTable()->getTableName());
  }

  /**
   * Должен формировать объектную SQL инструкцию D\library\patterns\entity\SQL\operators\DML\components\Select.
   * @covers D\library\patterns\entity\SQL\builder\Select::table
   */
  public function testShouldCreateObject2(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Select', self::$object->tables(['people'])->get());
  }

  /**
   * Должен устанавливать перечисленные в нем таблицы.
   * @covers D\library\patterns\entity\SQL\builder\Select::table
   */
  public function testShouldAddTables(){
    $this->assertEquals('people', self::$object->tables(['people'])->get()->getTables()[0]->getTableName());
  }

  /**
   * Должен устанавливать компонент Limit.
   * @covers D\library\patterns\entity\SQL\builder\Select::limit
   */
  public function testShouldAddLimit(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Limit', self::$object->tables(['people'])->limit(5)->get()->getLimit());
  }

  /**
   * Должен устанавливать компонент OrderBy.
   * @covers D\library\patterns\entity\SQL\builder\Select::orderBy
   */
  public function testShouldAddOrderBy(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\OrderBy', self::$object->tables(['people'])->orderBy(['name', 'phone'])->get()->getOrderBy());
  }

  /**
   * Должен добавлять компонент Join типа Inner.
   * @covers D\library\patterns\entity\SQL\builder\Select::innerJoin
   */
  public function testShouldAddInnerJoin(){
    $join = self::$object->tables(['people'])->innerJoin('student', 'people.id', '=', '`student.id`')->get()->getJoins()[0];
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Join', $join);
    $this->assertEquals('INNER', $join->getType());
  }

  /**
   * Должен добавлять компонент Join типа Cross.
   * @covers D\library\patterns\entity\SQL\builder\Select::crossJoin
   */
  public function testShouldAddCrossJoin(){
    $join = self::$object->tables(['people'])->crossJoin('student', 'people.id', '=', '`student.id`')->get()->getJoins()[0];
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Join', $join);
    $this->assertEquals('CROSS', $join->getType());
  }

  /**
   * Должен добавлять компонент Join типа Left.
   * @covers D\library\patterns\entity\SQL\builder\Select::leftJoin
   */
  public function testShouldAddLeftJoin(){
    $join = self::$object->tables(['people'])->leftJoin('student', 'people.id', '=', '`student.id`')->get()->getJoins()[0];
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Join', $join);
    $this->assertEquals('LEFT', $join->getType());
  }

  /**
   * Должен добавлять компонент Join типа Right.
   * @covers D\library\patterns\entity\SQL\builder\Select::rightJoin
   */
  public function testShouldAddRightJoUpdatein(){
    $join = self::$object->tables(['people'])->rightJoin('student', 'people.id', '=', '`student.id`')->get()->getJoins()[0];
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Join', $join);
    $this->assertEquals('RIGHT', $join->getType());
  }

  /**
   * Должен добавлять компонент Join типа Full.
   * @covers D\library\patterns\entity\SQL\builder\Select::fullJoin
   */
  public function testShouldAddFullJoin(){
    $join = self::$object->tables(['people'])->fullJoin('student', 'people.id', '=', '`student.id`')->get()->getJoins()[0];
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\Join', $join);
    $this->assertEquals('FULL', $join->getType());
  }

  /**
   * Должен возвращать сформированную объектную SQL инструкцию D\library\patterns\entity\SQL\operators\DML\components\Select.
   * @covers D\library\patterns\entity\SQL\builder\Select::get
   */
  public function testShouldReturnCreateObject(){
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\Select', self::$object->fields()->tables(['people'])->get());
  }

  /**
   * Должен возвращать SQL инструкцию Select в виде строки.
   * @covers D\library\patterns\entity\SQL\builder\Select::interpretation
   */
  public function testShouldReturnSQLString(){
    $this->assertEquals('SELECT * FROM people INNER JOIN student ON (people.id = student.id)  ORDER BY name,phone ASC LIMIT 10', self::$object->fields()->tables(['people'])->limit(10)->orderBy(['name', 'phone'])->innerJoin('student', 'people.id', '=', '`student.id`')->interpretation('mysql'));
    $this->assertEquals('SELECT name,phone FROM people  WHERE (id < "10")', self::$object->fields(['name', 'phone'])->tables(['people'])->where('id', '<', '10')->select->interpretation('mysql'));
  }

  /**
   * Должен возвращать объект класса D\library\patterns\entity\SQL\builder\Where с указанным условием.
   * @covers D\library\patterns\entity\SQL\builder\Select::where
   */
  public function testShouldReturnObjectWhere(){
    $o = self::$object->fields()->tables(['people'])->where('name', '=', 'ivan');
    $this->assertInstanceOf('D\library\patterns\entity\SQL\builder\Where', $o);
    $this->assertEquals('name', $o->last()->getCondition()->getField()->getName());
  }

  /**
   * Должен добавлять свойство select, ссылающееся на фабрику, объекту класса D\library\patterns\entity\SQL\builder\Where.
   * @covers D\library\patterns\entity\SQL\builder\Select::where
   */
  public function testShouldAddProperty(){
    $o = self::$object->fields()->tables(['people'])->where('name', '=', 'ivan');
    $this->assertTrue(isset($o->select));
    $this->assertInstanceOf('D\library\patterns\entity\SQL\builder\Select', $o->select);
  }

  /**
   * Должен выбрасывать исключение при вызове до метода table или fields.
   * @covers D\library\patterns\entity\SQL\builder\Select::where
   */
  public function testShouldThrowExceptionIfCallBeforeTableMethod(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $o = self::$object->where('id', '>', '5');
  }
}
