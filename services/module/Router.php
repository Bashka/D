<?php
namespace D\services\module;

use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\resources\fileSystem\components\special\ini\INI;
use D\services\cache\Cache;

/**
 * Служба отвечает за роутинг модулей.
 * Служба использует следующие свойства кэша: ModuleRouter::init - флаг заполнености кэша картой роутинга; ModuleRouter::имяМодуля - адрес модуля относительно корня хранилища модулей; ModuleRouter::modules - сериализованный массив имен модулей.
 * @author  Artur Sh. Mamedbekov
 */
class Router implements Singleton{
use TSingleton;
  /**
   * Адрес хранилища модулей.
   */
  const MODULES_DIR = 'D/model/modules';

  /**
   * @var string[] Карта роутинга.
   */
  private $map;

  /**
   * @var \D\library\resources\storage\cache\CacheAdapter Драйвер кэша.
   */
  private $cache;

  private function __construct(){
    $this->cache = Cache::getInstance();
    // Заполнение кэша.
    if(is_null($this->cache->get('ModuleRouter::init'))){
      $this->map = parse_ini_file('map.ini', true)['Map'];
      foreach($this->map as $module => $address){
        $this->cache->set('ModuleRouter::'.$module, $address);
      }
      $this->cache->set('ModuleRouter::init', 1);
      $this->cache->set('ModuleRouter::modules', serialize(array_keys($this->map)));
    }
  }

  /**
   * Метод определяет, зарегистрирован ли указанный модуль в роутере.
   * @param string $name Имя модуля.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - если модуль зарегистрирован.
   */
  public function hasModule($name){
    InvalidArgumentException::verify($name, 's', [1]);
    // Обработка при отключеном кэше.
    if(is_null($this->cache->get('ModuleRouter::init'))){
      return isset($this->map[$name]);
    }
    // Обработка при включеном кэше.
    else{
      return !is_null($this->cache->get('ModuleRouter::'.$name));
    }
  }

  /**
   * Метод возвращает адрес модуля относительно корня хранялища модулей.
   * @param string $name Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого модуля в карте роутинга.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string Адрес модуля относительно корня хранялища модулей.
   */
  public function get($name){
    // Проверка аргумента выполняется в методе hasModule.
    if(!$this->hasModule($name)){
      throw new NotExistsException('Запрашиваемый модуль ['.$name.'] отсутствует.');
    }
    // Обработка при отключеном кэше.
    if(is_null($this->cache->get('ModuleRouter::init'))){
      return $this->map[$name];
    }
    // Обработка при включеном кэше.
    else{
      return $this->cache->get('ModuleRouter::'.$name);
    }
  }

  /**
   * Метод возвращает имена зарегистрированных модулей.
   * @return string[] Имена зарегистрированных модулей.
   */
  public function getAllNames(){
    // Обработка при отключеном кэше.
    if(is_null($this->cache->get('ModuleRouter::init'))){
      return array_keys($this->map);
    }
    // Обработка при включеном кэше.
    else{
      return unserialize($this->cache->get('ModuleRouter::modules'));
    }
  }

  /**
   * Метод добавляет модуль в роутер.
   * @param string $name Имя модуля.
   * @param string $parent [optional] Имя родителя.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если добавление модуля приведет к дублированию.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия родительского модуля.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function add($name, $parent = null){
    // Проверка аргумента $name выполняется в методе hasModule.
    InvalidArgumentException::verify($parent, 'ns', [1]);
    if($this->hasModule($name)){
      throw new DuplicationException('Невозможно добавить модуль ['.$name.'], он уже зарегистрирован в роутере.');
    }
    $address = (is_null($parent))? $name : $this->get($parent).'/'.$name;
    // Добавление информации в файл.
    $ini = new INI(__DIR__.'/map.ini');
    $ini->set('Map', $name, $address);
    $ini->rewrite();
    // Добавление информации в карту.
    $this->map = $ini->getSection('Map');
    // Добавление информации в роутер.
    $this->cache->set('ModuleRouter::'.$name, $address);
    $this->cache->set('ModuleRouter::modules', serialize(array_keys($this->map)));
  }

  /**
   * Метод удаляет модуль из роутера.
   * @param string $name Имя модуля.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого модуля в карте роутинга.
   */
  public function remove($name){
    // Проверка аргумента выполняется в методе hasModule.
    if(!$this->hasModule($name)){
      throw new NotExistsException('Невозможно удалить модуль ['.$name.'], он еще не зарегистрирован в роутере.');
    }
    // Удаление информации из файла.
    $ini = new INI(__DIR__.'/map.ini');
    $ini->remove('Map', $name);
    $ini->rewrite();
    // Удаление информации из карты.
    $this->map = $ini->getSection('Map');
    // Удаление информации из роутера.
    $this->cache->remove('ModuleRouter::'.$name);
    $this->cache->set('ModuleRouter::modules', serialize(array_keys($this->map)));
  }

  /**
   * Метод возвращает карту роутинга.
   * Метод не использует кэширование.
   * @return string[] Карта роутинга.
   */
  public function getAll(){
    return parse_ini_file('map.ini', true)['Map'];
  }
} 