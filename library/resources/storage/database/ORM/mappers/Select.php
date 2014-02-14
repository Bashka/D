<?php
namespace D\library\resources\storage\database\ORM\mappers;

use D\library\patterns\entity\SQL\operators\DML as DML;
use D\library\patterns\entity\SQL\operators\DML\components\FieldAlias;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\DML\components\condition\AndMultiCondition;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\persistent\LongObject;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\entity\reflection\ReflectionClass;
use D\library\patterns\structure\conversion\Metamorphosis;

/**
 * Класс восстанавливает SQL инструкцию запроса состояния персистентного объекта на основании его Proxy.
 * @author Artur Sh. Mamedbekov
 */
class Select extends DML\Select implements Metamorphosis{
  /**
   * Метод возвращает SQL инструкцию запроса состояний объектов определенного класса без условия отбора.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $mainClass Класс-основание.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Результирующая SQL инструкция.
   */
  protected static function getSelectTemplate(ReflectionClass $mainClass){
    $select = new DML\Select;
    // Определение основной таблицы
    $mainTable = Table::metamorphose($mainClass);
    $select->addTable($mainTable);
    // Формирование списка доступных для редактирования полей
    /**
     * @var Reflect $mainClassName
     */
    $mainClassName = $mainClass->getName();
    $fields = $mainClassName::getAllReflectionProperties();
    if(count($fields) == 0){
      throw new NotFoundDataException('Объект-основание ['.$mainClassName.'] не имеет ни одного связанного поля в таблице.');
    }
    // Формирование полей запроса
    $classes = [];
    foreach($fields as $fieldName => $fieldReflection){
      // Исключение не аннотированных свойств
      if(!$fieldReflection->hasMetadata(Field::ORM_FIELD_NAME)){
        continue;
      }
      /**
       * @var Reflect $declaringClassName
       */
      $declaringClassName = $fieldReflection->getDeclaringClass()->getName();
      $select->addAliasField(new FieldAlias(Field::metamorphose($declaringClassName::getReflectionClass(), $fieldName), $fieldName)); // Выброс исключений не предполагается
      if($declaringClassName != $mainClass->getName()){
        // Формирование списка родительских классов
        $classes[] = $declaringClassName;
      }
    }
    // Добавление идентификационного поля
    $OIDField = new Field($mainClass->getMetadata(Join::ORM_PK));
    $select->addAliasField(new FieldAlias($OIDField->setTable($mainTable), 'OID'));
    // Формирование списка объединений
    $classes = array_unique($classes);
    /**
     * @var Reflect $class
     */
    foreach($classes as $class){
      $select->addJoin(Join::metamorphose($mainClass, $class::getReflectionClass()));
    }

    return $select;
  }

  /**
   * Метод восстанавливает SQL инструкцию Select состояния персистентного объекта на основании его Proxy.
   * В запрос включаются только те свойства, которые анотированы как ORM\ColumnName.
   * Класс объекта должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * Класс объекта должен сопровождаться анотацией ORM\PK, хранящей имя primary key поля таблицы.
   * @param \D\library\patterns\entity\persistent\LongObject $object Исходный объект.
   * @param mixed $driver [optional] Данный аргумент не используется.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Результирующий объект.
   */
  public static function metamorphose($object, $driver = null){
    if(!is_a($object, 'D\library\patterns\entity\persistent\LongObject')){
      throw InvalidArgumentException::getTypeException('D\library\patterns\entity\persistent\LongObject', get_class($object));
    }
    if(!$object->isOID()){
      throw new NotFoundDataException('Исходный объект класса ['.get_class($object).'] не идентифицирован. Преобразование невозможно.');
    }
    $objectClassReflection = $object::getReflectionClass();
    $select = self::getSelectTemplate($objectClassReflection);
    // Формирование условия отбора
    $pkField = Join::getPKField($objectClassReflection);
    $pkField->setTable(Table::metamorphose($objectClassReflection));
    $select->insertWhere(new Where(new LogicOperation($pkField, '=', $object->getOID())));

    return $select;
  }

  /**
   * Метод восстанавливает SQL инструкцию Select состояний множества персистентных объектов на основании их класса и условий отбора.
   * В запрос включаются только те свойства, которые анотированы как ORM\ColumnName.
   * Класс должен сопровождаться анотацией ORM\Table, хранящей имя таблицы данного класса.
   * Класс должен сопровождаться анотацией ORM\PK, хранящей имя primary key поля таблицы.
   * @param \D\library\patterns\entity\reflection\ReflectionClass $assocClass Класс основание.
   * @param array $conditions [optional] Массив условий отбора, имеющий следующую структуру: [[имяСвойства, операцияСравнения, значение], ...]. В случае отсутствия данного параметра в результирующем объекте отсутствует условие отбора.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия требуемых для восстановления данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Результирующий объект.
   */
  public static function metamorphoseAssociation(ReflectionClass $assocClass, array $conditions = null){
    $select = self::getSelectTemplate($assocClass);
    if(!is_null($conditions)){
      /**
       * @var Reflect $assocClassName
       */
      $assocClassName = $assocClass->getName();
      $fields = $assocClassName::getAllReflectionProperties();
      $condition = null;
      $andCondition = new AndMultiCondition();
      foreach($conditions as $condition){
        if(!array_key_exists($condition[0], $fields)){
          throw new NotFoundDataException('Указанного поля отбора [' . $condition[0] . '] нет в классе-основании ['.$assocClassName.'].');
        }
        // Предварительная сериализация объектов
        if(($condition[2] instanceof LongObject) && $condition[2]->isOID()){
          $condition[2] = $condition[2]->interpretation();
        }
        $condition = new LogicOperation(Field::metamorphose($assocClass, $condition[0]), $condition[1], $condition[2]);
        $andCondition->addCondition($condition);
      }
      if(count($conditions) == 1){
        $where = new Where($condition);
      }
      else{
        $where = new Where($andCondition);
      }
      $select->insertWhere($where);
    }

    return $select;
  }
}