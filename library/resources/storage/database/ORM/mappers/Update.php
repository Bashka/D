<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\persistent\LongObject;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\structure\conversion\Metamorphosis;
use D\library\patterns\entity\SQL\operators\DML as DML;

/**
 * Класс восстанавливает SQL инструкцию обновления состояния персистентного объекта.
 * @author Artur Sh. Mamedbekov
 */
class Update extends DML\Update implements Metamorphosis{
  /**
   * Метод восстанавливает SQL инструкцию Update состояния персистентного объекта.
   * Метод учитывает иерархию наследования таблиц и возвращает транзакцию запросов.
   * В запрос включаются только те свойства, которые анотированы как ORM\ColumnName и которые возвращаются методом TOriginator::getSavedState исходного объекта.
   * Класс объекта должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * Класс объекта должен сопровождаться анотацией ORM\PK, хранящей имя primary key поля таблицы.
   * @param \D\library\patterns\entity\persistent\LongObject $object Исходный объект.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Where $driver [optional] Если данный параметр передан, он используется как условие отбора в инструкции.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Update[] Транзакция запросов.
   */
  public static function metamorphose($object, $driver = null){
    if(!is_a($object, 'D\library\patterns\entity\persistent\LongObject')){
      throw InvalidArgumentException::getTypeException('D\library\patterns\entity\persistent\LongObject', get_class($object));
    }
    if(!$object->isOID()){
      throw new NotFoundDataException('Исходный объект класса ['.get_class($object).'] не идентифицирован. Преобразование невозможно.');
    }
    /**
     * @var DML\Update[] $updates
     */
    $updates = [];
    $state = $object->createMemento()->getState($object); // Выброс исключений не предполагается
    foreach($state as $k => &$v){
      // Предварительная сериализация объектов в полученом массиве
      if(($v instanceof LongObject) && $v->isOID()){
        $v = $v->interpretation(); // Перехват исключений не выполняется в связи с невозможностью их появления
      }
      // Замена null на пустую строку
      if(is_null($v)){
        $v = '';
      }
      $reflectionProperty = $object->getReflectionProperty($k); // Выброс исключений не предполагается
      if($reflectionProperty->hasMetadata(Field::ORM_FIELD_NAME)){
        $field = Field::metamorphose($object->getReflectionClass(), $k); // Выброс исключений не предполагается
        $table = $field->getTable();
        $tableName = $table->getTableName();
        if(!isset($updates[$tableName])){
          $updates[$tableName] = new DML\Update($table);
          /**
           * @var Reflect $declaringClassName
           */
          $declaringClassName = $reflectionProperty->getDeclaringClass()->getName();
          if(!is_null($driver) && is_a($driver, 'D\library\patterns\entity\SQL\operators\DML\components\Where')){
            $updates[$tableName] = $updates[$tableName]->insertWhere($driver);
          }
          else{
            $updates[$tableName]->insertWhere(new Where(new LogicOperation(Join::getPKField($declaringClassName::getReflectionClass()), '=', $object->getOID())));
          }
        }
        $updates[$tableName]->addData($field, $v);
      }
    }
    // Индексация результата целыми числами
    $result = [];
    foreach($updates as $update){
      $result[] = $update;
    }

    return $result;
  }
}