<?php
namespace D\services\config;

use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\resources\fileSystem\components\special\ini\INI;
use D\services\cache\Cache;

/**
 * Служба, отвечающая за инициализацию системы.
 * Служба использует следующие свойства кэша: SystemConf::имяСекции::имяСвойства - значение свойства конфигурации.
 * @author Artur Sh. Mamedbekov
 */
class SystemConf implements Singleton{
use TSingleton;
  /**
   * Метод возвращает значение заданного свойства.
   * @param string $section Секция конфигурации.
   * @param string $key Имя свойства.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия указанного свойства.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string Значение свойства.
   */
  public function get($section, $key){
    $cache = Cache::getInstance();
    // Получение значения из кэша.
    if(!is_null($cacheValue = $cache->get('SystemConf::'.$section.'::'.$key))){
      return $cacheValue;
    }
    // Получение значение из файла.
    else{
      $ini = new INI(__DIR__.'/system.ini');
      // Заполнение кэша.
      foreach($ini->getAll() as $sectionName => $s){
        foreach($s as $k => $v){
          $cache->set('SystemConf::'.$sectionName.'::'.$k, $v);
        }
      }
      return $ini->get($section, $key);
    }
  }
}