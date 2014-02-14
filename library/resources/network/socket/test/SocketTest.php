<?php
namespace D\library\resources\network\socket\test;

use D\library\resources\network\socket\Socket;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class SocketTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен создавать сокетное соединение.
   * @covers D\library\resources\network\socket\Socket::createSocket
   * @covers D\library\resources\network\socket\Socket::__construct
   */
  public function testShouldCreate(){
    $s = new Socket;
    $r1 = $s->getResource();
    $this->assertEquals('resource', gettype($r1));
  }

  /**
   * Должен прослушивать указанный порт.
   * @covers D\library\resources\network\socket\Socket::listen
   */
  public function testShouldListenPort(){
    $sl = new Socket;
    $sl->listen('127.0.0.1', 1024);
    $sc = new Socket;
    $sc->connect('127.0.0.1', 1024);
    $stream = $sl->accept();
    $this->assertInstanceOf('D\library\resources\network\socket\Stream', $stream);
    $sl->shutdown();
  }

  /**
   * Должен возвращать соединение с клиенским сокетом.
   * @covers D\library\resources\network\socket\Socket::accept
   */
  public function testShouldReturnServerStream(){
    $sl = new Socket;
    $sl->listen('127.0.0.1', 1024);
    $sc = new Socket;
    $sc->connect('127.0.0.1', 1024);
    $stream = $sl->accept();
    $this->assertInstanceOf('D\library\resources\network\socket\Stream', $stream);
    $sl->shutdown();
  }

  /**
   * Должен возвращать соединение с серверным сокетом.
   * @covers D\library\resources\network\socket\Socket::connect
   */
  public function testShouldReturnClientStream(){
    $sl = new Socket;
    $sl->listen('127.0.0.1', 1024);
    $sc = new Socket;
    $stream = $sc->connect('127.0.0.1', 1024);
    $this->assertInstanceOf('D\library\resources\network\socket\Stream', $stream);
    $sl->shutdown();
  }

  /**
   * Должен отключать серверный сокет от прослушивания.
   * @covers D\library\resources\network\socket\Socket::shutdown
   */
  public function testShouldShutdownServerSocket(){
    $sl = new Socket;
    $sl->listen('127.0.0.1', 1024);
    $sl->shutdown();
  }
}
