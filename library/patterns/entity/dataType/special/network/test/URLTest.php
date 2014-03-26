<?php
namespace D\library\patterns\entity\dataType\special\network\test;

use D\library\patterns\entity\dataType\special\network\URL;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class URLTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var \D\library\patterns\entity\dataType\special\network\URL
   */
  protected $object;

  protected function setUp(){
    $this->object = new URL('http://test.com:8080/testDir/testFile.txt');
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\network\URL::isReestablish
   */
  public function testIsReestablish(){
    $this->assertFalse(URL::isReestablish(''));
    $this->assertTrue(URL::isReestablish('http://test'));
    $this->assertTrue(URL::isReestablish('http://test.com'));
    $this->assertTrue(URL::isReestablish('http://test.com:8080'));
    $this->assertTrue(URL::isReestablish('http://test.com/test/text.txt'));
    $this->assertTrue(URL::isReestablish('http://test.com:8080/test/text.txt'));
    $this->assertFalse(URL::isReestablish('http://test.com//test/text.txt'));
    $this->assertFalse(URL::isReestablish('test://test.com:8080/test/text.txt'));
    $this->assertFalse(URL::isReestablish('http://test.com:8080//test/text.txt'));
    $this->assertFalse(URL::isReestablish('http://a:8080/test/text.txt'));
    $this->assertFalse(URL::isReestablish('http://a:/test/text.txt'));
    $this->assertTrue(URL::isReestablish('http://test.com:8080/test/'));
    $this->assertTrue(URL::isReestablish('http://test.com:8080/'));
    $this->assertTrue(URL::isReestablish('http://192.168.1.1'));
    $this->assertTrue(URL::isReestablish('http://192.168.1.1:8080/test/text.txt'));
  }

  /**
   * @covers \D\library\patterns\entity\dataType\special\network\URL::reestablish
   * @covers \D\library\patterns\entity\dataType\special\network\URL::getReport
   * @covers \D\library\patterns\entity\dataType\special\network\URL::getAddress
   * @covers \D\library\patterns\entity\dataType\special\network\URL::getPort
   * @covers \D\library\patterns\entity\dataType\special\network\URL::getFileSystemAddress
   */
  public function testReestablish(){
    $o = URL::reestablish('http://test.com:8080/test/text.txt');
    $this->assertEquals('http', $o->getReport()->getName());
    $this->assertEquals('com', $o->getAddress()->getComponent(0));
    $this->assertEquals('8080', $o->getPort()->getVal());
    $this->assertEquals('/test/text.txt', $o->getFileSystemAddress()->getVal());
    $this->assertTrue($o->getFileSystemAddress()->isRoot());
    $o = URL::reestablish('http://192.168.1.1:8080/test/text.txt');
    $this->assertEquals('168', $o->getAddress()->getTrio(1));
  }
}
