<?php
namespace D\library\resources\storage\cache\test;

use D\library\resources\storage\cache\CacheAdapter;

class CacheDriverMock extends CacheAdapter{
  private $storage = [];

  public function set($key, $value, $time = null){
    $this->storage[$key] = $value;
  }

  public function get($key){
    if(!isset($this->storage[$key])){
      return null;
    }
    return $this->storage[$key];
  }

  public function remove($key){
    if(isset($this->storage[$key])){
      unset($this->storage[$key]);
    }
  }

  public function connect($host, $port = null){

  }
}