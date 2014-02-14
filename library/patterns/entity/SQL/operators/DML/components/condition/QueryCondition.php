<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет логическое выражение.
 * @author Artur Sh. Mamedbekov
 */
abstract class QueryCondition extends Condition{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition[] Множество условий, входящих в логическое выражение.
   */
  protected $conditions;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['\(' . Condition::getPatterns()['condition'] . ' ' . static::getPatterns()['moreCondition'] . '(?: ' . static::getPatterns()['moreCondition'] . ')*\)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['moreCondition' => static::getOperator() . ' ' . Condition::getPatterns()['condition']];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    /**
     * @var QueryCondition $o
     */
    $o = new static();
    $conditions = explode(static::getOperator(), substr($string, 1, -1));
    foreach($conditions as $condition){
      $o->addCondition(Condition::reestablishCondition(trim($condition)));
    }

    return $o;
  }

  /**
   * Метод возвращает объединяющий логический оператор.
   * @return string Логический оператор.
   */
  protected static function getOperator(){
    return '';
  }

  function __construct(){
    $this->conditions = [];
  }

  /**
   * Метод добавляет логическую операцию в выражение.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition $condition Логический оператор.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\QueryCondition Метод возвращает вызываемый объект.
   */
  public function addCondition(Condition $condition){
    $this->conditions[] = $condition;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);
    if(count($this->conditions) < 2){
      throw new NotFoundDataException('Недостаточное число условий в выражении.');
    }
    $operator = static::getOperator();
    $conditions = [];
    foreach($this->conditions as $condition){
      $conditions[] = $condition->interpretation($driver);
    }

    return '(' . implode(' ' . $operator . ' ', $conditions) . ')';
  }

  /**
   * Метод возвращает входящие в выражения члены.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition[] Массив члеов, входящих в логическое выражение.
   */
  public function getConditions(){
    return $this->conditions;
  }
}
