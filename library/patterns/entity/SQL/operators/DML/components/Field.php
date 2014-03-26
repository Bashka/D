<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Класс представляет поле таблицы в запросе.
 * @author Artur Sh. Mamedbekov
 */
class Field extends ComponentQuery{
  /**
   * @var string Имя поля.
   */
  private $name;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Table Таблица, к которой относится поле.
   */
  private $table;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return [self::getPatterns()['fieldName'], Table::getPatterns()['tableName'] . '\.' . self::getPatterns()['fieldName']];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['fieldName' => '[A-Za-z_][A-Za-z0-9_]*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $mask = parent::reestablish($string);
    $object = null;
    if($mask['key'] == 0){
      $object = new static($string);
    }
    elseif($mask['key'] == 1){
      $components = explode('.', $string);
      /**
       * @var Field $object
       */
      $object = new static($components[1]);
      $object->setTable(Table::reestablish($components[0]));
    }

    return $object;
  }

  /**
   * @param string $name Имя поля.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct($name){
    InvalidArgumentException::verify($name, 's', [1]);
    $this->name = $name;
  }

  /**
   * Метод определяет таблицу, к которой относится данное поле.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Table $table Таблица, к которой будет относится поле.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field Метод возвращает вызываемый объект.
   */
  public function setTable(Table $table){
    $this->table = $table;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    if(!empty($this->table)){
      return $this->table->interpretation($driver) . '.' . $this->name;
    }
    else{
      return $this->name;
    }
  }

  /**
   * Метод возвращает таблицу, к которой относится поле.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Table Таблица, к которой относится поле.
   */
  public function getTable(){
    return $this->table;
  }

  /**
   * Метод возвращает имя поля.
   * @return string Имя поля.
   */
  public function getName(){
    return $this->name;
  }
}
