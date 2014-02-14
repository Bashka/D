<?php
namespace D\library\patterns\entity\SQL\operators\DML;

use D\library\patterns\entity\SQL\operators\ComponentQuery;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\Operator;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет объектную SQL инструкцию для обновления данных в БД.
 * Объекты данного класса могут быть восстановлены из строки следующего формата: UPDATE имяТаблицы SET имяПоля|имяТаблицы.имяПоля = "значение", ... [WHERE (логическоеВыражение)].
 * @author Artur Sh. Mamedbekov
 */
class Update extends ComponentQuery implements Operator{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Table Целевая таблица.
   */
  private $table;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Field[] Множество полей, используемых в таблице.
   */
  private $fields;

  /**
   * @var mixed[] Множество значений, устанавливаемых в поля записи.
   */
  private $values;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Where Условие отбора.
   */
  private $where;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['UPDATE (' . Table::getPatterns()['tableName'] . ') SET (' . self::getPatterns()['setValue'] . '(, ?' . self::getPatterns()['setValue'] . ')*)( ' . Where::getMasks()[0] . ')?'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['setValue' => '(' . Field::getMasks()[0] . '|' . Field::getMasks()[1] . ') = ((' . LogicOperation::getPatterns()['stringValue'].')|(\?))'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $mask = parent::reestablish($string);
    $o = new self(Table::reestablish($mask[1]));
    $data = explode(',', $mask[2]);
    // Запись данных в запрос
    foreach($data as $v){
      $v = explode('=', $v);
      $v[1] = trim($v[1]);
      if($v[1] == '?'){
        $v[1] = null;
      }
      else{
        $v[1] = substr($v[1], 1, -1);
      }
      $o->addData(Field::reestablish(trim($v[0])), $v[1]);
    }
    if(($p = strrpos($string, 'WHERE')) !== false){
      $o->insertWhere(Where::reestablish(substr($string, $p)));
    }

    return $o;
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Table $table Целевая таблица.
   */
  function __construct(Table $table){
    $this->fields = [];
    $this->values = [];
    $this->table = $table;
  }

  /**
   * Метод устанавливает условие отбора для запроса.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Where $where Условие отбора.
   * @return \D\library\patterns\entity\SQL\operators\DML\Update Метод возвращает вызываемый объект.
   */
  public function insertWhere(Where $where){
    $this->where = $where;

    return $this;
  }

  /**
   * Метод добавляет данные в запрос.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $field Целевое поле.
   * @param string|number|boolean $value [optional] Значение целевого поля или null - если значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если указанное поле уже присутствует в запросе.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Update Метод возвращает вызываемый объект.
   */
  public function addData(Field $field, $value = null){
    if(array_search($field, $this->fields) !== false){
      throw new DuplicationException('Ошибка дублирования компонента [D\library\patterns\entity\SQL\operators\DML\components\Field] в SQL инструкции [D\library\patterns\entity\SQL\operators\DML\Update].');
    }
    InvalidArgumentException::verify($value, 'sifbn');
    $this->fields[] = $field;
    $this->values[] = $value;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    if(count($this->values) == 0){
      throw new NotFoundDataException('Недостаточно данных для интерпретации [D\library\patterns\entity\SQL\operators\DML\Update].');
    }
    $resultString = 'UPDATE ' . $this->table->interpretation($driver) . ' SET ';
    foreach($this->fields as $k => $field){
      if(is_null($this->values[$k])){
        $val = '?';
      }
      else{
        $val = '"'.$this->values[$k].'"';
      }
      $resultString .= $field->interpretation($driver) . ' = ' . $val . ',';
    }
    $resultString = substr($resultString, 0, -1);
    if(!empty($this->where)){
      InvalidArgumentException::verify($driver, 'ns', [1]);
      $resultString .= ' ' . $this->where->interpretation($driver);
    }

    return $resultString;
  }

  /**
   * Метод возвращает целевую таблицу.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Table Целевая таблица.
   */
  public function getTable(){
    return $this->table;
  }

  /**
   * Метод возвращает условие отбора.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Where Условие отбора.
   */
  public function getWhere(){
    return $this->where;
  }

  /**
   * Метод возвращает массив изменяемых полей.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field[] Массив изменяемых полей.
   */
  public function getFields(){
    return $this->fields;
  }

  /**
   * Метод возвращает массив записываемых данных.
   * @return mixed[] Массив записываемых данных.
   */
  public function getValues(){
    return $this->values;
  }
}
