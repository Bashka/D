<?php
namespace D\library\patterns\structure\conversion;

/**
 * Объекты классов, реализующие данный интерфейс, могут быть восстановлены из строки.
 * @author Artur Sh. Mamedbekov
 */
interface Restorable{
  /**
   * Метод позволяет определить допустимость восстановления объекта из строки-основания.
   * @param string $string Строка-основание.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику интерпретации исходной строки.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return boolean true - если интерпретация возможна, иначе - false.
   */
  public static function isReestablish($string, $driver = null);

  /**
   * Метод восстанавливает объект из строки-основания.
   * @param string $string Строка-основание.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику восстановления объекта.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\StructureDataException Выбрасывается в случае, если строка-основание не отвечает требования структуры.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return static Восстановленный объект.
   */
  public static function reestablish($string, $driver = null);
}