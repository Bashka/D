<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\dataType\String;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;
use D\library\patterns\entity\SQL\operators\DML\components\condition\Condition;
use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;

/**
 * Класс представляет компонент объединения записей.
 * Объекты данного класса могут быть восстановлены из строки следующего формата: CROSS|INNER|LEFT|RIGHT|FULL JOIN  `имяТаблицы` ON (`имяПоля`|имяТаблицы.имяПоля = `имяПоля`|имяТаблицы.имяПоля)|(`имяПоля`|имяТаблицы.имяПоля = "значение").
 * @author Artur Sh. Mamedbekov
 */
class Join extends ComponentQuery{

  const CROSS = 'CROSS';

  /**
   * Исключающее объединение.
   */
  const INNER = 'INNER';

  /**
   * Левое объединение.
   */
  const LEFT = 'LEFT';

  /**
   * Правое объединение.
   */
  const RIGHT = 'RIGHT';

  /**
   * Полное объединение.
   */
  const FULL = 'FULL';

  /**
   * @var string Тип связи.
   */
  protected $type;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Table Связываемая таблица.
   */
  protected $table;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Условие связывания.
   */
  protected $condition;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    // Условие объединения ограничено одним логическим выражением.
    return [self::getPatterns()['types'] . ' JOIN (?:' . Table::getMasks()[0] . ') ON (?:(?:' . LogicOperation::getMasks()[0] . ')|(?:' . LogicOperation::getMasks()[1] . '))'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['types' => '(?:(?:' . self::CROSS . ')|(?:' . self::INNER . ')|(?:' . self::LEFT . ')|(?:' . self::RIGHT . ')|(?:' . self::FULL . '))'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    $s = new String($string);
    $type = $s->getUpTo(' ')->getVal();
    $s->jump($s->key() + 5);
    $table = $s->getUpTo(' ON ')->getVal();
    $condition = $s->get()->getVal();
    assert('\D\library\patterns\entity\SQL\operators\DML\components\Table::isReestablish($table)');

    return new static($type, Table::reestablish($table), Condition::reestablishCondition($condition));
  }

  /**
   * @param string $type Тип соединения. CROSS, INNER, LEFT, RIGHT или FULL.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Table $table Связываемая таблица.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition $condition Условие связывания.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct($type, Table $table, Condition $condition){
    if($type != 'CROSS' && $type != 'INNER' && $type != 'LEFT' && $type != 'RIGHT' && $type != 'FULL'){
      throw InvalidArgumentException::getValidException('CROSS|INNER|LEFT|RIGHT|FULL', $type);
    }
    $this->type = $type;
    $this->table = $table;
    $this->condition = $condition;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);

    return $this->type . ' JOIN ' . $this->table->interpretation($driver) . ' ON ' . $this->condition->interpretation($driver);
  }

  /**
   * Метод возвращает условие объединения.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\condition\Condition Условие объединения.
   */
  public function getCondition(){
    return $this->condition;
  }

  /**
   * Метод возвращает целевую таблицу.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Table Целевая таблица.
   */
  public function getTable(){
    return $this->table;
  }

  /**
   * Метод возвращает тип объединения.
   * @return string Тип объединения.
   */
  public function getType(){
    return $this->type;
  }
}