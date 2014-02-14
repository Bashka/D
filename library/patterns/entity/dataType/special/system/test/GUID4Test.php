<?php
namespace D\library\patterns\entity\dataType\special\system\test;

use D\library\patterns\entity\dataType\special\system\GUID4;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class GUID4Test extends \PHPUnit_Framework_TestCase {
  /**
   * @covers D\library\patterns\entity\dataType\special\system\GUID4::isReestablish
   */
  public function testIsReestablish(){
    $this->assertTrue(GUID4::isReestablish('a141aa94-3bec-4b68-b562-6b05fc2bfa48'));
    $this->assertFalse(GUID4::isReestablish(''));
    $this->assertFalse(GUID4::isReestablish('141aa94-3bec-4b68-b562-6b05fc2bfa48'));
    $this->assertFalse(GUID4::isReestablish('a141aa94-3bec-3b68-b562-6b05fc2bfa48'));
  }

  /**
   * @covers D\library\patterns\entity\dataType\special\system\GUID4::reestablish
   */
  public function testReestablish(){
    $o = GUID4::reestablish('a141aa94-3bec-4b68-b562-6b05fc2bfa48');
    $this->assertEquals('a141aa94-3bec-4b68-b562-6b05fc2bfa48', $o->getVal());
  }

  /**
   * Должен генрировать GUID v.4.
   * @covers D\library\patterns\entity\dataType\special\system\GUID4::generate
   */
  public function testShouldGenerateGUID(){
    $o = GUID4::generate();
    $this->assertInstanceOf('D\library\patterns\entity\dataType\special\system\GUID4', $o);
  }
}
 