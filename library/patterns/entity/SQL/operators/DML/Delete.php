<?php
namespace D\library\patterns\entity\SQL\operators\DML;

use D\library\patterns\entity\SQL\operators\ComponentQuery;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\Operator;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет объектную SQL инструкцию для удаления записей из таблицы.
 * Объекты данного класса могут быть восстановлены из строки следующего формата: DELETE FROM имяТаблицы [WHERE (логическоеВыражение)].
 * @author Artur Sh. Mamedbekov
 */
class Delete extends ComponentQuery implements Operator{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Table Целевая таблица.
   */
  private $table;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Where Условие отбора.
   */
  private $where;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['DELETE FROM (' . Table::getMasks()[0] . ')( ' . Where::getMasks()[0] . ')?'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    /**
     * @var string[] $mask
     */
    $mask = parent::reestablish($string);
    $o = new Delete(Table::reestablish($mask[1]));
    $o->insertWhere(Where::reestablish(trim($mask[2])));

    return $o;
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Table $table Целевая таблица.
   */
  function __construct(Table $table){
    $this->table = $table;
  }

  /**
   * Метод устанавливает условие отбора для запроса.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Where $where Условие отбора.
   * @return \D\library\patterns\entity\SQL\operators\DML\Delete Метод возвращает вызываемый объект.
   */
  public function insertWhere(Where $where){
    $this->where = $where;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);

    return 'DELETE FROM ' . $this->table->interpretation($driver) . '' . (is_object($this->where)? ' ' . $this->where->interpretation($driver) : '');
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
}
