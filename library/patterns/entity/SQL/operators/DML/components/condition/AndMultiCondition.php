<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

/**
 * Множественное логическое выражение с AND логическим разделителем.
 * @author Artur Sh. Mamedbekov
 */
class AndMultiCondition extends QueryCondition{
  /**
   * Метод возвращает объединяющий логический оператор И.
   * @return string Строка AND.
   */
  protected static function getOperator(){
    return 'AND';
  }
}
