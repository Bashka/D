<?php
namespace D\library\patterns\entity\dataType\special\network\test;

use D\library\patterns\entity\dataType\special\network\EMail;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class EMailTest extends \PHPUnit_Framework_TestCase{
  /**
   * @covers \D\library\patterns\entity\dataType\special\network\EMail::isReestablish
   */
  public function testIsReestablish(){
    $this->assertFalse(EMail::isReestablish(''));
    $this->assertTrue(EMail::isReestablish('a@b.c'));
    $this->assertTrue(EMail::isReestablish('1_-a@email.com'));
    $this->assertTrue(EMail::isReestablish('Login@email.com'));
    $this->assertFalse(EMail::isReestablish('#@email.com'));
    $this->assertFalse(EMail::isReestablish('Loginemail.com'));
    $this->assertFalse(EMail::isReestablish('Login@'));
    $this->assertFalse(EMail::isReestablish('@email.com'));
    $this->assertFalse(EMail::isReestablish('Login@1'));
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\network\EMail::reestablish
   * @covers \D\library\patterns\entity\dataType\special\network\EMail::getDomain
   * @covers \D\library\patterns\entity\dataType\special\network\EMail::getLocal
   */
  public function testReestablish(){
    $o = EMail::reestablish('Login@email.com');
    $this->assertEquals('Login', $o->getLocal());
    $this->assertEquals('com', $o->getDomain()->getComponent(0));
  }
}
