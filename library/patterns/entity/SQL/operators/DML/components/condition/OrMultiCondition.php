<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

/**
 * Множественное логическое выражение с OR логическим разделителем.
 * @author Artur Sh. Mamedbekov
 */
class OrMultiCondition extends QueryCondition{
  /**
   * Метод возвращает объединяющий логический оператор ИЛИ.
   * @return string Строка OR.
   */
  protected static function getOperator(){
    return 'OR';
  }
}
