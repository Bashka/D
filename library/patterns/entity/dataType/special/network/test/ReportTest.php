<?php
namespace D\library\patterns\entity\dataType\special\network\test;

use D\library\patterns\entity\dataType\special\network\Report;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class ReportTest extends \PHPUnit_Framework_TestCase{
  /**
   * @covers \D\library\patterns\entity\dataType\special\network\Report::isReestablish
   */
  public function testIsReestablish(){
    $this->assertFalse(Report::isReestablish(''));
    $this->assertTrue(Report::isReestablish('http://'));
    $this->assertTrue(Report::isReestablish('ftp://'));
    $this->assertFalse(Report::isReestablish('http:/'));
    $this->assertFalse(Report::isReestablish('http//'));
    $this->assertFalse(Report::isReestablish('http'));
    $this->assertFalse(Report::isReestablish('://'));
    $this->assertFalse(Report::isReestablish('http:'));
    $this->assertFalse(Report::isReestablish('test://'));
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\network\Report::reestablish
   * @covers \D\library\patterns\entity\dataType\special\network\Report::getName
   */
  public function testReestablish(){
    $o = Report::reestablish('http://');
    $this->assertEquals('http', $o->getName());
  }
}
