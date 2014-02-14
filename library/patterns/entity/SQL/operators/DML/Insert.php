<?php
namespace D\library\patterns\entity\SQL\operators\DML;

use D\library\patterns\entity\SQL\operators\ComponentQuery;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\Operator;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет объектную SQL инструкцию для вставки записи в таблицу.
 * Объекты данного класса могут быть восстановлены из строки следующего формата: INSERT INTO имяТаблицы (имяПоля|имяТаблицы.имяПоля, ...) VALUES ("данные", ...).
 * @author Artur Sh. Mamedbekov
 */
class Insert extends ComponentQuery implements Operator{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Table Целевая таблица.
   */
  private $table;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Field[] Множество используемых в запросе полей таблицы.
   */
  private $fields;

  /**
   * @var mixed[] Множество устанавливаемых значений записи.
   */
  private $values;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\Select SQL инструкция, возвращающая добавляемое множество значений записи(ей).
   */
  private $select;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['INSERT INTO (' . Table::getPatterns()['tableName'] . ') (\((' . Field::getMasks()[0] . '|' . Field::getMasks()[1] . ')(, ?(' . Field::getMasks()[0] . '|' . Field::getMasks()[1] . '))*\) VALUES \(((' . LogicOperation::getPatterns()['stringValue'] . ')|(\?))(, ?((' . LogicOperation::getPatterns()['stringValue'] . ')|(\?)))*\))'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $mask = parent::reestablish($string);
    $o = new self(Table::reestablish($mask[1]));
    $data = explode('VALUES', $mask[2]);
    // Обработка полей
    $fields = explode(',', substr(trim($data[0]), 1, -1));
    // Обработка значений
    $values = explode(',', substr(trim($data[1]), 1, -1));
    // Запись данных в запрос
    foreach($fields as $k => $v){
      $values[$k] = trim($values[$k]);
      if($values[$k] == '?'){
        $values[$k] = null;
      }
      else{
        $values[$k] = substr($values[$k], 1, -1); // Удаление обрамляющих скалярные данные кавычек.
      }
      $o->addData(Field::reestablish(trim($v)), $values[$k]);
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
   * Метод добавляет данные в запрос.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $field Целевое поле.
   * @param string|number|boolean $value [optional] Значение поля или null - если значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если указанное поле уже присутствует в запросе.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Insert Метод возвращает вызываемый объект.
   */
  public function addData(Field $field, $value = null){
    if(array_search($field, $this->fields) !== false){
      throw new DuplicationException('Ошибка дублирования компонента.');
    }
    InvalidArgumentException::verify($value, 'sifbn');
    $this->fields[] = $field;
    $this->values[] = $value;

    return $this;
  }

  /**
   * Метод устанавливает запрос, возвращающий данные для добавления.
   * @param \D\library\patterns\entity\SQL\operators\DML\Select $select SELECT запрос, возвращающий данные для добавления.
   * @return \D\library\patterns\entity\SQL\operators\DML\Insert Метод возвращает вызываемый объект.
   */
  public function setSelect(Select $select){
    $this->select = $select;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);
    if(count($this->values) == 0 && !is_object($this->select)){
      throw new NotFoundDataException('Нет данных для формирования строки [D\library\patterns\entity\SQL\operators\DML\Insert].');
    }
    $resultString = 'INSERT INTO ' . $this->table->interpretation($driver) . ' ';
    // Генерация запроса с данными вложенного запроса
    if(is_object($this->select)){
      $resultString .= $this->select->interpretation($driver);
    }
    else{
      $resultString .= '(';
      foreach($this->fields as $field){
        $resultString .= $field->interpretation($driver) . ',';
      }
      $resultString = substr($resultString, 0, -1);
      // Генерация запроса с константными данными
      $resultString .= ') VALUES (';
      foreach($this->values as $val){
        if(is_null($val)){
          $resultString .= '?,';
        }
        else{
          $resultString .= '"' . $val . '",';
        }
      }
      $resultString = substr($resultString, 0, -1);
      $resultString .= ')';
    }

    return $resultString;
  }

  /**
   * Возвращает целевую таблицу.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Table Целевая таблица.
   */
  public function getTable(){
    return $this->table;
  }

  /**
   * Метод возвращает список затрагиваемых полей.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field[] Список затрагиваемых полей.
   */
  public function getFields(){
    return $this->fields;
  }

  /**
   * Метод возвращает список записываемых значений.
   * @return mixed[] Список записываемых значений.
   */
  public function getValues(){
    return $this->values;
  }

  /**
   * Метод возвращает SQL инструкцию, возвращающую изменяемые данные.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select SQL инструкция, возвращающая изменяемые данные.
   */
  public function getSelect(){
    return $this->select;
  }
}
