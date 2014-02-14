<?php
namespace D\library\resources\storage\cache;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-декоратор, предоставляющий дополнительные механизмы по кэшированию персистентных объектов.
 * @author Artur Sh. Mamedbekov
 */
class ObjectCache {
  /**
   * @var \D\library\resources\storage\cache\CacheAdapter Используемый драйвер кэша.
   */
  private $cache;

  /**
   * @param \D\library\resources\storage\cache\CacheAdapter $cache Используемый драйвер кэша.
   */
  public function __construct(CacheAdapter $cache){
    $this->cache = $cache;
  }

  /**
   * Метод добавляет состояние объекта в кэш.
   * @param string $className Имя класса объекта.
   * @param integer $OID Идентификатор объекта.
   * @param mixed[] $state Состояние объекта.
   */
  public function add($className, $OID, array $state){
    InvalidArgumentException::verify($className, 's', [1]);
    InvalidArgumentException::verify($OID, 's', [1]);
    // Добавление объекта в кучу
    $this->cache->set('ObjectCache::Pile::' . $className . '::' . $OID, $state);
    // Добавление индекса на объект
    $classIndex = $this->cache->get('ObjectCache::Classes::' . $className);
    if(is_null($classIndex)){
      $classIndex = [];
    }
    if(array_search($OID, $classIndex) === false){
      $classIndex[] = $OID;
    }
    $this->cache->set('ObjectCache::Classes::' . $className, $classIndex);
  }

  /**
   * Метод возвращает состояние объекта из кэша по его идентификатору.
   * @param string $className Имя класса объекта.
   * @param integer $OID Идентификатор объекта.
   * @return null|mixed[] Состояние объекта или null если объект не найден.
   */
  public function get($className, $OID){
    InvalidArgumentException::verify($className, 's', [1]);
    InvalidArgumentException::verify($OID, 's', [1]);

    return $this->cache->get('ObjectCache::Pile::' . $className . '::' . $OID);
  }

  /**
   * Метод удаляет состояние объекта по его идентификатору.
   * @param string $className Имя класса объекта.
   * @param integer $OID Идентификатор объекта.
   */
  public function remove($className, $OID){
    InvalidArgumentException::verify($className, 's', [1]);
    InvalidArgumentException::verify($OID, 's', [1]);
    $index = 'ObjectCache::Pile::' . $className . '::' . $OID;
    if(!is_null($this->cache->get($index))){
      // Удаление из кучи
      $this->cache->remove($index);
    }
    // Удаление из идекса
    if(!is_null($classIndex = $this->cache->get('ObjectCache::Classes::' . $className))){
      if(($key = array_search($OID, $classIndex)) !== false){
        unset($classIndex[$key]);
        $this->cache->set('ObjectCache::Classes::' . $className, $classIndex);
      }
    }
  }

  /**
   * Метод выполняет поиск объектов в кэше согласно некоторому условию.
   * @param string $className Имя класса объекта.
   * @param mixed[] $conditions Ассоциативный массив, определяющий условие отбора. Массив имеет следующую структуру: [[имяСвойства, оператор, значение], ...].
   * @return mixed[] Массив найденных состояний объектов. Ключами массива служат идентификатору соответствующих объектов. Массив пуст, если ни одного объекта не найдено по данному условию.
   */
  public function find($className, array $conditions){
    InvalidArgumentException::verify($className, 's', [1]);
    $classIndex = $this->cache->get('ObjectCache::Classes::' . $className);
    $result = [];
    if(!is_null($classIndex)){
      foreach($classIndex as $OID){
        $state = $this->get($className, $OID);
        $flag = true;
        foreach($conditions as $condition){
          if(isset($state[$condition[0]])){
            switch($condition[1]){
              case '=':
                $flag *= ($state[$condition[0]] == $condition[2])? 1 : 0;
                break;
              case '!=':
                $flag *= ($state[$condition[0]] != $condition[2])? 1 : 0;
                break;
              case '>':
                $flag *= ($state[$condition[0]] > $condition[2])? 1 : 0;
                break;
              case '<':
                $flag *= ($state[$condition[0]] < $condition[2])? 1 : 0;
                break;
              case '>=':
                $flag *= ($state[$condition[0]] >= $condition[2])? 1 : 0;
                break;
              case '<=':
                $flag *= ($state[$condition[0]] <= $condition[2])? 1 : 0;
                break;
            }
          }
          else{
            $flag *= 0;
          }
        }
        if($flag){
          $result[$OID] = $state;
        }
      }
    }

    return $result;
  }
}