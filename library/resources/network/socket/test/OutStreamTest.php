<?php
namespace D\library\resources\network\socket\test;

use D\library\resources\network\socket\Socket;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class OutStreamTest extends \PHPUnit_Framework_TestCase {
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
   * @covers D\library\resources\network\socket\OutStream::close
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
   * @covers D\library\resources\network\socket\OutStream::isClose
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
   * Должен записывать данные в поток.
   * @covers D\library\resources\network\socket\InStream::write
   */
  public function testShouldWriteDataInStream(){
    $cs = $this->client->connect('127.0.0.1', 1024);
    $ss = $this->server->accept();
    $cs->write('ab');
    $this->assertEquals('a', $ss->read());
    $this->assertEquals('b', $ss->read());
    $ss->write('cd');
    $this->assertEquals('c', $cs->read());
    $this->assertEquals('d', $cs->read());
  }
}
