<?php
namespace D\library\resources\storage\database\ORM;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\persistent\LongAssociation;
use D\library\patterns\entity\persistent\LongObject;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\entity\reflection\ReflectionClass;
use D\library\patterns\entity\reflection\ReflectionProperty;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\structure\identification\OIDGenerator;
use D\library\patterns\structure\memento\Memento;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\resources\storage\cache\ObjectCache;
use D\library\resources\storage\database\DDO;
use D\library\resources\storage\database\ORM\mappers\Delete;
use D\library\resources\storage\database\ORM\mappers\Field;
use D\library\resources\storage\database\ORM\mappers\Insert;
use D\library\resources\storage\database\ORM\mappers\Join;
use D\library\resources\storage\database\ORM\mappers\Select;
use D\library\resources\storage\database\ORM\mappers\Update;

/**
 * Фассадный класс, позволяющий сохранять, восстанавливать, обновлять и удалять состояния персистентных объектов в реляционых базах данных по средствам SQL запросов к ним.
 * @author Artur Sh. Mamedbekov
 */
class EntityManager implements Singleton{
  use TSingleton;

  /**
   * Имя класса, объекты которого ассоциированы с данным объектом множественной связью.
   */
  const ORM_ASSOC_CLASS = 'ORM\Assoc';

  /**
   * Имя свойства, которым ассоциированные объекты ссылаются на данный объект.
   */
  const ORM_ASSOC_FK = 'ORM\FK';

  /**
   * Маркер, определяющий композиционную множественную ассоциацию. Свойства множественных ассоциаций, помеченные данным маркером, определяют классы, объекты которых должны быть удалены при удалении агрегата.
   */
  const ORM_COMPOSITION = 'ORM\Composition';

  /**
   * Маркерная аннотация, определяющая ассоциативное свойство, которое выполняет полную инициализацию связанных объектов (тип связи - один к одному, много к одному, один ко многим, много ко многому).
   */
  const ORM_FULL = 'ORM\Full';

  /**
   * Маркерная аннотация, определяющая поля, значения которых не должны повторяться в таблице, хранящей объект.
   * Если данная аннотация определена для нескольких свойств объекта, то их совокупность не должна повторяться.
   */
  const ORM_UNIQUE = 'ORM\Unique';

  /**
   * Маркерная аннотация, определяющая классы, объекты которых должны кэшироваться.
   */
  const CACHE_CACHE = 'Cache\Cache';

  /**
   * @var \D\library\resources\storage\database\DDO Объект, отвечающий за взаимодействие с базой данных.
   */
  private $do;

  /**
   * @var \D\library\patterns\structure\identification\OIDGenerator
   */
  private $OIDGenerator;

  /**
   * @var \D\library\resources\storage\cache\ObjectCache|null Объектный кэш.
   */
  private $objectCache;

  /**
   * Метод устанавливает объект, отвечающий за взаимодействие с базой данных.
   * @param \D\library\resources\storage\database\DDO $do Объект, отвечающий за взаимодействие с базой данных.
   */
  public function setDO(DDO $do){
    $this->do = $do;
  }

  /**
   * Метод возвращает объект, используемый для взаимодействия с базой данных.
   * @return \D\library\resources\storage\database\DDO $do Объект, отвечающий за взаимодействие с базой данных.
   */
  public function getDO(){
    return $this->do;
  }

  /**
   * Метод устанавливает компонент генерации идентификаторов.
   * @param \D\library\patterns\structure\identification\OIDGenerator $generator Объект, отвечающий за генерацию уникальных идентификаторов.
   */
  public function setOIDGenerator(OIDGenerator $generator){
    $this->OIDGenerator = $generator;
  }

  /**
   * Метод устанавливает объектный кэш.
   * @param \D\library\resources\storage\cache\ObjectCache $cache Используемый объектный кэш.
   */
  public function setObjectCache(ObjectCache $cache){
    $this->objectCache = $cache;
  }

