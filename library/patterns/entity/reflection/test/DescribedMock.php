<?php
namespace D\library\patterns\entity\reflection\test;

use D\library\patterns\entity\reflection\TDocMetadata;

class DescribedMock {
  use TDocMetadata;

  public static $doc;

  public function getDocComment(){
    return self::$doc;
  }

  public function method($arg){

  }
}