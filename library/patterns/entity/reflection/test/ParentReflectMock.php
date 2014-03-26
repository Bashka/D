<?php
namespace D\library\patterns\entity\reflection\test;

use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\entity\reflection\TReflect;

class ParentReflectMock implements Reflect{
  use TReflect;

  private $a;

  protected $b;

  private function c($x){
  }

  protected function d(){
  }
}