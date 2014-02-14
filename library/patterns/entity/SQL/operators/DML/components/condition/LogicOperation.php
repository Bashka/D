<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

use D\library\patterns\entity\dataType\String;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\DML\components\Field;

/**
 * Логический оператор сравнения.
 * @author Artur Sh. Mamedbekov
 */
class LogicOperation extends Condition{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Field Сравниваемое поле.
   */
  private $field;

  /**
   * @var string Оператор сравнения. Одно из следующих значений: =, !=, >=, <=, >, <.
   */
  private $operator;

  /**
   * @var string|number|boolean|null|\D\library\patterns\entity\SQL\operators\DML\components\Field Правый операнд.
   */
  private $value;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['\((?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')) ' . self::getPatterns()['operator'] . ' ((' . self::getPatterns()['stringValue'] . ')|(\?))\)', '\((?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')) ' . self::getPatterns()['operator'] . ' (?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . '))\)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['operator' => '(?:=|!=|>=|<=|>|<)', 'stringValue' => '"[^"]*"'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $mask = parent::reestablish($string);
    $string = trim(substr($string, 1, -1)); // Исключение обрамляющих круглых скобок
    $string = new String($string);
    $lField = $string->getUpTo(' ')->getVal();
    assert('\D\library\patterns\entity\SQL\operators\DML\components\Field::isReestablish($lField)');
    $operator = trim($string->getUpTo(' ')->getVal());
    assert('$operator == "=" || $operator == "!=" || $operator ==  ">=" || $operator == "<=" || $operator == ">" || $operator == "<"');
    $value = trim($string->get()->getVal());
    $lField = Field::reestablish($lField);
    if($mask['key'] == 1){
      assert('\D\library\patterns\entity\SQL\operators\DML\components\Field::isReestablish($value)');
      $value = Field::reestablish($value);
    }
    elseif($mask['key'] == 0){
      if($value == '?'){
        $value = null;
      }
      else{
        $value = substr(substr($value, 1), 0, -1); // Удаление обрамляющих скалярные значения кавычек.
      }
    }

    return new self($lField, $operator, $value);
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $field Сравниваемое поле.
   * @param string $operator Оператор сравнения. Одно из следующих значений: =, !=, >=, <=, >, <.
   * @param string|number|boolean|float|\D\library\patterns\entity\SQL\operators\DML\components\Field $value [optional] Правый операнд или null - если значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct(Field $field, $operator, $value = null){
    try{
      InvalidArgumentException::verify($value, 'sifbn');
    }
    catch(InvalidArgumentException $e){
      if(!($value instanceof Field)){
        throw $e;
      }
    }

    if(array_search($operator, ['=', '!=', '>', '>=', '<', '<=']) === false){
      throw InvalidArgumentException::getValidException('=|>=|<=|!=|>|<', $operator);
    }
    $this->field = $field;
    $this->operator = $operator;
    $this->value = $value;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    if($this->value instanceof Field){
      $value = $this->value->interpretation($driver);
    }
    elseif(is_null($this->value)){
      $value = '?';
    }else{
      $value = '"' . (string) $this->value . '"';
    }

    return '(' . $this->field->interpretation($driver) . ' ' . $this->operator . ' ' . $value . ')';
  }

  /**
   * Метод возвращает сравниваемое поле.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field Сравниваемое поле.
   */
  public function getField(){
    return $this->field;
  }

  /**
   * Метод возвращает оператор сравнения.
   * @return string Оператор сравнения.
   */
  public function getOperator(){
    return $this->operator;
  }

  /**
   * Метод возвращает правый операнд.
   * @return string|number|boolean|null|\D\library\patterns\entity\SQL\operators\DML\components\Field Правый операнд или null - если значение параметризовано.
   */
  public function getValue(){
    return $this->value;
  }
}
