<?php
namespace D\library\patterns\entity\cache\test;

use D\library\patterns\entity\cache\Cache;

class CacheMock extends Cache{
  public static $data = ['key' => 1];

  protected function getFromSource($key, array $arguments = null){
    if(!array_key_exists($key, self::$data)){
      return null;
    }

    return self::$data[$key];
  }
}
