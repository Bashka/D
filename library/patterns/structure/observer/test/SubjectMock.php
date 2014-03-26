<?php
namespace D\library\patterns\structure\observer\test;

use D\library\patterns\structure\observer\TSubject;

class SubjectMock implements \SplSubject{
  use TSubject;

  /**
   * @return \SplObjectStorage
   */
  public function getObservers(){
    return $this->observers;
  }
}