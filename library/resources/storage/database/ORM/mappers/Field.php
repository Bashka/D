<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\SQL\operators\DML\components as DMLComponents;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\exceptions\semanticExceptions\LackException;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\structure\conversion\Metamorphosis;

/**
 * Данный класс позволяет восстанавливать объекты типа D\library\patterns\entity\SQL\operators\DML\components\Field с использовании персистентного объекта класса LongObject и имени его свойства.
 * @author Artur Sh. Mamedbekov
 */
class Field extends DMLComponents\Field implements Metamorphosis{
  /**
   * Имя поля таблицы, с которым ассоциированно свойство.
   */
  const ORM_FIELD_NAME = 'ORM\ColumnName';

  /**
   * Метод восстанавливает SQL компонент Field на основании требуемого свойства персистентного класса.
   * Свойство класса-основания должно сопровождаться анотацией ORM\ColumnName, хранящей имя ассоциированного поля в таблице данного класса.
   * Класс-основание должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $object Отражение класса-оснвования.
   * @param string $driver Имя свойства объекта, с которым ассоциировано поле.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field Результирующий объект.
   */
  public static function metamorphose($object, $driver = null){
    if(!is_a($object, 'D\library\patterns\entity\reflection\ReflectionClass')){
      throw InvalidArgumentException::getTypeException('D\library\patterns\entity\reflection\ReflectionClass', get_class($object));
    }
    InvalidArgumentException::verify($driver, 's', [1]);
    /**
     * @var Reflect $className
     */
    $className = $object->getName();
    try{
      $reflectionField = $className::getReflectionProperty($driver);
    }
    catch(LackException $e){
      throw new NotFoundDataException('Отсутствует запрошенное свойство ['.$driver.'] класса ['.$className.']. Преобразование невозможно.', 1, $e);
    }
    // Проекция primary key поля.
    if($reflectionField->getName() == 'OID'){
      if(!$object->hasMetadata(Join::ORM_PK)){
        throw new NotFoundDataException('Отсутствуют необходимые метаданные [' . Join::ORM_PK . '] класса ['.$className.']. Преобразование невозможно.');
      }
      $field = new DMLComponents\Field($object->getMetadata(Join::ORM_PK));
      $field->setTable(Table::metamorphose($object)); // Primary key относится к текущему классу
    }
    // Проекция не ключевого поля.
    else{
      if(!$reflectionField->hasMetadata(self::ORM_FIELD_NAME)){
        throw new NotFoundDataException('Отсутствуют необходимые метаданные [' . self::ORM_FIELD_NAME . '] свойства [' . $driver . '] класса ['.$className.']. Преобразование невозможно.');
      }
      $field = new DMLComponents\Field($reflectionField->getMetadata(self::ORM_FIELD_NAME));
      // Поиск класса, к которому относится поле
      /**
       * @var Reflect $reflectionClass
       */
      $reflectionClass = $reflectionField->getDeclaringClass()->getName();
      $field->setTable(Table::metamorphose($reflectionClass::getReflectionClass()));
    }

    return $field;
  }
}