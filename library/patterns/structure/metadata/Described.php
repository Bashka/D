<?php
namespace D\library\patterns\structure\metadata;

/**
 * Класс, реализующий данный интерфейс может быть описан с использованием метаданных.
 * @author  Artur Sh. Mamedbekov
 */
interface Described{
  /**
   * Метод возвращает все метаданные вызываемого объекта.
   * @return string[] Ассоциативный массив, в качестве ключей которого установлены имена метаданных, а в качестве значений их (метаданных) значения.
   */
  public function getAllMetadata();

  /**
   * Метод возвращает значение конкретных метаданных вызываемого объекта.
   * @param string $name Имя метаданных.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается при запросе не установленных метаданных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа или недопустимого значения.
   * @return string Метод возвращает значение метаданных.
   */
  public function getMetadata($name);

  /**
   * Метод проверяет, установлены ли метаданные в вызываемом объекте.
   * @param string $name Имя метаданных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа или недопустимого значения.
   * @return boolean true - если метаданные установлены, иначе - false.
   */
  public function hasMetadata($name);
}
