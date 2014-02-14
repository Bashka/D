<?php
namespace D\library\patterns\entity\dataType\special\network\test;

use D\library\patterns\entity\dataType\special\network\Port;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class PortTest extends \PHPUnit_Framework_TestCase{
  /**
   * @covers \D\library\patterns\entity\dataType\special\network\Port::isReestablish
   */
  public function testIsReestablish(){
    $this->assertFalse(Port::isReestablish(''));
    $this->assertTrue(Port::isReestablish('0'));
    $this->assertTrue(Port::isReestablish('80'));
    $this->assertTrue(Port::isReestablish('65536'));
    $this->assertFalse(Port::isReestablish('-1'));
    $this->assertFalse(Port::isReestablish('65537'));
    $this->assertFalse(Port::isReestablish('a'));
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\network\Port::reestablish
   */
  public function testReestablish(){
    $o = Port::reestablish('80');
    $this->assertEquals(80, $o->getVal());
  }
}