  /**
   * Метод восстанавливает объект на основании массива свойств.
   * Ссылки на персистентные объекты восстанавливаются в виде Proxy этих объектов.
   * Свойства класса объекта, аннотированные по средствам метаданных ORM_ASSOC_CLASS и ORM_ASSOC_FK восстанавливаются в виде LongAssociation как множества объектов, ссылающихся на данных, восстанавливаемый объект. Метод восстановления ассоциации "ленивый".
   * @param \D\library\patterns\entity\persistent\LongObject $object Восстанавливаемый объект.
   * @param mixed[] $data Массив свойств.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   */
  public function setStateObject(LongObject &$object, array $data){
    // Определение идентификатора объекта
    if(!$object->isOID()){
      $object->setOID($data['OID']);
    }
    // Преобразование объектных ссылок в Proxy
    foreach($data as $name => &$value){
      if(is_string($value) && LongObject::isReestablish($value)){
        $value = LongObject::reestablish($value);
        // Полное восстановление связи
        if($object->getReflectionProperty($name)->hasMetadata(self::ORM_FULL)){
          $this->recover($value);
        }
      }
    }
    // Восстановление множественных ассоциаций
    $reflectionProperties = $object->getAllReflectionProperties();
    /**
     * @var ReflectionProperty $property
     */
    foreach($reflectionProperties as $property){
      // Работа только со свойствами, имеющими соответствующие аннотации
      if($property->hasMetadata(self::ORM_ASSOC_CLASS) && $property->hasMetadata(self::ORM_ASSOC_FK)){
        /**
         * @var Reflect $reflectionAssocCLass
         */
        $reflectionAssocCLass = $property->getMetadata(self::ORM_ASSOC_CLASS);
        $reflectionAssocCLass = $reflectionAssocCLass::getReflectionClass();
        try{
          $assoc = new LongAssociation(Select::metamorphoseAssociation($reflectionAssocCLass, [[$property->getMetadata(self::ORM_ASSOC_FK), '=', $object->interpretation()]]), $reflectionAssocCLass);
          // Полное восстановление связи
          if($property->hasMetadata(self::ORM_FULL)){
            $this->recoverAssoc($assoc);
          }
          $data[$property->getName()] = $assoc;
        }
        catch(NotFoundDataException $e){
          throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
        }
      }
    }
    $object->restoreFromMemento(new Memento($object, $data));
  }

  /**
   * Метод определяет, является ли объект с уникальными свойствами дублирующим.
   * @param \D\library\patterns\entity\persistent\LongObject $object Проверяемый объект.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   * @return boolean true - если объект является дублирующим, иначе - false.
   */
  public function isDuplicate(LongObject $object){
    $values = $object->createMemento()->getState($object);
    $properties = $object->getAllReflectionProperties();
    $conditions = [];
    /**
     * @var ReflectionProperty $property
     */
    foreach($properties as $property){
      if($property->hasMetadata(self::ORM_UNIQUE) && $property->hasMetadata(Field::ORM_FIELD_NAME)){
        $propertyName = $property->getName();
        $conditions[] = [$propertyName, '=', $values[$propertyName]];
      }
    }
    if(count($conditions) > 0){
      try{
        $select = Select::metamorphoseAssociation($object->getReflectionClass(), $conditions);
      }
      catch(NotFoundDataException $e){
        throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
      }
      // Исключение проверяемого объекта из списка дублирующих.
      if($object->isOID()){
        $select->insertWhere(new Where(new MultiCondition($select->getWhere()->getCondition(), 'AND', new LogicOperation(Join::getPKField($object->getReflectionClass()), '!=', $object->getOID()))));
      }
      $queryResult = $this->do->objectQuery($select);

      return ($queryResult->rowCount() != 0)? true : false;
    }

    return false;
  }

  /**
   * Метод добавляет объект в базу данных одновременно устанавливая для него текущий идентификатор.
   * @param \D\library\patterns\entity\persistent\LongObject $object Добавляемый объект.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если сохранение объекта приведет к дублированию уникальных свойств.
   * @throws \D\library\patterns\structure\identification\OIDException Выбрасывается в случае невозможности генерации нового идентификатора.
   * @return string Установленный идентификатор.
   */
  public function insert(LongObject &$object){
    if($this->isDuplicate($object)){
      throw new DuplicationException('Невозможно сохранить персистентный объект [' . get_class($object) . '] из за дублирования.');
    }
    $newOID = $this->OIDGenerator->generateOID();
    try{
      $inserts = Insert::metamorphose($object, $newOID);
    }
    catch(NotFoundDataException $e){
      throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
    }
    $this->do->multiObjectQuery($inserts);
    // Установка идентификатора
    $object->setOID($newOID);

    return $newOID;
  }

