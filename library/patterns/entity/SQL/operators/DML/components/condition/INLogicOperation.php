<?php
namespace D\library\patterns\entity\SQL\operators\DML\components\condition;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\Select;

/**
 * Логический оператор вхождения значения в указанное множество значений.
 * @author Artur Sh. Mamedbekov
 */
class INLogicOperation extends Condition{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Field Сравниваемое поле.
   */
  private $field;

  /**
   * @var mixed[] Допустимые значения (string|integer|float|boolean|null).
   */
  private $values;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\Select Select запрос, возвращающий искомые данные.
   */
  private $selectQuery;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['\(((?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')) IN \((((' . LogicOperation::getPatterns()['stringValue'] . ')|(\?))(, ?((' . LogicOperation::getPatterns()['stringValue'] . ')|(\?)))*)\)\)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $mask = parent::reestablish($string);
    $o = new self(Field::reestablish($mask[1]));
    $values = explode(',', $mask[2]);
    foreach($values as $value){
      $value = trim($value);
      if($value == '?'){
        $value = null;
      }
      else{
        $value = substr($value, 1, -1); // Удаление обрамляющих скалярные данные кавычек.
      }

      $o->addValue($value);
    }

    return $o;
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $field Проверяемое поле.
   */
  function __construct(Field $field){
    $this->field = $field;
    $this->values = [];
  }

  /**
   * Метод добавляет значение в список допустимых.
   * @param string|integer|float|boolean|null $value Добавляемое значение или null - если значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation Метод возвращает вызываемый объект.
   */
  public function addValue($value = null){
    InvalidArgumentException::verify($value, 'sifbn');
    if(is_bool($value)){
      $value = ($value)? 'true' : 'false';
    }
    $this->values[] = $value;

    return $this;
  }

  /**
   * Метод определяет SQL инструкцию, возвращающую список допустимых значений.
   * @param \D\library\patterns\entity\SQL\operators\DML\Select $selectQuery SQL инструкция, возвращающая список допустимых значений.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation Метод возвращает вызываемый объект.
   */
  public function setSelectQuery(Select $selectQuery){
    $this->selectQuery = $selectQuery;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);
    if(empty($this->selectQuery) && count($this->values) == 0){
      throw new NotFoundDataException('Для интерпретации объекта ['.get_class().'] необходимо определить хотя бы одно значение.');
    }
    if(empty($this->selectQuery)){
      $values = '';
      foreach($this->values as $val){
        if(is_null($val)){
          $values .= '?,';
        }
        else{
          $values .= '"'.$val.'",';
        }
      }
      $values = substr($values, 0, -1);
    }
    else{
      $values = $this->selectQuery->interpretation($driver);
    }

    return '(' . $this->field->interpretation($driver) . ' IN (' . $values . '))';
  }

  /**
   * Метод возвращает сравниваемое поле.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field Сравниваемое поле.
   */
  public function getField(){
    return $this->field;
  }

  /**
   * Метод возвращает Select запрос, определяющий искомые данные.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select|null Select запрос, определяющий искомые данные или null, если запрос не используется.
   */
  public function getSelectQuery(){
    return $this->selectQuery;
  }

  /**
   * Метод возвращает массив допустимых значений.
   * @return mixed[] Массив допустимых значений или пустой массив, если используется Select запрос.
   */
  public function getValues(){
    return $this->values;
  }
}
