<?php
namespace D\library\patterns\structure\memento;

/**
 * Класс позволяет сохранять состояние объекта в себе и возвращать его по требованию хозяина.
 * @author Artur Sh. Mamedbekov
 */
class Memento{
  /**
   * @var mixed[] Ассоциативный массив имен и значений хранимых свойств.
   */
  private $properties;

  /**
   * @var \D\library\patterns\structure\memento\Originator Хозяин хранителя.
   */
  private $originator;

  /**
   * @param \D\library\patterns\structure\memento\Originator $originator Хозяин хранителя.
   * @param mixed[] $savedProperties Ассоциативный массив, ключами которого являются имена свойств хозяина, а значениями их значения.
   */
  function __construct(Originator $originator, array $savedProperties){
    $this->originator = $originator;
    $this->properties = $savedProperties;
  }

  /**
   * Метод возвращает хранимые значения свойств хранителю.
   * @param \D\library\patterns\structure\memento\Originator $originator Хозяин хранителя. Метод вернет значения полей только если в данном аргументе передан истинный хозяин хранителя.
   * @throws \D\library\patterns\structure\memento\AccessException Выбрасывается в случае, если в качестве хозяина передан не истинный хозяин хранителя.
   * @return mixed[] Ассоциативный массив значений полей хозяина.
   */
  public final function getState(Originator $originator){
    if($this->originator !== $originator){
      throw new AccessException('Доступ к хранимому состоянию запрещен.');
    }

    return $this->properties;
  }
}
