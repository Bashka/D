<?php
namespace D\library\patterns\entity\SQL\operators\DML;

use D\library\patterns\entity\SQL\operators\ComponentQuery;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\FieldAlias;
use D\library\patterns\entity\SQL\operators\DML\components\Join;
use D\library\patterns\entity\SQL\operators\DML\components\Limit;
use D\library\patterns\entity\SQL\operators\DML\components\OrderBy;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\patterns\entity\SQL\operators\Operator;
use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет объектную SQL инструкцию для получение записей из таблицы.
 * Объекты данного класса могут быть восстановлены из строки следующего формата: SELECT (имяПоля|имяТаблицы.имяПоля, ...)|* FROM имяТаблицы, ... [типОбъединения JOIN имяТаблицы ON логическоеВыражение, ...] [ORDER BY имяПоля|имяТаблицы.имяПоля ASC|DESC] [LIMIT числоСтрок] [WHERE (логическоеВыражение)].
 * @author Artur Sh. Mamedbekov
 */
class Select extends ComponentQuery implements Operator{
  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Field[]|\D\library\patterns\entity\SQL\operators\DML\components\FieldAlias[] Множество запрашиваемых полей.
   */
  private $fields;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Table[] Множество таблиц, используемых в запросе.
   */
  private $tables;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Join[] Множество соединений.
   */
  private $joins;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Where Условие отбора записей.
   */
  private $where;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\OrderBy Сортировка записей.
   */
  private $orderBy;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Limit Ограничитель выборки.
   */
  private $limit;

