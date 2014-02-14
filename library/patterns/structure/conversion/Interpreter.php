<?php
namespace D\library\patterns\structure\conversion;

/**
 * Объекты классов, реализующие данный интерфейс, могут быть интерпретированы в строку.
 * @author Artur Sh. Mamedbekov
 */
interface Interpreter{
  /**
   * Метод возвращает строку, полученную при интерпретации вызываемого объекта.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику интерпретации объекта.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для интерпретации данных.
   * @return string Результат интерпретации.
   */
  public function interpretation($driver = null);
}
