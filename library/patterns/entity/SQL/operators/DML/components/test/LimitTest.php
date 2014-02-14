<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\test;

use D\library\patterns\entity\SQL\operators\DML\components\Limit;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class LimitTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен определять количество строк.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::__construct
   */
  public function testShouldSetCountRows(){
    $l = new Limit(5);
    $this->assertEquals(5, $l->getCountRow());
  }

  /**
   * Может быть null.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::__construct
   */
  public function testMaybeNull(){
    new Limit();
  }

  /**
   * Должен быть целым числом большим нуля.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::__construct
   */
  public function testShouldBeInteger(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Limit(0);
    new Limit('5');
  }

  /**
   * Должен возвращать число выбираемых строк.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::getCountRow
   */
  public function testShouldReturnCountRows(){
    $l = new Limit(5);
    $this->assertEquals(5, $l->getCountRow());
    $l = new Limit();
    $this->assertEquals(null, $l->getCountRow());
  }

  /**
   * Должен возвращать SQL компонент согласно выбранной СУБД.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::interpretation
   */
  public function testShouldReturnString(){
    $l = new Limit(5);
    $this->assertEquals('TOP 5', $l->interpretation('sqlsrv'));
    $this->assertEquals('FIRST 5', $l->interpretation('firebird'));
    $this->assertEquals('ROWNUM <= 5', $l->interpretation('oci'));
    $this->assertEquals('LIMIT 5', $l->interpretation('mysql'));
    $this->assertEquals('LIMIT 5', $l->interpretation('pgsql'));
    $this->assertEquals('FETCH FIRST 5 ROWS ONLY', $l->interpretation('ibm'));
    $l = new Limit();
    $this->assertEquals('TOP ?', $l->interpretation('sqlsrv'));
    $this->assertEquals('FIRST ?', $l->interpretation('firebird'));
    $this->assertEquals('ROWNUM <= ?', $l->interpretation('oci'));
    $this->assertEquals('LIMIT ?', $l->interpretation('mysql'));
    $this->assertEquals('LIMIT ?', $l->interpretation('pgsql'));
    $this->assertEquals('FETCH FIRST ? ROWS ONLY', $l->interpretation('ibm'));
  }

  /**
   * Должен восстанавливаться из строки вида: LIMIT числоСтрок|?.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::reestablish
   */
  public function testShouldRestorableForString(){
    /**
     * @var Limit $l
     */
    $l = Limit::reestablish('LIMIT 10');
    $this->assertEquals(10, $l->getCountRow());
    /**
     * @var Limit $l
     */
    $l = Limit::reestablish('LIMIT ?');
    $this->assertEquals(null, $l->getCountRow());
  }

  /**
   * Допустимой строкой является строка вида: LIMIT числоСтрок.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::isReestablish
   */
  public function testGoodString(){
    $this->assertTrue(Limit::isReestablish('LIMIT 1'));
    $this->assertTrue(Limit::isReestablish('LIMIT 99'));
    $this->assertTrue(Limit::isReestablish('LIMIT 999'));
    $this->assertTrue(Limit::isReestablish('LIMIT ?'));
  }

  /**
   * Должен возвращать false при недопустимой структуре строки.
   * @covers D\library\patterns\entity\SQL\operators\DML\components\Limit::isReestablish
   */
  public function testBedString(){
    $this->assertFalse(Limit::isReestablish('1'));
    $this->assertFalse(Limit::isReestablish('LIMIT'));
    $this->assertFalse(Limit::isReestablish('LIMIT 0'));
    $this->assertFalse(Limit::isReestablish('LIMIT -1'));
    $this->assertFalse(Limit::isReestablish('LIMIT a'));
    $this->assertFalse(Limit::isReestablish('LIMIT 1a'));
    $this->assertFalse(Limit::isReestablish('LIMIT ??'));
  }
}
