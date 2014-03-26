<?php
namespace D\services\cache\test;

use D\services\cache\Cache;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class CacheTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать выбранный драйвер кэша.
   * @covers D\library\resources\storage\cache\Cache::getInstance
   */
  public function testShouldReturnDriver(){
    $this->assertInstanceOf('\D\library\resources\storage\cache\drivers\\' . Cache::DRIVER, Cache::getInstance());
  }

  /**
   * Должен реализовывать логику Singleton.
   * @covers D\library\resources\storage\cache\Cache::getInstance
   */
  public function testShouldBeSingleton(){
    $d = Cache::getInstance();
    $this->assertEquals($d, Cache::getInstance());
  }
}
