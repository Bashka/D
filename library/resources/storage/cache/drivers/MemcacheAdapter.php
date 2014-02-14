<?php
namespace D\library\resources\storage\cache\drivers;

use D\library\resources\storage\cache\CacheAdapter;
use D\library\resources\storage\cache\CacheException;

/**
 * Адаптер, для взаимодействия с memcache.
 * @author Artur Sh. Mamedbekov
 */
class MemcacheAdapter extends CacheAdapter{
  /**
   * @var \Memcache Не адаптированный объект взаимодействия с кэш-системой.
   */
  private $cache;

  function __construct(){
    $this->cache = new \Memcache;
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function set($key, $value, $time = null){
    $this->cache->set($key, $value, $time);
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function get($key){
    $result = $this->cache->get($key);
    if($result === false){
      return null;
    }

    return $result;
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function connect($host, $port = null){
    $result = @$this->cache->pconnect($host, $port); // Подавление оповещений для выброса исключения.
    if(!$result){
      throw new CacheException('Невозможно выполнить подключение к системе кэширования [Memcache]. Возможно служба не запущена по указанному адерсу ['.$host.':'.$port.'].');
    }
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function remove($key){
    $this->cache->delete($key);
  }
}
