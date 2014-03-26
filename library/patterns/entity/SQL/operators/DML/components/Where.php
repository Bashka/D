<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;
use D\library\patterns\entity\SQL\operators\DML\components\condition\Condition;

/**
 * Класс представляет условие в SQL запросе.
 * @author Artur Sh. Mamedbekov
 */
class Where extends ComponentQuery{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Логическая операция.
   */
  private $condition;

  /**
   * @prototype \D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['WHERE (?:' . Condition::getPatterns()['condition'] . ')'];
  }

  /**
   * @prototype \D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);

    return new self(Condition::reestablishCondition(substr($string, 6)));
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition $condition Логическая операция.
   */
  function __construct(Condition $condition){
    $this->condition = $condition;
  }

  /**
   * @prototype \D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);

    return 'WHERE ' . $this->condition->interpretation($driver);
  }

  /**
   * Метод возвращает логическое выражение.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Логическое выражение.
   */
  public function getCondition(){
    return $this->condition;
  }
}
