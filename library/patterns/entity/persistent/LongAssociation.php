<?php
namespace D\library\patterns\entity\persistent;

use D\library\patterns\entity\reflection\ReflectionClass;
use D\library\patterns\entity\SQL\operators\DML\Select;

/**
 * Класс представляет множество ссылок на персистентные объекты, которое может быть восстановлено из БД.
 * Экземпляр данного класса может находится в двух состояниях:
 * 1. Невостановленное состояние - ассоциация не содержит ссылок, а только SQL инструкцию, позволяющую восстановить ее;
 * 2. Востановленное состояние - ассоциация содержит ссылки.
 * Алгоритм восстановления объектов данного класса является частью востанавливающего класса (DataMapper).
 * @author Artur Sh. Mamedbekov
 */
class LongAssociation extends \SplObjectStorage{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\Select SQL инструкция, служащая для восстановления множества.
   */
  protected $selectQuery;

  /**
   * @var \D\library\patterns\entity\reflection\ReflectionClass Отображение класса, являющегося основанием для восстановления. Ассоциация может включать ссылки только на экземпляры данного класса.
   */
  protected $assocClass;

  /**
   * @param \D\library\patterns\entity\SQL\operators\DML\Select $selectQuery SQL инструкция, служащая для восстановления множества.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $assocClass Отображение класса, являющегося основанием для восстановления.
   */
  function __construct(Select $selectQuery, ReflectionClass $assocClass){
    $this->selectQuery = $selectQuery;
    $this->assocClass = $assocClass;
  }

  /**
   * Метод возвращает отражение класса, являющегося основанием для восстановления.
   * @return \D\library\patterns\entity\reflection\ReflectionClass Отражение класса, являющегося основанием для восстановления
   */
  public function getAssocClass(){
    return $this->assocClass;
  }

  /**
   * Метод возвращает SQL инструкцию, используемую для восстановления ассоциации.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select SQL инструкция, используемая для восстановления ассоциации.
   */
  public function getSelectQuery(){
    return $this->selectQuery;
  }
}