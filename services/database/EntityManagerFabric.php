<?php
namespace D\services\database;

use D\library\patterns\structure\singleton\Singleton;
use D\library\resources\storage\cache\ObjectCache;
use D\library\resources\storage\database\ORM\EntityManager;
use D\library\resources\storage\database\generator\GUIDGenerator;
use D\services\cache\Cache;

class EntityManagerFabric implements Singleton{
  /**
   * @var \D\library\resources\storage\database\ORM\EntityManager Менеджер для работы с персистентными объектами.
   */
  private static $entityManager;

  private function __construct(){}

  /**
   * Метод возвращает менеджер для работы с персистентными объектами.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае появления ошибки во время подключения к СУБД.
   * @throws \D\library\resources\storage\cache\CacheException Выбрасывается в случае ошибки при подключении к системе кэширования.
   * @return \D\library\resources\storage\database\ORM\EntityManager Менеджер для работы с персистентными объектами.
   */
  public static function getInstance(){
    if(is_null(self::$entityManager)){
      /**
       * @var EntityManager $entityManager
       */
      $entityManager = EntityManager::getInstance();
      $entityManager->setDO(DDOFabric::getInstance());
      $entityManager->setOIDGenerator(GUIDGenerator::getInstance());
      $entityManager->setObjectCache(new ObjectCache(Cache::getInstance()));
      self::$entityManager = $entityManager;
    }

    return self::$entityManager;
  }
} 