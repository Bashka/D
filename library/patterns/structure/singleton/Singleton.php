<?php
namespace D\library\patterns\structure\singleton;

/**
 * Класс, реализующий данный интерфейс, может быть инстанциирован только единожды, последующие попытки инстанциации приведут к возврату уже существующего экземпляра.
 * @author Artur Sh. Mamedbekov
 */
interface Singleton{
  /**
   * Метод возвращает единтвенный экземпляр данного класса.
   * @return static Единственный экземпляр данного класса.
   */
  public static function getInstance();
}