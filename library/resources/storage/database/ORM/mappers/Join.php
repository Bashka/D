<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\SQL\operators\DML\components as DMLComponents;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\reflection\ReflectionClass;
use D\library\patterns\structure\conversion\Metamorphosis;

/**
 * Данный класс позволяет восстанавливать объекты типа D\library\patterns\entity\SQL\operators\DML\components\Join с использовании персистентного объекта класса LongObject и отражения связываемого класса.
 * @author Artur Sh. Mamedbekov
 */
class Join extends DMLComponents\Join implements Metamorphosis{
  /**
   * Имя primary key поля, являющегося идентификационным для объектов данного класса.
   */
  const ORM_PK = 'ORM\PK';

  /**
   * Метод восстанавливает SQL инструкцию объединения на основании указанного отражения связываемого класса и их primary key.
   * Класс-основание и связываемый класс должены сопровождаться анотацией ORM\Table, хранящей имя таблицы классов.
   * Класс-основание и связываемый класс должны сопровождаться анотацией ORM\PK, хранящей имя поля primary key, которое ассоциируется с OID персистентных объектов.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $object Отражение исходного класса.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $driver Отражение связываемого класса.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Join Результирующий объект.
   */
  public static function metamorphose($object, $driver = null){
    // Проверка типа параметров выполняется в методе Table::metamorphose.
    $tableOriginal = Table::metamorphose($object);
    $tableAssociate = Table::metamorphose($driver);
    $originalPKField = self::getPKField($object);
    $originalPKField->setTable($tableOriginal);
    $associatePKField = self::getPKField($driver);
    $associatePKField->setTable($tableAssociate);
    return new DMLComponents\Join(DMLComponents\Join::INNER, $tableAssociate, new LogicOperation($originalPKField, '=', $associatePKField));
  }

  /**
   * Метод возвращает SQL компонент Field для primary key класса-основания.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $class Класс-основание.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field Результирующий объект.
   */
  public static function getPKField(ReflectionClass $class){
    if(!$class->hasMetadata(self::ORM_PK)){
      throw new NotFoundDataException('Отсутствуют необходимые метаданные [' . self::ORM_PK . '] класса ['.$class->getName().']. Преобразование невозможно.');
    }

    return new DMLComponents\Field($class->getMetadata(self::ORM_PK));
  }
}