  /**
   * Метод обновляет данные о состоянии объекта к базе данных.
   * @param \D\library\patterns\entity\persistent\LongObject $object Добавляемый объект.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если сохранение объекта приведет к дублированию уникальных свойств.
   */
  public function update(LongObject $object){
    if($this->isDuplicate($object)){
      throw new DuplicationException('Невозможно сохранить персистентный объект [' . get_class($object) . '] из за дублирования.');
    }
    try{
      $updates = Update::metamorphose($object);
    }
    catch(NotFoundDataException $e){
      throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
    }
    $this->do->multiObjectQuery($updates);
    // Исключение из кэша
    if(!is_null($this->objectCache)){
      $this->objectCache->remove(get_class($object), $object->getOID());
    }
  }

  /**
   * Метод удаляет данные из базы данных.
   * Если одно или несколько свойств объекта, определяющих множественную ассоциацию аннотированы композитным маркером, то объекты, ассоциированны с данным классом, будут так же удалены.
   * @param \D\library\patterns\entity\persistent\LongObject $object Удаляемый объект.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   */
  public function delete(LongObject $object){
    try{
      $deletes = Delete::metamorphose($object);
    }
    catch(NotFoundDataException $e){
      throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
    }
    // Удаление композита
    $reflectionProperties = $object->getAllReflectionProperties();
    /**
     * @var ReflectionProperty $property
     */
    foreach($reflectionProperties as $property){
      // Работа только со свойствами, имеющими соответствующие аннотации
      if($property->hasMetadata(self::ORM_ASSOC_CLASS) && $property->hasMetadata(self::ORM_ASSOC_FK) && $property->hasMetadata(self::ORM_COMPOSITION)){
        /**
         * @var Reflect $reflectionAssocCLass
         */
        $reflectionAssocCLass = $property->getMetadata(self::ORM_ASSOC_CLASS);
        $reflectionAssocCLass = $reflectionAssocCLass::getReflectionClass();
        $components = $this->recoverGroupFinding($reflectionAssocCLass, [[$property->getMetadata(self::ORM_ASSOC_FK), '=', $object]]);
        foreach($components as $component){
          $this->delete($component);
        }
      }
    }
    $this->do->multiObjectQuery($deletes);
    // Исключение из кэша
    if(!is_null($this->objectCache)){
      $this->objectCache->remove(get_class($object), $object->getOID());
    }
  }

  /**
   * Метод восстанавливает состояние объекта из базы данных.
   * @param \D\library\patterns\entity\persistent\LongObject $object Востанавливаемый объект.
   * @throws \D\library\resources\storage\database\ORM\UncertaintyException Выбрасывается в случае, если результатом запроса является множество записей или ни одной записи.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   */
  public function recover(LongObject &$object){
    $className = $object->getReflectionClass()->getName();
    if(!is_null($this->objectCache) && !is_null($state = $this->objectCache->get($className, $object->getOID()))){
      // Востановление из кэша.
      $this->setStateObject($object, $state);
    }
    else{
      // Восстановление из базы данных.
      try{
        $select = Select::metamorphose($object);
      }
      catch(NotFoundDataException $e){
        throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
      }
      $queryResult = $this->do->objectQuery($select);
      if($queryResult->rowCount() != 1){
        throw new UncertaintyException('Запрашиваемое состояние объекта [' . get_class($object) . ':' . $object->getOID() . '] не найдено в базе данных или результат неоднозначен. Восстановление невозможно.');
      }
      // Восстановление объекта
      $queryResult = $queryResult->fetch(\PDO::FETCH_ASSOC);
      $this->setStateObject($object, $queryResult);
      // Кэширование
      if($object->getReflectionClass()->hasMetadata(self::CACHE_CACHE)){
        if(!is_null($this->objectCache)){
          $this->objectCache->add($className, $object->getOID(), $queryResult);
        }
      }
    }
  }

