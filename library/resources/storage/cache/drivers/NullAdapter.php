<?php
namespace D\library\resources\storage\cache\drivers;

use D\library\resources\storage\cache\CacheAdapter;

/**
 * Объект-пустышка, применяемый в случае отсутствия кэш-системы.
 * @author Artur Sh. Mamedbekov
 */
class NullAdapter extends CacheAdapter{
  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function set($key, $value, $time = null){
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function get($key){
    return null;
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function connect($host, $port = null){
  }

  /**
   * @prototype D\library\resources\storage\cache\CacheAdapter
   */
  public function remove($key){
  }
}
