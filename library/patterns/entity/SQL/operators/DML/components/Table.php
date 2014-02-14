<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Класс представляет таблицу в запросе.
 * @author Artur Sh. Mamedbekov
 */
class Table extends ComponentQuery{
  /**
   * @var string Имя таблицы.
   */
  private $tableName;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return [self::getPatterns()['tableName']];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['tableName' => '[A-Za-z_][A-Za-z0-9_]*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);

    return new static($string);
  }

  /**
   * @param string $tableName Имя таблицы.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct($tableName){
    InvalidArgumentException::verify($tableName, 's', [1]);
    $this->tableName = $tableName;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    return $this->tableName;
  }

  /**
   * Метод возвращает имя таблицы.
   * @return string Имя таблицы.
   */
  public function getTableName(){
    return $this->tableName;
  }
}