  /**
   * Метод восстанавливает состояние объекта из базы данных производя поиск состояния на основании массива требуемых значений.
   * @param \D\library\patterns\entity\persistent\LongObject $object Восстанавливаемый, не идентифицированный объект.
   * @param mixed[] $conditions Ассоциативный массив, определяющий условие отбора. Массив имеет следующую структуру: [[имяСвойства, оператор, значение], ...].
   * @throws \D\library\resources\storage\database\ORM\UncertaintyException Выбрасывается в случае, если результатом запроса является множество записей или ни одной записи.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   */
  public function recoverFinding(LongObject &$object, array $conditions){
    $className = $object->getReflectionClass()->getName();
    if(count($state = $this->objectCache->find($className, $conditions)) == 1){
      $state['OID'] = key($state);
      // Востановление из кэша.
      $this->setStateObject($object, $state);
    }
    else{
      // Восстановление из базы данных.
      try{
        $select = Select::metamorphoseAssociation($object->getReflectionClass(), $conditions);
      }
      catch(NotFoundDataException $e){
        throw new EntityException('Недопустимая структура сущности [' . $object->getOID() . ']. Обработка невозможна.', 1, $e);
      }
      //Дальнейший выброс исключений не предполагается
      // Выполнение запроса
      $queryResult = $this->do->objectQuery($select);
      if($queryResult->rowCount() != 1){
        throw new UncertaintyException('Запрашиваемое состояние объекта [' . get_class($object) . ':' . $object->getOID() . '] не найдено в базе данных или результат неоднозначен. Восстановление невозможно.');
      }
      // Восстановление объекта
      $queryResult = $queryResult->fetch(\PDO::FETCH_ASSOC);
      $this->setStateObject($object, $queryResult);
      // Кэширование
      if($object->getReflectionClass()->hasMetadata(self::CACHE_CACHE)){
        if(!is_null($this->objectCache)){
          $this->objectCache->add($className, $object->getOID(), $queryResult);
        }
      }
    }
  }

  /**
   * Метод восстанавливает множество объектов согласно массиву требований к значениям их свойств.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $reflectionClass Восстанавливаемый класс объектов.
   * @param mixed[] $conditions Массив требований к значениям свойств.
   * @throws \D\library\resources\storage\database\ORM\UncertaintyException Выбрасывается в случае, если результатом запроса является множество записей или ни одной записи.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   * @throws \D\library\resources\storage\database\ORM\EntityException Выбрасывается в случае передачи недопустимого для работы объекта.
   * @return \D\library\patterns\entity\persistent\LongObject[] Ассоциативный массив восстановленных согласно условию отбора объектов. Ключами массива являются идентификаторы объектов. Пустой массив если объектов не найдено.
   */
  public function recoverGroupFinding(ReflectionClass $reflectionClass, array $conditions){
    try{
      $select = Select::metamorphoseAssociation($reflectionClass, $conditions);
    }
    catch(NotFoundDataException $e){
      throw new EntityException('Недопустимая структура класса сущности [' . $reflectionClass->getName() . ']. Обработка невозможна.', 1, $e);
    }
    $queryResult = $this->do->objectQuery($select);
    // Формирование массива объектов
    $result = [];
    /**
     * @var LongObject $className
     */
    $className = $reflectionClass->getName();
    while($row = $queryResult->fetch(DDO::FETCH_ASSOC)){
      $object = $className::getProxy($row['OID']);
      $this->setStateObject($object, $row);
      // Кэширование
      if($reflectionClass->hasMetadata(self::CACHE_CACHE)){
        $this->objectCache->add($className, $row['OID'], $row);
      }
      $result[$row['OID']] = $object;
    }

    return $result;
  }

  /**
   * Метод восстанавливает множественную ассоциацию.
   * @param \D\library\patterns\entity\persistent\LongAssociation $assoc Восстанавливаемая ассоциация.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае, если запрос к БД выполнен с ошибкой.
   */
  public function recoverAssoc(LongAssociation &$assoc){
    // Формирование SQL и выполнение запроса
    $queryResult = $this->do->objectQuery($assoc->getSelectQuery());
    // Восстановление ассоциации
    $assoc->removeAll($assoc);
    /**
     * @var LongObject $className
     */
    $className = $assoc->getAssocClass()->getName();
    while($row = $queryResult->fetch(DDO::FETCH_ASSOC)){
      /**
       * @var LongObject $object
       */
      $object = $className::getProxy($row['OID']);
      $this->setStateObject($object, $row);
      // Кэширование
      if($object->getReflectionClass()->hasMetadata(self::CACHE_CACHE)){
        $this->objectCache->add($className, $row['OID'], $row);
      }
      $assoc->attach($object);
    }
  }
}