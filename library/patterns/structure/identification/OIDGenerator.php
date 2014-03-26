<?php
namespace D\library\patterns\structure\identification;

/**
 * Интерфейс определяет семантику генератора идентификационных номеров.
 * @author Artur Sh. Mamedbekov
 */
interface OIDGenerator{
  /**
   * Метод генерирует новый идентификатор и возвращает его.
   * @throws \D\library\patterns\structure\identification\OIDException Выбрасывается в случае невозможности генерации нового идентификатора.
   * @return string Возвращает новый идентификатор.
   */
  public function generateOID();
}