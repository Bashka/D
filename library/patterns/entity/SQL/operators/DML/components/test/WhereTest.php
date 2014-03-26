<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Where;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class WhereTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять логическое выражение.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Where::__construct
   */
  public function testShouldSetCondition(){
    $c = new LogicOperation(new Field('fieldA'), '=', new Field('fieldB'));
    $w = new Where($c);
    $this->assertEquals($c, $w->getCondition());
  }

  /**
   * Должен возвращать строку вида: WHERE условие.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Where::interpretation
   */
  public function testShouldInterpretation(){
    $w = new Where(new LogicOperation(new Field('a'), '=', 'a'));
    $this->assertEquals('WHERE (a = "a")', $w->interpretation());
  }

  /**
   * Должен восстанавливаться из строки вида: WHERE условие.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Where::reestablish
   */
  public function testShouldRestorableForString(){
    $r = Where::reestablish('WHERE (field = "0")');
    /**
     * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation $c
     */
    $c = $r->getCondition();
    $this->assertEquals('0', $c->getValue());
  }

  /**
   * Допустимой строкой является строка вида: WHERE условие.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Where::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Where::isReestablish('WHERE (field = "0")'));
    $this->assertTrue(Where::isReestablish('WHERE ((fieldA = "0")
                                                         AND (fieldB = "0"))'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Where::isReestablish
   */
  public function testBedString(){
    $this->assertTrue(Where::isReestablish('WHERE (table.field = "0")'));
    $this->assertFalse(Where::isReestablish('(field = "0")'));
    $this->assertFalse(Where::isReestablish('WHERE field = "0"'));
    $this->assertFalse(Where::isReestablish('WHERE ()'));
  }
}
