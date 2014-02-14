<?php
namespace D\library\patterns\entity\io\test;

use D\library\patterns\entity\io\InStream;

class InStreamMock extends InStream{
  const LENGTH = 52;

  private $content = "First string \r\nВторая строка\r\nLast string";

  private $point = 0;

  function __construct($resource){
    $this->resource = $resource;
  }

  public function read(){
    if(!isset($this->content[$this->point])){
      return '';
    }
    else{
      return $this->content[$this->point++];
    }
  }

  public function setPoint($point){
    $this->point = $point;
  }
}
