<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Класс представляет условие сортировки результата запроса.
 * @author Artur Sh. Mamedbekov
 */
class OrderBy extends ComponentQuery{
  /**
   * Маркер для сортировки по возрастанию.
   */
  const ASC = 'ASC';

  /**
   * Маркер для сортировки по убыванию.
   */
  const DESC = 'DESC';

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\components\Field[] Используемые в сортировке поля.
   */
  private $fields;

  /**
   * @var string Способ сортировки.
   */
  private $sortedType;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['ORDER BY (?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . '))(?:, ?(?:(?:' . Field::getMasks()[0] . ')|(?:' . Field::getMasks()[1] . ')))* ' . self::getPatterns()['types']];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['types' => '(?:ASC|DESC)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    $type = trim(substr($string, -4));
    $orderBy = new OrderBy($type);
    $fields = explode(',', substr(substr($string, 9), 0, -4));
    foreach($fields as $field){
      $field = trim($field);
      assert('\D\library\patterns\entity\SQL\operators\DML\components\Field::isReestablish($field)');
      $orderBy->addField(Field::reestablish($field));
    }

    return $orderBy;
  }

  /**
   * @param string $sortedType [optional] Способ сортировки.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct($sortedType = self::ASC){
    if($sortedType != 'ASC' && $sortedType != 'DESC'){
      throw InvalidArgumentException::getValidException('ASC|DESC', $sortedType);
    }
    $this->fields = [];
    $this->sortedType = $sortedType;
  }

  /**
   * Метод добавляет поле для сортировки.
   * @param \D\library\patterns\entity\SQL\operators\DML\components\Field $field Поле для сортировки.
   */
  public function addField(Field $field){
    $this->fields[] = $field;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);
    if(count($this->fields) == 0){
      throw new NotFoundDataException('Недостаточно данных для формирования строки [D\library\patterns\entity\SQL\operators\DML\components\OrderBy].');
    }
    $result = 'ORDER BY ';
    foreach($this->fields as $field){
      $result .= $field->interpretation($driver) . ',';
    }

    return substr($result, 0, strlen($result) - 1) . ' ' . $this->sortedType;
  }

  /**
   * Метод возвращает массив используемых в сортировке полей.
   * @return \D\library\patterns\entity\SQL\operators\DML\components\Field[] Массив используемых в сортировке полей.
   */
  public function getFields(){
    return $this->fields;
  }

  /**
   * Метод возвращает способ сортировки.
   * @return string Способ сортировки.
   */
  public function getSortedType(){
    return $this->sortedType;
  }
}
