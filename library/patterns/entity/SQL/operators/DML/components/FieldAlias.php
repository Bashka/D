<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Класс позволяет добавить псевдоним полю.
 * @author Artur Sh. Mamedbekov
 */
class FieldAlias extends Alias{
  /**
   * Данный метод восстанавливает компонент.
   * @param string $string Исходная строка компонента.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику интерпретации исходной строки.
   * @return \D\library\patterns\entity\SQL\operators\ComponentQuery Восстановленный компонент.
   */
  protected static function reestablishComponent($string, $driver = null){
    return Field::reestablish($string);
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['(?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')) AS ' . Alias::getPatterns()['aliasValue']];
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $component Поле, к которому добавляется псевдоним. Ожидается объект класса Field.
   * @param string $alias Псевдоним поля.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct(ComponentQuery $component, $alias){
    // Логическая проверка осуществляется в связи с поддержкой семантики родительского класса
    if(!($component instanceof Field)){
      throw InvalidArgumentException::getTypeException('Field', gettype($component));
    }
    parent::__construct($component, $alias);
  }
}
