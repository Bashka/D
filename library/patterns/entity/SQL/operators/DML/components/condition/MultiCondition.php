<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Множественное логическое выражение.
 * @author Artur Sh. Mamedbekov
 */
class MultiCondition extends Condition{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Левый логический операнд.
   */
  private $leftOperand;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Правый логический операнд.
   */
  private $rightOperand;

  /**
   * @var string Логический оператор. Одно из следующих значений: AND, OR.
   */
  private $logicOperator;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['\((' . Condition::getPatterns()['condition'] . ') ' . self::getPatterns()['operator'] . ' (' . Condition::getPatterns()['condition'] . ')\)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['operator' => '(AND|OR)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $mask = parent::reestablish($string);

    return new self(Condition::reestablishCondition($mask[1]), $mask[2], Condition::reestablishCondition($mask[3]));
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition $leftOperand Левый логический операнд.
   * @param string $logicOperator Логический оператор. Одно из следующих значений: AND, OR.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition $rightOperand Правый логический операнд.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   */
  function __construct(Condition $leftOperand, $logicOperator, Condition $rightOperand){
    if($logicOperator != 'AND' && $logicOperator != 'OR'){
      throw InvalidArgumentException::getValidException('AND|OR', $logicOperator);
    }
    $this->leftOperand = $leftOperand;
    $this->logicOperator = $logicOperator;
    $this->rightOperand = $rightOperand;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);

    return '(' . $this->leftOperand->interpretation($driver) . ' ' . $this->logicOperator . ' ' . $this->rightOperand->interpretation($driver) . ')';
  }

  /**
   * Метод возвращает левый операнд выражения.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Левый операнд выражения.
   */
  public function getLeftOperand(){
    return $this->leftOperand;
  }

  /**
   * Метод возвращает логический оператор.
   * @return string Логический оператор.
   */
  public function getLogicOperator(){
    return $this->logicOperator;
  }

  /**
   * Метод возвращает правый операнд выражения.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Правый операнд выражения
   */
  public function getRightOperand(){
    return $this->rightOperand;
  }
}