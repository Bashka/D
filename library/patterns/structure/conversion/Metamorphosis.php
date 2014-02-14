<?php
namespace D\library\patterns\structure\conversion;

/**
 * Объекты классов, реализующие данный интерфейс, могут быть восстановлены из других объектов.
 * @author Artur Sh. Mamedbekov
 */
interface Metamorphosis{
  /**
   * Метод восстанавливает объект данного класса из другого объекта.
   * @param Object $object Объект-основание.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику интерпретации исходного объекта.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return static Результирующий объект.
   */
  public static function metamorphose($object, $driver = null);
}