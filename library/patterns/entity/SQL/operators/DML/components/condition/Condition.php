<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

use D\library\patterns\entity\exceptions\dataExceptions\StructureDataException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Дочерние классы данного класса являются представлениями логических конструкций.
 * @author Artur Sh. Mamedbekov
 */
abstract class Condition extends ComponentQuery{
  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['condition' => ' ?\(.+\) ?'];
  }

  /**
   * Метод выполняет восстановление дочерних классов данного класса из строки.
   * Метод автоматически определяет класс восстанавливаемого объекта на основании анализа структуры исходной строки.
   * @param string $string Исходная строка.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику интерпретации исходной строки.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\StructureDataException Выбрасывается в случае, если исходная строка не отвечает требования структуры.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Результирующий объект.
   */
  public static function reestablishCondition($string, $driver = null){
    // Верификация параметров выполняется в вызываемом методе isReestablish.
    if(LogicOperation::isReestablish($string)){
      return LogicOperation::reestablish($string);
    }
    elseif(MultiCondition::isReestablish($string)){
      return MultiCondition::reestablish($string);
    }
    elseif(INLogicOperation::isReestablish($string)){
      return INLogicOperation::reestablish($string);
    }
    elseif(AndMultiCondition::isReestablish($string)){
      return AndMultiCondition::reestablish($string);
    }
    elseif(OrMultiCondition::isReestablish($string)){
      return OrMultiCondition::reestablish($string);
    }
    else{
      throw new StructureDataException('Недопустимая структура исходной строки ['.$string.']. Невозможно определить условие.');
    }
  }
}
