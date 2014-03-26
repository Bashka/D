<?php
namespace D\library\patterns\entity\SQL\builder;

use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Join;
use D\library\patterns\entity\SQL\operators\DML\components\Limit;
use D\library\patterns\entity\SQL\operators\DML\components\OrderBy;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\patterns\entity\SQL\operators\DML as DML;

/**
 * Класс представляет фабрику объектной SQL инструкции Select.
 * @author  Artur Sh. Mamedbekov
 */
class Select implements Singleton, Interpreter{
  use TSingleton;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\Select Объектная SQL инструкция Select.
   */
  protected $select;

  /**
   * @var \D\library\patterns\entity\SQL\builder\Where|null Фабрика Where, являющаяся частью запроса, или null - если запрос не имеет условия.
   */
  protected $where;

  /**
   * Метод определяет поля запроса.
   * @param string[]|null $fields [optional] Массив имен полей запроса. Если передан ассоциативный массив, то ключи определяют целевые таблицы полей. Если параметр не передан, выбираются все поля.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function fields(array $fields = null){
    if(empty($this->select)){
      $this->select = new DML\Select;
    }
    if(is_null($fields)){
      $this->select->addAllField();
    }
    else{
      foreach($fields as $table => $field){
        $field = new Field($field);
        if(is_string($table)){
          $field->setTable(new Table($table));
        }
        $this->select->addField($field);
      }
    }

    return $this;
  }

  /**
   * Метод определяет целевые таблицы запроса.
   * @param string[] $tables Список имен целевых таблиц запроса.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function tables(array $tables){
    if(empty($this->select)){
      $this->select = new DML\Select;
    }
    foreach($tables as $table){
      $this->select->addTable(new Table($table));
    }

    return $this;
  }

  /**
   * Метод добавляет объединение типа INNER.
   * @param string $table Объединяемая таблица.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function innerJoin($table, $leftOperand, $operator, $rightOperand){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->select->addJoin(new Join(Join::INNER, new Table($table), Where::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод добавляет объединение типа CROSS.
   * @param string $table Объединяемая таблица.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function crossJoin($table, $leftOperand, $operator, $rightOperand){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->select->addJoin(new Join(Join::CROSS, new Table($table), Where::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод добавляет объединение типа LEFT.
   * @param string $table Объединяемая таблица.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function leftJoin($table, $leftOperand, $operator, $rightOperand){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->select->addJoin(new Join(Join::LEFT, new Table($table), Where::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод добавляет объединение типа RIGHT.
   * @param string $table Объединяемая таблица.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function rightJoin($table, $leftOperand, $operator, $rightOperand){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->select->addJoin(new Join(Join::RIGHT, new Table($table), Where::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод добавляет объединение типа FULL.
   * @param string $table Объединяемая таблица.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function fullJoin($table, $leftOperand, $operator, $rightOperand){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->select->addJoin(new Join(Join::FULL, new Table($table), Where::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод добавляет компонент Limit.
   * @param integer $limit Объем выборки.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function limit($limit){
    InvalidArgumentException::verify($limit, 'i');
    $this->select->insertLimit(new Limit($limit));

    return $this;
  }

  /**
   * Метод добавляет компонент OrderBy.
   * @param string[] $fields Имена полей сортировки.
   * @param string $type [optional] Тип сортировки.
   * @return \D\library\patterns\entity\SQL\builder\Select Вызываемый объект.
   */
  public function orderBy(array $fields, $type = OrderBy::ASC){
    $ob = new OrderBy($type);
    foreach($fields as $field){
      $ob->addField(new Field($field));
    }
    $this->select->insertOrderBy($ob);

    return $this;
  }

  /**
   * Метод создает объектный SQL компонент Where для данного условия.
   * Метод должен быть вызван только после вызова метода table или fields, формирующего инструкцию.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|array $rightOperand [optional] Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Если параметр не задан, значит значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается если метод вызывается до вызова метода table или fields.
   * @return \D\library\patterns\entity\SQL\builder\Where Фабрика объектного SQL компонента Where для данной инструкции.
   */
  public function where($leftOperand, $operator, $rightOperand = null){
    if(empty($this->select)){
      throw new NotFoundDataException('Невозможно добавить условие отбора без указания целевой таблицы [D\library\patterns\entity\SQL\builder\Select].');
    }
    /**
     * @var Where $whereBuilder
     */
    $whereBuilder = Where::getInstance();
    $this->where = $whereBuilder->create($leftOperand, $operator, $rightOperand);
    $this->where->select = $this;

    return $this->where;
  }

  /**
   * Метод возвращает полученную объектную SQL инструкцию Select.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Результат работы фабрики.
   */
  public function get($driver = null){
    $select = $this->select;
    if(isset($this->where)){
      $this->select->insertWhere($this->where->last());
    }
    $this->clear();

    return $select;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    return $this->get()->interpretation($driver);
  }

  /**
   * Метод удаляет текущую конструкцию.
   */
  public function clear(){
    unset($this->select);
    unset($this->where);
  }
}