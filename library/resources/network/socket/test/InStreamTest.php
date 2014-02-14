<?php
namespace D\library\resources\network\socket\test;

use D\library\resources\network\socket\Socket;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class InStreamTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var Socket
   */
  private $client;

  /**
   * @var Socket
   */
  private $server;

  protected function setUp(){
    $this->server = new Socket;
    $this->server->listen('127.0.0.1', 1024);
    $this->client = new Socket;
  }

  protected function tearDown(){
    $this->server->shutdown();
  }


  /**
   * Должен закрывать поток ввода.
   * @covers D\library\resources\network\socket\InStream::close
   */
  public function testShouldCloseStream(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $this->assertFalse($cs->isClose());
    $this->assertFalse($ss->isClose());
    $cs->close();
    $ss->close();
    $this->assertTrue($cs->isClose());
    $this->assertTrue($ss->isClose());
  }

  /**
   * Должен возвращать true - если поток закрыт.
   * @covers D\library\resources\network\socket\InStream::isClose
   */
  public function testShouldReturnTrueIfStreamClosed(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $this->assertFalse($cs->isClose());
    $this->assertFalse($ss->isClose());
    $cs->close();
    $ss->close();
    $this->assertTrue($cs->isClose());
    $this->assertTrue($ss->isClose());
  }

  /**
   * Должен считывать один байт из потока.
   * @covers D\library\resources\network\socket\InStream::read
   */
  public function testShouldReturnCurrentByte(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $cs->write('ab');
    $this->assertEquals('a', $ss->read());
    $this->assertEquals('b', $ss->read());
    $ss->write('cd');
    $this->assertEquals('c', $cs->read());
    $this->assertEquals('d', $cs->read());
  }

  /**
   * Должен считывать указанное число байт из потока с ожиданием.
   * @covers D\library\resources\network\socket\InStream::readPackage
   */
  public function testShouldReturnPackage(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $cs->write('ab');
    $this->assertEquals('ab', $ss->readPackage(2));
    $ss->write('cd');
    $this->assertEquals('cd', $cs->readPackage(2));
  }

  /**
   * Должен устанавливать время ожидания.
   * @covers D\library\resources\network\socket\InStream::setReadTimeout
   */
  public function testShouldSetReadTime(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $cs->setReadTimeout(5);
    $this->assertEquals(5, $cs->getReadTimeout());
    $ss->setReadTimeout(5);
    $this->assertEquals(5, $ss->getReadTimeout());
  }

  /**
   * Должен возвращать время ожидания.
   * @covers D\library\resources\network\socket\InStream::getReadTimeout
   */
  public function testShouldReturnReadTime(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $cs->setReadTimeout(5);
    $this->assertEquals(5, $cs->getReadTimeout());
    $ss->setReadTimeout(5);
    $this->assertEquals(5, $ss->getReadTimeout());
  }
}
