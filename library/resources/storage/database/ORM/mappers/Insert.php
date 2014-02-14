<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\persistent\LongObject;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\structure\conversion\Metamorphosis;
use D\library\patterns\entity\SQL\operators\DML as DML;

/**
 * Класс восстанавливает SQL инструкцию добавления состояния персистентного объекта.
 * @author Artur Sh. Mamedbekov
 */
class Insert extends DML\Insert implements Metamorphosis{
  /**
   * Метод восстанавливает SQL инструкцию Insert состояния персистентного объекта.
   * Метод учитывает иерархию наследования таблиц и возвращает транзакцию запросов.
   * В запрос включаются только те свойства, которые анотированы как ORM\ColumnName и которые возвращаются методом TOriginator::getSavedState исходного объекта.
   * Класс объекта должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * Класс объекта должен сопровождаться анотацией ORM\PK, хранящей имя primary key поля таблицы.
   * @param \D\library\patterns\entity\persistent\LongObject $object Исходный объект.
   * @param integer $driver Идентификатор новой записи.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Insert[] Транзакция запросов.
   */
  public static function metamorphose($object, $driver = null){
    if(!is_a($object, 'D\library\patterns\entity\persistent\LongObject')){
      throw InvalidArgumentException::getTypeException('D\library\patterns\entity\persistent\LongObject', get_class($object));
    }
    InvalidArgumentException::verify($driver, 's', [1]);
    if($object->isOID()){
      throw new NotFoundDataException('Исходный объект класса ['.get_class($object).'] идентифицирован. Преобразование невозможно.');
    }
    /**
     * @var DML\Insert[] $inserts
     */
    $inserts = [];
    $state = $object->createMemento()->getState($object);
    foreach($state as $k => &$v){
      // Предварительная сериализация объектов в полученом массиве.
      if(($v instanceof LongObject) && $v->isOID()){
        $v = $v->interpretation();
      }
      // Замена null на пустую строку
      if(is_null($v)){
        $v = '';
      }
      // Формирование полей.
      $reflectionProperty = $object->getReflectionProperty($k);
      if($reflectionProperty->hasMetadata(Field::ORM_FIELD_NAME)){
        $field = Field::metamorphose($object->getReflectionClass(), $k);
        $table = $field->getTable();
        $tableName = $table->getTableName();
        if(!isset($inserts[$tableName])){
          $inserts[$tableName] = new DML\Insert($table);
          /**
           * @var Reflect $declaringClassName
           */
          $declaringClassName = $reflectionProperty->getDeclaringClass()->getName();
          $inserts[$tableName]->addData(Join::getPKField($declaringClassName::getReflectionClass()), $driver);
        }
        $inserts[$tableName]->addData($field, $v);
      }
    }
    // Индексация результата целыми числами.
    $result = [];
    foreach($inserts as $insert){
      $result[] = $insert;
    }

    return $result;
  }
}