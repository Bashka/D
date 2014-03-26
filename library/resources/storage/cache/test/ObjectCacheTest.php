<?php
namespace D\library\resources\storage\cache\test;

use D\library\resources\storage\cache\CacheAdapter;
use D\library\resources\storage\cache\drivers\NullAdapter;
use D\library\resources\storage\cache\ObjectCache;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class ObjectCacheTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var CacheAdapter
   */
  private static $cache;

  /**
   * @var ObjectCache
   */
  private static $synch;

  public static function setUpBeforeClass(){
    parent::setUpBeforeClass();
    self::$cache = new CacheDriverMock();
    self::$synch = new ObjectCache(self::$cache);
  }

  /**
   * Должен добавлять объект в кэш.
   * @covers \D\library\resources\storage\cache\ObjectCache::add
   */
  public function testShouldAddObjectInCache(){
    self::$synch->add('TestClass', '1', ['a' => 1, 'b' => 2]);
    $this->assertEquals(['a' => 1, 'b' => 2], self::$cache->get('ObjectCache::Pile::TestClass::1'));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * Должен добавлять объект в индекс.
   * @covers \D\library\resources\storage\cache\ObjectCache::add
   */
  public function testShouldAddObjectInIndex(){
    self::$synch->add('TestClass', '1', ['a' => 1, 'b' => 2]);
    $this->assertEquals([0 => '1'], self::$cache->get('ObjectCache::Classes::TestClass'));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * При повторном добавлении не должен увеличивать кэш.
   * @covers \D\library\resources\storage\cache\ObjectCache::add
   */
  public function testShouldSilentIfDuplicate(){
    self::$synch->add('TestClass', '1', ['a' => 1, 'b' => 2]);
    $this->assertEquals([0 => '1'], self::$cache->get('ObjectCache::Classes::TestClass'));
    self::$synch->add('TestClass', '1', ['a' => 1, 'b' => 2]);
    $this->assertEquals([0 => '1'], self::$cache->get('ObjectCache::Classes::TestClass'));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * Должен возвращать объект из кэша.
   * @covers \D\library\resources\storage\cache\ObjectCache::get
   */
  public function testShouldReturnObject(){
    self::$cache->set('ObjectCache::Pile::TestClass::1', ['a' => 1, 'b' => 2]);
    $this->assertEquals(['a' => 1, 'b' => 2], self::$synch->get('TestClass', '1'));
    self::$cache->set('ObjectCache::Classes::TestClass', [0 => '1']);
    $this->assertEquals(['a' => 1, 'b' => 2], self::$synch->get('TestClass', '1'));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * Должен удалять объект из кэша.
   * @covers \D\library\resources\storage\cache\ObjectCache::remove
   */
  public function testShouldRemoveObject(){
    self::$cache->set('ObjectCache::Pile::TestClass:1', ['a' => 1, 'b' => 2]);
    self::$cache->set('ObjectCache::Classes::TestClass', [0 => '1']);
    self::$synch->remove('TestClass', '1');
    $this->assertEquals(null, self::$cache->get('ObjectCache::Pile::TestClass::1'));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * Должен удалять объект из индекса.
   * @covers \D\library\resources\storage\cache\ObjectCache::remove
   */
  public function testShouldRemoveIndexObject(){
    self::$cache->set('ObjectCache::Pile::TestClass::1', ['a' => 1, 'b' => 2]);
    self::$cache->set('ObjectCache::Classes::TestClass', [0 => '1']);
    self::$synch->remove('TestClass', '1');
    $this->assertEquals([], self::$cache->get('ObjectCache::Classes::TestClass'));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * Должен выполнять поиск объекта по критериям.
   * @covers \D\library\resources\storage\cache\ObjectCache::find
   */
  public function testShouldFindObject(){
    self::$cache->set('ObjectCache::Pile::TestClass::1', ['a' => 1, 'b' => 2]);
    self::$cache->set('ObjectCache::Classes::TestClass', [0 => '1']);
    $this->assertEquals([1 => ['a' => 1, 'b' => 2]], self::$synch->find('TestClass', [['a', '=', 1]]));
    self::$cache->set('ObjectCache::Pile::TestClass::2', ['a' => 2, 'b' => 2]);
    self::$cache->set('ObjectCache::Classes::TestClass', [0 => '1', 1 => '2']);
    $this->assertEquals([1 => ['a' => 1, 'b' => 2]], self::$synch->find('TestClass', [['a', '=', 1]]));
    self::$cache->set('ObjectCache::Pile::TestClass::3', ['a' => 1, 'b' => 2]);
    self::$cache->set('ObjectCache::Classes::TestClass', [0 => '1', 1 => '2', 2 => '3']);
    $this->assertEquals([1 => ['a' => 1, 'b' => 2], 3 => ['a' => 1, 'b' => 2]], self::$synch->find('TestClass', [['a', '=', 1]]));
    self::$cache->remove('ObjectCache::Pile::TestClass::1');
    self::$cache->remove('ObjectCache::Classes::TestClass');
  }

  /**
   * Должен работать с NullAdapter.
   * @covers \D\library\resources\storage\cache\ObjectCache::add
   * @covers \D\library\resources\storage\cache\ObjectCache::get
   * @covers \D\library\resources\storage\cache\ObjectCache::remove
   * @covers \D\library\resources\storage\cache\ObjectCache::find
   */
  public function testShouldUseNullAdapter(){
    $cache = new ObjectCache(new NullAdapter());
    $cache->add('TestClass', '1', ['a' => 1, 'b' => 2]);
    $this->assertEquals(null, $cache->get('TestClass', '1'));
    $cache->remove('TestClass', '1');
    $this->assertEquals([], $cache->find('TestClass', [['a', '=', 1]]));
  }
}
