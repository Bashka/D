<?php
namespace D\library\patterns\structure\memento\test;

use D\library\patterns\structure\memento\Originator;
use D\library\patterns\structure\memento\TOriginator;

class OriginatorMock implements Originator{
  use TOriginator;

  private $testVar = 5;

  public function getTestVar(){
    return $this->testVar;
  }

  public function setTestVar($testVar){
    $this->testVar = $testVar;
  }

  protected function getSavedState(){
    return ['testVar' => $this->testVar];
  }
}
