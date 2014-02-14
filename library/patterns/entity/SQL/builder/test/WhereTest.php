<?php
namespace D\library\patterns\entity\SQL\builder\test;

use D\library\patterns\entity\SQL\builder\Where;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class WhereTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var \D\library\patterns\entity\SQL\builder\Where
   */
  private static $object;

  public static function setUpBeforeClass(){
    self::$object = Where::getInstance();
  }

  protected function setUp(){
    self::$object->clear();
  }

  /**
   * Должен добавлять условие в стек.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   */
  public function testShouldAddConditionInStack(){
    $c = self::$object->create('id', '>', '5')->getConditions();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation', $c->top());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $f
     */
    $f = $c->top()->getField();
    $this->assertEquals('id', $f->getName());
    $this->assertEquals('>', $c->top()->getOperator());
    $this->assertEquals('5', $c->top()->getValue());
    $c = self::$object->create('id', '<', '10')->getConditions();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation', $c->top());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $f
     */
    $f = $c->top()->getField();
    $this->assertEquals('id', $f->getName());
    $this->assertEquals('<', $c->top()->getOperator());
    $this->assertEquals('10', $c->top()->getValue());
  }

  /**
   * Если третий параметр обрамлен в косые кавычки (`), он определяет объект Field.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testRightOperandFieldIfWrapQuotes(){
    $c = self::$object->create('id', '=', '`name`')->getConditions();
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $f
     */
    $f = $c->top()->getValue();
    $this->assertEquals('name', $f->getName());
  }

  /**
   * Если третий параметр не обрамлен в косые кавычки (`), он определяет строковое значение.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testRightOperandStringIfNoWrapQuotes(){
    $c = self::$object->create('id', '=', '5')->getConditions();
    $this->assertEquals('5', $c->top()->getValue());
  }

  /**
   * Если третий параметр не передан, он определяет параметризованное значение.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testRightOperandParamIfNull(){
    $c = self::$object->create('id', '=')->getConditions();
    $this->assertEquals(null, $c->top()->getValue());
  }

  /**
   * Если в качестве оператора указан in, то последний элемент может быть массивом с перечислением значений.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testOperationInIfOperatorSetIn(){
    $c = self::$object->create('id', 'in', [1, 2, 3])->getConditions();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation', $c->top());
    $this->assertEquals([1, 2, 3], $c->top()->getValues());
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    self::$object->create('id', 'in', '1');
  }

  /**
   * В качестве первого параметра может быть только строка.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testLeftOperandShouldBeString(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    self::$object->create(1, '=', '`id`');
  }

  /**
   * В качестве второго параметра может быть только одно из следующих значений: =, !=, >, <, >=, <=, in.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testOperatorShouldBeLogicOperator(){
    self::$object->create('name', '=', 'test');
    self::$object->create('name', '!=', 'test');
    self::$object->create('name', '>=', 'test');
    self::$object->create('name', '<=', 'test');
    self::$object->create('name', '>', 'test');
    self::$object->create('name', '<', 'test');
    self::$object->create('name', 'in', [1]);
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    self::$object->create('name', '*', 'test');
  }

  /**
   * В качестве третьего параметра может быть только строка, массив или null.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testRightOperandShouldBeStringArrayNull(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    self::$object->create('id', '=', 1);
  }

  /**
   * Если в первом или песледнем параметре присутствует точка, должен добавлять информацию о таблице поля.
   * @covers \D\library\patterns\entity\SQL\builder\Where::create
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   * @covers \D\library\patterns\entity\SQL\builder\Where::createCondition
   */
  public function testShouldAddTableFieldIfPointSet(){
    $c = self::$object->create('people.id', '=', '`people.name`')->getConditions();
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $f
     */
    $f = $c->top()->getField();
    $this->assertEquals('people', $f->getTable()->getTableName());
    $this->assertEquals('people', $f->getTable()->getTableName());
  }

  /**
   * Должен создавать логическое выражение для текущего условия с разделителем И и переданным условием в качестве правого операнда.
   * @covers \D\library\patterns\entity\SQL\builder\Where::andC
   */
  public function testShouldCreateAndMultiCondition(){
    $c = self::$object->create('id', '>', '5')->andC('id', '<', '10')->getConditions();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition', $c->top());
    $this->assertEquals('AND', $c->top()->getLogicOperator());
  }

  /**
   * Должен создавать логическое выражение для текущего условия с разделителем ИЛИ и переданным условием в качестве правого операнда.
   * @covers \D\library\patterns\entity\SQL\builder\Where::orC
   */
  public function testShouldCreateOrMultiCondition(){
    $c = self::$object->create('id', '>', '5')->orC('id', '<', '10')->getConditions();
    $this->assertInstanceOf('D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition', $c->top());
    $this->assertEquals('OR', $c->top()->getLogicOperator());
  }

  /**
   * Если передан параметр, должен объединять два последних условных выражения с указанным в параметре разделителем.
   * @covers \D\library\patterns\entity\SQL\builder\Where::last
   */
  public function testShouldCreateMultiConditionToTopStack(){
    $c = self::$object->create('id', '>', '5')->create('id', '<', '10')->andC('name', '=', 'test')->last('OR')->last();
    $this->assertEquals('WHERE ((id > "5") OR ((id < "10") AND (name = "test")))', $c->interpretation('mysql'));
  }

  /**
   * В качестве параметра может быть передана строка вида: AND или OR.
   * @covers \D\library\patterns\entity\SQL\builder\Where::last
   */
  public function testArgShouldBeAndOr(){
    self::$object->create('id', '>', '5')->create('id', '<', '10')->last('OR');
    self::$object->create('id', '>', '5')->create('id', '<', '10')->last('AND');
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    self::$object->create('id', '>', '5')->create('id', '<', '10')->last('and');
  }
}
