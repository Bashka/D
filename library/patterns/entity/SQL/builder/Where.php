<?php
namespace D\library\patterns\entity\SQL\builder;

use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\condition\MultiCondition;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\patterns\entity\SQL\operators\DML\components as components;

/**
 * Класс представляет фабрику объектного SQL компонента Where.
 * @author  Artur Sh. Mamedbekov
 */
class Where implements Singleton{
  use TSingleton;

  /**
   * @var \SplStack Стек логических выражений.
   */
  protected $conditions;

  private function __construct(){
    $this->conditions = new \SplStack;
  }

  /**
   * Метод формирует объектный SQL компонент условия.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand [optional] Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Добавляет информацию о целевой таблице поля, если в строке присутствует точка. Если параметр не задан, значит значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\INLogicOperation|\D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation Объектный SQL компонент условия
   */
  public static function createCondition($leftOperand, $operator, $rightOperand = null){
    InvalidArgumentException::verify($leftOperand, 's', [1]);
    InvalidArgumentException::verify($operator, 's', [1]);
    if(array_search($operator, ['>', '<', '>=', '<=', '=', '!=', 'in']) === false){
      throw InvalidArgumentException::getValidException('>|<|(>=)|(<=)|=|(!=)|(in)', $operator);
    }
    InvalidArgumentException::verify($rightOperand, 'san', [1]);
    // Формирование левого операнда.
    if(strpos($leftOperand, '.') !== false){
      $leftOperand = explode('.', $leftOperand);
      $table = new Table($leftOperand[0]);
      $leftOperand = new Field($leftOperand[1]);
      $leftOperand->setTable($table);
    }
    else{
      $leftOperand = new Field($leftOperand);
    }
    // Формирование правого операнда.
    if(is_null($rightOperand)){
      return new LogicOperation($leftOperand, $operator);
    }
    if($rightOperand[0] == '`' && $rightOperand[strlen($rightOperand) - 1] == '`'){
      $rightOperand = substr($rightOperand, 1, -1);
      if(strpos($rightOperand, '.') !== false){
        $rightOperand = explode('.', $rightOperand);
        $table = new Table($rightOperand[0]);
        $rightOperand = new Field($rightOperand[1]);
        $rightOperand->setTable($table);
      }
      else{
        $rightOperand = new Field($rightOperand);
      }
    }
    if($operator == 'in'){
      InvalidArgumentException::verify($rightOperand, 'a', [1]);
      $inLO = new INLogicOperation($leftOperand);
      foreach($rightOperand as $value){
        $inLO->addValue($value);
      }

      return $inLO;
    }
    else{
      return new LogicOperation($leftOperand, $operator, $rightOperand);
    }
  }

  /**
   * Метод создает новое условное выражение помещая его в стек.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand [optional] Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Если параметр не задан, значит значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\builder\Where Выбываемый объект.
   */
  public function create($leftOperand, $operator, $rightOperand = null){
    $this->conditions->push(self::createCondition($leftOperand, $operator, $rightOperand));

    return $this;
  }

  /**
   * Метод создает логическое выражение из текущего логического выражения, переданного условия и оператора И.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|mixed[] $rightOperand [optional] Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Если параметр не задан, значит значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\builder\Where Выбываемый объект.
   */
  public function andC($leftOperand, $operator, $rightOperand = null){
    $this->conditions->push(new MultiCondition($this->conditions->pop(), 'AND', self::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод создает логическое выражение из текущего логического выражения, переданного условия и оператора ИЛИ.
   * @param string $leftOperand Левый операнд.
   * @param string $operator Оператор. Допустимо одно из следующих значений: >, <, >=, <=, =, !=, in.
   * @param string|array $rightOperand [optional] Правый операнд. Может быть массивом, если в качестве оператора передан in. Может быть объектным SQL компонентом Field, если значение обрамлено в косые кавычки (`). Если параметр не задан, значит значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\builder\Where Выбываемый объект.
   */
  public function orC($leftOperand, $operator, $rightOperand = null){
    $this->conditions->push(new MultiCondition($this->conditions->pop(), 'OR', self::createCondition($leftOperand, $operator, $rightOperand)));

    return $this;
  }

  /**
   * Метод объединяет текущее логическое выражение с предыдущем с помощью указанного оператора или возвращает объектный SQL компонент Where с текущим логическим выражением если парамент не передан.
   * @param string|null $operator Оператор объединения. Допустимые значения: AND, OR.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Where|\D\library\patterns\entity\SQL\builder\Where Выбываемый объект или созданный объектный SQL компонент Where.
   */
  public function last($operator = null){
    InvalidArgumentException::verify($operator, 'ns', [1]);
    if(!is_null($operator)){
      if($operator != 'AND' && $operator != 'OR'){
        throw InvalidArgumentException::getValidException('(AND)|(OR)', $operator);
      }
      $condition = $this->conditions->pop();
      $this->conditions->push(new MultiCondition($this->conditions->pop(), $operator, $condition));

      return $this;
    }
    else{
      $where = new components\Where($this->conditions->pop());
      $this->conditions = new \SplStack;

      return $where;
    }
  }

  /**
   * Метод возвращает стек логического выражения.
   * @return \SplStack Стек логического выражения.
   */
  public function getConditions(){
    return $this->conditions;
  }

  /**
   * Метод удаляет все содержимое стека логических выражений.
   */
  public function clear(){
    $this->conditions = new \SplStack;
  }
}