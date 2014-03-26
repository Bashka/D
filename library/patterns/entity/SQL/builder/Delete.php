<?php
namespace D\library\patterns\entity\SQL\builder;

use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\patterns\entity\SQL\operators\DML as DML;

/**
 * Класс представляет фабрику объектной SQL инструкции Delete.
 * @author  Artur Sh. Mamedbekov
 */
class Delete implements Singleton, Interpreter{
  use TSingleton;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\Delete Объектная SQL инструкция Delete.
   */
  protected $delete;

  /**
   * @var \D\library\patterns\entity\SQL\builder\Where|null Фабрика Where, являющаяся частью запроса, или null - если запрос не имеет условия.
   */
  protected $where;

  /**
   * Метод создает новую объектную SQL инструкцию Delete.
   * @param string $table Имя целевой таблицы.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\builder\Delete Вызываемый объект.
   */
  public function table($table){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->delete = new DML\Delete(new Table($table));

    return $this;
  }

  /**
   * Метод создает объектный SQL компонент Where для данного условия.
   * Метод должен быть вызван только после вызова метода table, формирующего инструкцию.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|array $rightOperand Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается если метод вызывается до вызова метода table.
   * @return \D\library\patterns\entity\SQL\builder\Where Фабрика объектного SQL компонента Where для данной инструкции.
   */
  public function where($leftOperand, $operator, $rightOperand){
    if(empty($this->delete)){
      throw new NotFoundDataException('Невозможно добавить условие отбора без указания целевой таблицы [D\library\patterns\entity\SQL\builder\Delete].');
    }
    /**
     * @var Where $whereBuilder
     */
    $whereBuilder = Where::getInstance();
    $this->where = $whereBuilder->create($leftOperand, $operator, $rightOperand);
    $this->where->delete = $this;

    return $this->where;
  }

  /**
   * Метод возвращает полученную объектную SQL инструкцию Delete.
   * @return \D\library\patterns\entity\SQL\operators\DML\Delete Результат работы фабрики.
   */
  public function get(){
    if(isset($this->where)){
      $this->delete->insertWhere($this->where->last());
      unset($this->where);
    }

    return $this->delete;
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
    unset($this->delete);
  }
}