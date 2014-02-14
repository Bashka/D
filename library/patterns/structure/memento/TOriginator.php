<?php
namespace D\library\patterns\structure\memento;

/**
 * Классическая реализация интерфейса Originator.
 * @author Artur Sh. Mamedbekov
 */
trait TOriginator{
  /**
   * Метод возвращает ассоциативный массив значений свойств, которые будут записаны в хранитель.
   * Необходимо помнить, что этот метод позволяет решить проблемы, связанные с наследованием, в частности передать хранителю только те значения свойств сохраняемого объекта, которые доступны из его области видимости.
   * В случае, если сохраняемый объект наследует свойства интерфейса Originator, то следует объединить возвращаемые этим методом родителя свойства с сохраняемыми свойствами вызываемого объекта путем конкатенации массивов.
   * @return mixed[]
   */
  protected abstract function getSavedState();

  /*
  // Метод следует реализовать, в использующем данный trait классе, следующим образом.
  protected function getSavedState(){
    $state = get_object_vars($this);
    // $state = $state + parent::getSavedState(); // Расскоментируйте в дочерних классах.
    return $state;
  }
  */
  /**
   * @prototype \D\library\patterns\structure\memento\Originator
   */
  public function createMemento(){
    assert(is_a($this, 'D\library\patterns\structure\memento\Originator'));
    $state = $this->getSavedState();
    assert('is_array($state)');
    return new Memento($this, $state);
  }

  /**
   * @prototype \D\library\patterns\structure\memento\Originator
   */
  public function restoreFromMemento(Memento $memento){
    $state = $memento->getState($this);
    assert('is_array($state)');
    $this->setSavedState($state);
  }

  /**
   * Метод позволяет восстановить состояние объекта на основании ассоциативного массива.
   * Необходимо понимать, что реализация данного метода в вершине иерархии классов делает private свойства дочерних классов недоступными для сохранения. Для решения этой проблемы, достаточно переопределить этот метод в дочернем классе передав полученный параметр в переопределенный метод родителя и реализовав его в переопределяющем метода.
   * @param mixed[] $state Ассоциативный массив, содержащий восстанавливаемое состояние объекта.
   */
  protected function setSavedState(array $state){
    foreach($state as $k => $v){
      if(property_exists(get_called_class(), $k)){
        $this->$k = $state[$k];
      }
    }
  }
  /*
  // Переопределите метод данным при необходимости доступа к private свойтсвам родительских объектов.
  protected function setSavedState(array $state){
    // parent::setSavedState($state); // Расскоментируйте в дочерних классах.

    foreach($state as $k => $v){
      if(property_exists($this, $k) && $this::getReflectionProperty($k)->getDeclaringClass()->getName() === get_class()){
        $this->$k = $state[$k];
      }
    }
  }
  */
}
