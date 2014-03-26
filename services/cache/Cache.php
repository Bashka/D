<?php
namespace D\services\cache;

use D\library\patterns\structure\singleton\Singleton;

/**
 * Класс-фабрика, отвечающий за инстанциацию и инициализацию кэш-драйверов.
 * @author Artur Sh. Mamedbekov
 */
class Cache implements Singleton{
  /**
   * Используемый драйвер кэша.
   */
  const DRIVER = 'NullAdapter';

  /**
   * Адрес сервера кэша.
   */
  const SERVER = 'localhost:11211';

  /**
   * @var \D\library\resources\storage\cache\CacheAdapter Кэш драйвера.
   */
  private static $cache;

  private function __construct(){
  }

  /**
   * Метод возвращает инициализированный драйвер кэша.
   * @throws \D\library\resources\storage\cache\CacheException Выбрасывается в случае ошибки при подключении к системе кэширования.
   * @return \D\library\resources\storage\cache\CacheAdapter Драйвер кэша.
   */
  public static function getInstance(){
    if(is_null(self::$cache)){
      $adapterName = '\D\library\resources\storage\cache\drivers\\' . self::DRIVER;
      self::$cache = new $adapterName;
      $server = explode(':', self::SERVER);
      if(!isset($server[1])){
        $server[1] = null;
      }
      self::$cache->connect($server[0], $server[1]);
    }

    return self::$cache;
  }
}