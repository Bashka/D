<?php
namespace D\services\database;

use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\resources\storage\database\DDO;
use D\library\resources\storage\database\DDOException;
use D\services\config\SystemConf;

/**
 * Служба, отвечающая за соединение с базой данных.
 * @author Artur Sh. Mamedbekov
 */
class DDOFabric implements Singleton{
  /**
   * @var \D\library\resources\storage\database\DDO Объектный интерфейс соединения с базой данных.
   */
  private static $DDO;

  private function __construct(){}

  /**
   * Метод возвращает объектный интерфейс соединения с базой данных.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае появления ошибки во время подключения к СУБД.
   * @return \D\library\resources\storage\database\DDO Объектный интерфейс соединения с базой данных.
   */
  public static function getInstance(){
    if(is_null(self::$DDO)){
      /**
       * @var SystemConf $conf
       */
      $conf = SystemConf::getInstance();
      try{
        self::$DDO = new DDO($conf->get('Database', 'driver') . ':host=' . $conf->get('Database', 'host') . ';dbname=' . $conf->get('Database', 'DBName') . ';charset=UTF8', $conf->get('Database', 'user'), $conf->get('Database', 'password'), [\PDO::ATTR_PERSISTENT => true]);
      }
      catch(\PDOException $e){
        throw new DDOException('Невозможно подключиться к СУБД.', 1, $e);
      }
    }

    return self::$DDO;
  }
} 