  /**
   * @var boolean Логический флаг, свидетельствующий о том, что должны быть выбраны все поля.
   */
  private $allField;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['SELECT ((?:' . self::getPatterns()['fieldGroup'] . ')|(?:\*)) FROM (' . self::getPatterns()['tableGroup'] . ')((?: ' . Join::getMasks()[0] . ')*)(?: (' . OrderBy::getMasks()[0] . '))?(?: (' . Limit::getMasks()[0] . '))?(?: (' . Where::getMasks()[0] . '))?'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['fieldGroup' => '(?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')|(?:' . FieldAlias::getMasks()[0] . '))(?:, ?(?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')))*', 'tableGroup' => '(?:' . Table::getMasks()[0] . ')(?:, ?' . Table::getMasks()[0] . ')*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $select = new self;
    if($m[1] == '*'){
      $select->addAllField();
    }
    else{
      $fields = explode(',', $m[1]);
      foreach($fields as $field){
        $field = trim($field);
        if(strpos($field, ' AS ') === false){
          $select->addField(Field::reestablish($field));
        }
        else{
          $select->addAliasField(FieldAlias::reestablish($field));
        }
      }
    }
    $tables = explode(',', str_replace(' ', '', $m[2]));
    foreach($tables as $table){
      $select->addTable(Table::reestablish($table));
    }
    if(!empty($m[3])){
      // Разбор join компонентов на части по пробелу и склеивание их по средствам нахождения ключевых модификаторов типа join.
      // Не следует использовать ключевый модификаторы join (CROSS|INNER|LEFT|RIGHT|FULL) в условиях запроса!
      $joinComponents = explode(' ', trim($m[3]));
      $join = null;
      foreach($joinComponents as $component){
        if(preg_match('/^(' . Join::getPatterns()['types'] . ')$/u', $component)){
          if(!is_null($join)){
            $select->addJoin(Join::reestablish(trim($join)));
          }
          $join = $component . ' ';
        }
        else{
          $join .= $component . ' ';
        }
      }
      $select->addJoin(Join::reestablish(trim($join)));
    }
    if(!empty($m[7])){
      $select->insertOrderBy(OrderBy::reestablish($m[7]));
    }
    if(!empty($m[8])){
      $select->insertLimit(Limit::reestablish($m[8]));
    }
    if(!empty($m[12])){
      $select->insertWhere(Where::reestablish($m[12]));
    }

    return $select;
  }

  function __construct(){
    $this->fields = [];
    $this->tables = [];
    $this->joins = [];
    $this->allField = false;
  }

  /**
   * Метод добавляет поле в запрос.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $field Добавляемое поле.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если указанное поле уже присутствует в запросе.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function addField(Field $field){
    if(array_search($field, $this->fields) !== false){
      throw new DuplicationException('Ошибка дублирования компонента [D\library\patterns\entity\SQL\operators\DML\components\Field] в SQL инструкции [D\library\patterns\entity\SQL\operators\DML\Select].');
    }
    $this->fields[] = $field;

    return $this;
  }

  /**
   * Метод добавляет поле с алиасом в запрос.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\FieldAlias $field Добавляемое поле.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если указанное поле уже присутствует в запросе.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function addAliasField(FieldAlias $field){
    if(array_search($field, $this->fields) !== false){
      throw new DuplicationException('Ошибка дублирования компонента [D\library\patterns\entity\SQL\operators\DML\components\FieldAlias] в SQL инструкции [D\library\patterns\entity\SQL\operators\DML\Select].');
    }
    $this->fields[] = $field;

    return $this;
  }

  /**
   * Метод добавляет таблицу в запрос.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Table $table Добавляемая таблица.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если указанная таблица уже присутствует в запросе.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function addTable(Table $table){
    if(array_search($table, $this->tables) !== false){
      throw new DuplicationException('Ошибка дублирования компонента [D\library\patterns\entity\SQL\operators\DML\components\Table] в SQL инструкции [D\library\patterns\entity\SQL\operators\DML\Select].');
    }
    $this->tables[] = $table;

    return $this;
  }

  /**
   * Метод добавляет соединение в запрос.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Join $join Добавляемое соединение.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если указанное соединение уже присутствует в запросе.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function addJoin(Join $join){
    if(array_search($join, $this->joins) !== false){
      throw new DuplicationException('Ошибка дублирования компонента [D\library\patterns\entity\SQL\operators\DML\components\Join] в SQL инструкции [D\library\patterns\entity\SQL\operators\DML\Select].');
    }
    $this->joins[] = $join;

    return $this;
  }

  /**
   * Метод устанавливает условие отбора для запроса.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Where $where Условие отбора.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function insertWhere(Where $where){
    $this->where = $where;

    return $this;
  }

  /**
   * Метод определяет порядок сортировки для запроса.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\OrderBy $orderBy Способ сортировки.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function insertOrderBy(OrderBy $orderBy){
    $this->orderBy = $orderBy;

    return $this;
  }

  /**
   * Метод определяет ограничение выборки.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Limit $limit Ограничение выборки.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function insertLimit(Limit $limit){
    $this->limit = $limit;

    return $this;
  }

  /**
   * Метод устанавливает флаг отбора всех полей.
   * @return \D\library\patterns\entity\SQL\operators\DML\Select Метод возвращает вызываемый объект.
   */
  public function addAllField(){
    $this->allField = true;

    return $this;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);
    if((count($this->fields) == 0 && !$this->allField) || count($this->tables) == 0){
      throw new NotFoundDataException('Недостаточно данных для интерпретации [D\library\patterns\entity\SQL\operators\DML\Select].');
    }
    if($this->allField){
      $fieldsString = '*';
    }
    else{
      $fieldsString = '';
      foreach($this->fields as $field){
        $fieldsString .= $field->interpretation($driver) . ',';
      }
      $fieldsString = substr($fieldsString, 0, -1);
    }
    $tableString = '';
    foreach($this->tables as $table){
      $tableString .= '' . $table->interpretation($driver) . ',';
    }
    $tableString = substr($tableString, 0, -1);
    $joinString = [];
    foreach($this->joins as $join){
      $joinString[] = $join->interpretation($driver);
    }
    $joinString = implode(' ', $joinString);
    $whereString = (is_object($this->where)? $this->where->interpretation($driver) : '');
    $orderByString = (is_object($this->orderBy)? $this->orderBy->interpretation($driver) : '');
    // Формирование платформо-независимой выборки при отсутствии несовместимых элементов.
    if(empty($this->limit)){
      return trim('SELECT ' . $fieldsString . ' FROM ' . $tableString . ' ' . $joinString . ' ' . $whereString . ' ' . $orderByString);
    }
    // Формирования платформо-зависимой выборки при наличии несовместимых элементов.
    else{
      InvalidArgumentException::verify($driver, 's', [1]);
      // Обработка LIMIT элемента
      $limitString = $this->limit->interpretation($driver);
      $staticPartString = $fieldsString . ' FROM ' . $tableString . ' ' . $joinString . ' ' . $whereString . ' ';
      switch($driver){
        case 'sqlsrv': // MS SQL Server
        case 'firebird': // Firebird
          return trim('SELECT ' . $limitString . $staticPartString . $orderByString);
        case 'oci': // Oracle
          return trim('SELECT ' . $staticPartString . ' AND (' . $limitString . ') ' . $orderByString);
        case 'mysql': // MySQL
        case 'pgsql': // PostgreSQL
        case 'ibm': // DB2
          return trim('SELECT ' . $staticPartString . $orderByString . ' ' . $limitString);
        default:
          throw InvalidArgumentException::getValidException('sqlsrv|firebird|oci|mysql|pgsql|ibm', $driver);
      }
    }
  }

  /**
   * Метод возвращает массив полей данного запроса или пустой массив, если выбраны все поля.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field[]|\D\library\patterns\entity\SQL\operators\DML\components\FieldAlias[]
   */
  public function getFields(){
    if($this->allField){
      return [];
    }

    return $this->fields;
  }

  /**
   * Метод возвращает массив объединений.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Join[] Массив объединений.
   */
  public function getJoins(){
    return $this->joins;
  }

  /**
   * Метод возвращает ограничитель выборки.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Limit Ограничитель выборки.
   */
  public function getLimit(){
    return $this->limit;
  }

  /**
   * Метод возвращает метод сортировки записей.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\OrderBy Метод сортировки записей.
   */
  public function getOrderBy(){
    return $this->orderBy;
  }

  /**
   * Метод возвращает массив целевых таблиц.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Table[] Массив целевых таблиц.
   */
  public function getTables(){
    return $this->tables;
  }

  /**
   * Метод возвращает условие отбора.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Where Условие отбора.
   */
  public function getWhere(){
    return $this->where;
  }

  /**
   * Определяет, производится ли выборка всех полей.
   * @return boolean true - если производится выборка всех полей, иначе - false.
   */
  public function isAllFields(){
    return $this->allField;
  }
}
