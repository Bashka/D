<?php
namespace D\library\patterns\structure\singleton\test;

use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;

class SingletonMock implements Singleton{
  use TSingleton;

  protected $var = 1;

  public function setVar($var){
    $this->var = $var;
  }

  public function getVar(){
    return $this->var;
  }
}
