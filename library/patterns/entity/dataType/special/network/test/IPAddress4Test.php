<?php
namespace D\library\patterns\entity\dataType\special\network\test;

use D\library\patterns\entity\dataType\special\network\IPAddress4;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class IPAddress4Test extends \PHPUnit_Framework_TestCase{
  /**
   * @covers \D\library\patterns\entity\dataType\special\network\IPAddress4::isReestablish
   */
  public function testIsReestablish(){
    $this->assertFalse(IPAddress4::isReestablish(''));
    $this->assertTrue(IPAddress4::isReestablish('0.0.0.0'));
    $this->assertTrue(IPAddress4::isReestablish('127.0.0.1'));
    $this->assertTrue(IPAddress4::isReestablish('255.255.255.255'));
    $this->assertFalse(IPAddress4::isReestablish('-1.0.0.0'));
    $this->assertFalse(IPAddress4::isReestablish('256.0.0.0'));
    $this->assertFalse(IPAddress4::isReestablish('0.0.0.'));
    $this->assertFalse(IPAddress4::isReestablish('0.0.0'));
    $this->assertFalse(IPAddress4::isReestablish('0.0.0.0.'));
    $this->assertFalse(IPAddress4::isReestablish('0.0.0.0.0'));
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\network\IPAddress4::reestablish
   * @covers \D\library\patterns\entity\dataType\special\network\IPAddress4::getTrio
   * @covers \D\library\patterns\entity\dataType\special\network\IPAddress4::getTrioBin
   */
  public function testReestablish(){
    $o = IPAddress4::reestablish('127.0.0.1');
    $this->assertEquals(127, $o->getTrio(0));
    $this->assertEquals(0, $o->getTrio(1));
    $this->assertEquals(0, $o->getTrio(2));
    $this->assertEquals(1, $o->getTrio(3));
    $this->assertEquals('1111111', $o->getTrioBin(0));
  }
}
