<?php
namespace D\library\patterns\structure\identification\test;

use D\library\patterns\structure\identification\TOID;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class OIDTest extends \PHPUnit_Framework_TestCase{
  /**
   * Для неидентифицированного объекта должен возвращать null.
   * @covers \D\library\patterns\structure\identification\TOID::getOID
   */
  public function testShouldReturnNullForNoIdentify(){
    $o = new OIDMock;
    $this->assertEquals(null, $o->getOID());
  }

  /**
   * Для идентифицированного объекта должен возвращать идентификатор объекта.
   * @covers \D\library\patterns\structure\identification\TOID::getOID
   */
  public function testShouldReturnOIDForIdentify(){
    $o = new OIDMock;
    $o->setOID('1');
    $this->assertEquals('1', $o->getOID());
  }

  /**
   * Не идентифицированному объекту должен присвоить идентификатор.
   * @covers \D\library\patterns\structure\identification\TOID::setOID
   */
  public function testShouldSetOIDForNoIdentify(){
    $o = new OIDMock;
    $o->setOID('1');
    $this->assertEquals('1', $o->getOID());
  }

  /**
   * В качестве идентификатора может выступать только не пустая строка.
   * @covers \D\library\patterns\structure\identification\TOID::setOID
   */
  public function testOIDShouldBePositiveInteger(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $o = new OIDMock;
    $o->setOID('');
  }

  /**
   * В качестве идентификатора не может выступать число.
   * @covers \D\library\patterns\structure\identification\TOID::setOID
   */
  public function testOIDCanNotBeString(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $o = new OIDMock;
    $o->setOID(1);
  }

  /**
   * Объект с идентификатором нельзя идентифицировать повторно.
   * @covers \D\library\patterns\structure\identification\TOID::setOID
   */
  public function testShouldThrowExceptionForIdentify(){
    $this->setExpectedException('\D\library\patterns\structure\identification\OIDException');
    $o = new OIDMock;
    $o->setOID('1');
    $o->setOID('1');
  }

  /**
   * Для идентифицированного объекта должен возвращать true.
   * @covers \D\library\patterns\structure\identification\TOID::isOID
   */
  public function testShouldReturnTrueForIdentify(){
    $o = new OIDMock;
    $o->setOID('1');
    $this->assertTrue($o->isOID());
  }

  /**
   * Для не идентифицированного объекта должен возвращать false.
   * @covers \D\library\patterns\structure\identification\TOID::isOID
   */
  public function testShouldReturnFalseForNoIdentify(){
    $o = new OIDMock;
    $this->assertFalse($o->isOID());
  }

  /**
   * Должен создавать объект вызываемого класса с установленным идентификатором.
   * @covers \D\library\patterns\structure\identification\TOID::getProxy
   */
  public function testShouldReturnProxy(){
    $o = OIDMock::getProxy('1');
    $this->assertEquals('1', $o->getOID());
  }
}
