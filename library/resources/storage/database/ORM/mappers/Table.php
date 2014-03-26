<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\SQL\operators\DML\components as DMLComponents;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\conversion\Metamorphosis;

/**
 * Данный класс позволяет восстанавливать объекты типа D\library\patterns\entity\SQL\operators\DML\components\Table с использовании отражения персистентного класса.
 * @author Artur Sh. Mamedbekov
 */
class Table extends DMLComponents\Table implements Metamorphosis{
  /**
   * Имя таблицы, с которой ассоциирован класс.
   */
  const ORM_NAME_TABLE = 'ORM\Table';

  /**
   * Метод восстанавливает SQL компонент Table на основании отражения класса.
   * Класс-основание должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $object Исходный объект.
   * @param mixed $driver [optional] Данный аргумент не используется.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Table Результирующий объект.
   */
  public static function metamorphose($object, $driver = null){
    if(!is_a($object, 'D\library\patterns\entity\reflection\ReflectionClass')){
      throw InvalidArgumentException::getTypeException('D\library\patterns\entity\reflection\ReflectionClass', get_class($object));
    }
    if(!$object->hasMetadata(self::ORM_NAME_TABLE)){
      throw new NotFoundDataException('Отсутствуют необходимые метаданные [' . self::ORM_NAME_TABLE . '] класса ['.$object->getName().']. Преобразование невозможно.');
    }

    return new DMLComponents\Table($object->getMetadata(self::ORM_NAME_TABLE));
  }
}