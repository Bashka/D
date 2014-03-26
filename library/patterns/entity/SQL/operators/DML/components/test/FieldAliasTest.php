<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\FieldAlias;
use D\library\patterns\entity\SQL\operators\DML\components\Table;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class FieldAliasTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять целевое поле и его псевданим.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::__construct
   */
  public function testShouldSetFieldAndAlias(){
    $o = new FieldAlias(new Field('name'), 'peopleName');
    $this->assertEquals('peopleName', $o->getAlias());
  }

  /**
   * В качестве псевдонима может выступать только не пустая строка.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Alias::__construct
   */
  public function testAliasCanBeString(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new FieldAlias(new Field('name'), 1);
  }

  /**
   * В качестве целевого поля может выступать только объект класса Field.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::__construct
   */
  public function testFieldCanBeObjectField(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new FieldAlias(new Table('people'), 'peopleName');
  }

  /**
   * Должен возвращать псевдоним.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Alias::getAlias
   */
  public function testShouldReturnAlias(){
    $o = new FieldAlias(new Field('name'), 'peopleName');
    $this->assertEquals('peopleName', $o->getAlias());
  }

  /**
   * Должен возвращать целевое поле.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::getComponent
   */
  public function testShouldReturnField(){
    $o = new FieldAlias(new Field('name'), 'peopleName');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $c
     */
    $c = $o->getComponent();
    $this->assertEquals('name', $c->getName());
  }

  /**
   * Должен формировать строку вида: имяПоля as псевдоним.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::interpretation
   */
  public function testShouldInterpretationAlias(){
    $o = new FieldAlias(new Field('name'), 'peopleName');
    $this->assertEquals('name AS peopleName', $o->interpretation());
  }

  /**
   * Должен восстанавливать объект из строки вида: имяПоля as псевдоним - и вида: имяТаблицы.имяПоля as псевдоним.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\FieldAlias $o
     */
    $o = FieldAlias::reestablish('name AS peopleName');
    $this->assertEquals('peopleName', $o->getAlias());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $c
     */
    $c = $o->getComponent();
    $this->assertEquals('name', $c->getName());
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\FieldAlias $o
     */
    $o = FieldAlias::reestablish('people.name AS peopleName');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\Field $c
     */
    $c = $o->getComponent();
    $this->assertEquals('people', $c->getTable()->getTableName());
  }

  /**
   * Допустимыми являются строки вида: `имяПоля` as псевдоним - и вида: имяТаблицы.имяПоля as псевдоним.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(FieldAlias::isReestablish('test AS t'));
    $this->assertTrue(FieldAlias::isReestablish('table.field AS f'));
    $this->assertTrue(FieldAlias::isReestablish('field5_ AS f'));
    $this->assertTrue(FieldAlias::isReestablish('_field AS f'));
  }

  /**
   * Должен
   * @covers D\library\patterns\entity\SQL\operators\DML\components\FieldAlias::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(FieldAlias::isReestablish('1field AS f'));
    $this->assertFalse(FieldAlias::isReestablish('field f'));
    $this->assertFalse(FieldAlias::isReestablish('tableA.field AS '));
    $this->assertFalse(FieldAlias::isReestablish('table.field AS 5'));
  }
}
