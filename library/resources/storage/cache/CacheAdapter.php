<?php
namespace D\library\resources\storage\cache;

/**
 * Объекты-адаптеры, предоставляющие интерфейс для работы с кэш-системами.
 * @author Artur Sh. Mamedbekov
 */
abstract class CacheAdapter{
  /**
   * Метод записывает значение в кэш.
   * @param string $key Ключ.
   * @param mixed $value Значение.
   * @param integer $time [optional] Время кэширования в секундах.
   */
  public abstract function set($key, $value, $time = null);

  /**
   * Метод возвращает данные из кэша.
   * @param string $key Ключ запрашиваемого значения.
   * @return string|null Ассоциированное с ключем значение или null, если значение не установленно.
   */
  public abstract function get($key);

  /**
   * Метод удаляет данные из кэша.
   * @param string $key Ключ удаляемого значения.
   */
  public abstract function remove($key);

  /**
   * Метод устанавливает соединение с кэш-системой.
   * @param string $host Адрес сервера, на котором располагается система.
   * @param integer $port [optional] Порт для соединения.
   * @throws \D\library\resources\storage\cache\CacheException Выбрасывается в случае ошибки при подключении к системе кэширования.
   */
  public abstract function connect($host, $port = null);
}
