<?php
namespace D\library\patterns\entity\dataType\special\system\test;

use D\library\patterns\entity\dataType\special\system\Name;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class NameTest extends \PHPUnit_Framework_TestCase {
  /**
   * @covers D\library\patterns\entity\dataType\special\system\Name::isReestablish
   */
  public function testIsReestablish(){
    $this->assertTrue(Name::isReestablish('Te_st123'));
    $this->assertTrue(Name::isReestablish('_ab'));
    $this->assertFalse(Name::isReestablish(''));
    $this->assertFalse(Name::isReestablish('0a'));
    $this->assertFalse(Name::isReestablish('ab*'));
  }

  /**
   * @covers D\library\patterns\entity\dataType\special\system\Name::reestablish
   */
  public function testReestablish(){
    $o = Name::reestablish('Te_st123');
    $this->assertEquals('Te_st123', $o->getVal());
  }
}
 