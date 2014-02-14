<?php
namespace D\library\patterns\structure\singleton\test;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class SingletonTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен возвращать экземпляр класса.
   * @covers \D\library\patterns\structure\singleton\TSingleton::getInstance
   */
  public function testShouldReturnObjectClass(){
    $this->assertInstanceOf('\D\library\patterns\structure\singleton\test\SingletonMock', SingletonMock::getInstance());
  }

  /**
   * Повторный вызов должен возвращать экземпляр? созданный при первом вызове.
   * @covers \D\library\patterns\structure\singleton\TSingleton::getInstance
   */
  public function testShouldReturnFirstObject(){
    $o = SingletonMock::getInstance();
    $this->assertEquals($o, SingletonMock::getInstance());
    $o = ChildSingletonMock::getInstance();
    $this->assertEquals($o, ChildSingletonMock::getInstance());
  }

  /**
   * Должен возвращать различные экземпляры для различных классов в одной иерархии наследования.
   * @covers \D\library\patterns\structure\singleton\TSingleton::getInstance
   */
  public function testShouldReturnChildrenObjects(){
    $po = SingletonMock::getInstance();
    $co = ChildSingletonMock::getInstance();
    $this->assertTrue($po !== $co);
  }

  /**
   * Должен выбрасывать исключение.
   * @covers \D\library\patterns\structure\singleton\TSingleton::__clone
   */
  public function testShouldThrowException(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\LackException');
    $instance = SingletonMock::getInstance();
    $instance = clone $instance;
  }
}
