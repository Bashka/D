<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\structure\conversion\Metamorphosis;
use D\library\patterns\entity\SQL\operators\DML as DML;

/**
 * Класс восстанавливает SQL инструкцию удаления состояния персистентного объекта на основании его Proxy или другого условия отбора.
 * @author Artur Sh. Mamedbekov
 */
class Delete extends DML\Delete implements Metamorphosis{
  /**
   * Метод восстанавливает SQL инструкцию Delete состояния персистентного объекта на основании его Proxy.
   * Метод учитывает иерархию наследования таблиц и возвращает транзакцию запросов.
   * В запрос включаются только те свойства, которые анотированы как ORM\ColumnName.
   * Класс объекта должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * Класс объекта должен сопровождаться анотацией ORM\PK, хранящей имя primary key поля таблицы.
   * @param \D\library\patterns\entity\persistent\LongObject $object Исходный объект.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Where $driver [optional] Если данный параметр передан, он используется как условие отбора в инструкции.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Delete[] Транзакция запросов.
   */
  public static function metamorphose($object, $driver = null){
    if(!is_a($object, 'D\library\patterns\entity\persistent\LongObject')){
      throw InvalidArgumentException::getTypeException('D\library\patterns\entity\persistent\LongObject', get_class($object));
    }
    if(!$object->isOID()){
      throw new NotFoundDataException('Исходный объект класса ['.get_class($object).'] не идентифицирован. Преобразование невозможно.');
    }
    $deletes = [];
    $reflectionClass = $object->getReflectionClass();
    do{
      if($reflectionClass->hasMetadata(Table::ORM_NAME_TABLE)){
        $tableName = $reflectionClass->getMetadata(Table::ORM_NAME_TABLE);
        $delete = new DML\Delete(new DML\components\Table($tableName));
        if(!is_null($driver) && is_a($driver, 'D\library\patterns\entity\SQL\operators\DML\components\Where')){
          $deletes[] = $delete->insertWhere($driver);
        }
        else{
          $deletes[] = $delete->insertWhere(new Where(new LogicOperation(Join::getPKField($reflectionClass), '=', $object->getOID())));
        }
      }
      /**
       * @var Reflect $parentReflectionClassName
       */
      $parentReflectionClassName = $reflectionClass->getName();
    } while(($parentReflectionClassName != 'D\library\patterns\structure\conversion\RestorableAdapter') && ($reflectionClass = $parentReflectionClassName::getParentReflectionClass()));

    return $deletes;
  }
}