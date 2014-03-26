<?php
namespace D\library\patterns\structure\memento\test;

use D\library\patterns\structure\memento\Memento;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class MementoTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var Memento
   */
  protected $memento;

  /**
   * @var OriginatorMock
   */
  protected $originator;

  protected function setUp(){
    $this->originator = new OriginatorMock();
    $this->memento = $this->originator->createMemento();
  }

  /**
   * @covers D\library\patterns\structure\memento\TOriginator::createMemento
   * @covers D\library\patterns\structure\memento\Memento::__construct
   */
  public function testCreateMemento(){
    $this->assertInstanceOf('D\library\patterns\structure\memento\Memento', $this->originator->createMemento());
  }

  /**
   * @covers D\library\patterns\structure\memento\TOriginator::restoreFromMemento
   */
  public function testRestoreFromMemento(){
    $this->originator->setTestVar(3);
    $this->originator->restoreFromMemento($this->memento);
    $this->assertEquals(5, $this->originator->getTestVar());
  }

  /**
   * @covers D\library\patterns\structure\memento\Memento::getState
   */
  public function testGetState(){
    $this->assertEquals(['testVar' => 5], $this->memento->getState($this->originator));
  }

  /**
   * @covers D\library\patterns\structure\memento\Memento::getState
   */
  public function testGetStateIfNonOwner(){
    $this->setExpectedException('D\library\patterns\structure\memento\AccessException');
    $this->memento->getState(new OriginatorMock());
  }
}
