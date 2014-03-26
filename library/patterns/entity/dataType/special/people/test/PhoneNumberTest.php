<?php
namespace D\library\patterns\entity\dataType\special\people\test;

use D\library\patterns\entity\dataType\special\people\PhoneNumber;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class PhoneNumberTest extends \PHPUnit_Framework_TestCase{
  /**
   * @covers \D\library\patterns\entity\dataType\special\people\PhoneNumber::isReestablish
   */
  public function testIsReestablish(){
    $this->assertTrue(PhoneNumber::isReestablish('+9(99999)999999999'));
    $this->assertTrue(PhoneNumber::isReestablish('+123(1)1'));
    $this->assertFalse(PhoneNumber::isReestablish(''));
    $this->assertFalse(PhoneNumber::isReestablish('+0(0)0'));
    $this->assertFalse(PhoneNumber::isReestablish('0(123)4567890'));
    $this->assertFalse(PhoneNumber::isReestablish('+0123456789'));
    $this->assertFalse(PhoneNumber::isReestablish('+0(123)'));
    $this->assertFalse(PhoneNumber::isReestablish('(123)456789'));
    $this->assertFalse(PhoneNumber::isReestablish('123'));
    $this->assertFalse(PhoneNumber::isReestablish('abc'));
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\people\PhoneNumber::reestablish
   * @covers \D\library\patterns\entity\dataType\special\people\PhoneNumber::getCode
   * @covers \D\library\patterns\entity\dataType\special\people\PhoneNumber::getNumber
   * @covers \D\library\patterns\entity\dataType\special\people\PhoneNumber::getRegion
   */
  public function testReestablish(){
    $o = PhoneNumber::reestablish('+1(234)5678901');
    $this->assertEquals('1', $o->getRegion());
    $this->assertEquals('234', $o->getCode());
    $this->assertEquals('5678901', $o->getNumber());
  }
}
