<?php
namespace D\library\patterns\structure\observer\test;

class ObserverMock implements \SplObserver{
  public static $state = 0;

  public function update(\SplSubject $subject){
    self::$state++;
  }
}