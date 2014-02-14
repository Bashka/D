<?php
namespace D\library\resources\storage\cache\drivers\test;

use D\library\resources\storage\cache\drivers\MemcacheAdapter;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class MemcacheAdapterTest extends \PHPUnit_Framework_TestCase {
  /**
   * Адрес службы.
   */
  const SERVER = '127.0.0.1';

  /**
   * Порт службы.
   */
  const PORT = '11211';

  /**
   * Должен выполнять подключение к демону.
   * @covers D\library\resources\storage\cache\drivers\MemcacheAdapter::connect
   */
  public function testShouldConnect(){
    $m = new MemcacheAdapter();
    $m->connect(self::SERVER, self::PORT);
  }

  /**
   * В случае ошибки при подключении должен выбрасывать исключение.
   * @covers D\library\resources\storage\cache\drivers\MemcacheAdapter::connect
   */
  public function testShouldThrowExceptionIfErrorConnect(){
    $this->setExpectedException('D\library\resources\storage\cache\CacheException');
    $m = new MemcacheAdapter();
    $m->connect(self::SERVER, 0);
  }

  /**
   * Должен устанавливать значение ключу.
   * @covers D\library\resources\storage\cache\drivers\MemcacheAdapter::set
   */
  public function testShouldSetValue(){
    $m = new MemcacheAdapter();
    $m->connect(self::SERVER, self::PORT);
    $m->set('MemcacheAdapterTest::test', 'value');
    $this->assertEquals('value', $m->get('MemcacheAdapterTest::test'));
    $m->remove('MemcacheAdapterTest::test');
  }

  /**
   * Должен возвращать значение ключа.
   * @covers D\library\resources\storage\cache\drivers\MemcacheAdapter::get
   */
  public function testShouldGetValue(){
    $m = new MemcacheAdapter();
    $m->connect(self::SERVER, self::PORT);
    $m->set('MemcacheAdapterTest::test', 'value');
    $this->assertEquals('value', $m->get('MemcacheAdapterTest::test'));
    $m->remove('MemcacheAdapterTest::test');
  }

  /**
   * Должен возвращать null, если значение ключа не установлено.
   * @covers D\library\resources\storage\cache\drivers\MemcacheAdapter::get
   */
  public function testShouldReturnNullIfValueNotExists(){
    $m = new MemcacheAdapter();
    $m->connect(self::SERVER, self::PORT);
    $this->assertEquals(null, $m->get('MemcacheAdapterTest::test'));
  }

  /**
   * Должен удалять ключ.
   * @covers D\library\resources\storage\cache\drivers\MemcacheAdapter::remove
   */
  public function testShouldRemoveValue(){
    $m = new MemcacheAdapter();
    $m->connect(self::SERVER, self::PORT);
    $m->set('MemcacheAdapterTest::test', 'value');
    $m->remove('MemcacheAdapterTest::test');
    $this->assertEquals(null, $m->get('MemcacheAdapterTest::test'));
  }
}
 