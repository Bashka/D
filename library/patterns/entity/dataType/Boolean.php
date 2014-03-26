<?php
namespace D\library\patterns\entity\dataType;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для верификации и представления логических данных.
 * @author  Artur Sh. Mamedbekov
 */
class Boolean extends Wrap{
  /**
   * @var boolean Оборачиваемое значение.
   */
  private $val;

  /**
   * @prototype \PPHP\tools\patterns\interpreter\TRestorable
   */
  public static function getMasks($driver = null){
    return ['((?:true)|(?:false))'];
  }

  /**
   * Допустимый тип: 'true' (эквивалентно true), 'false' (эквивалентно false).
   * @prototype \PPHP\tools\patterns\interpreter\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    if($string == 'true'){
      return new self(true);
    }
    else{
      return new self(false);
    }
  }

  /**
   * Метод проверяет, имеет ли параметр тип boolean.
   * @param mixed $val Проверяемое значение.
   * @return boolean true - если параметр типа boolean, иначе - false.
   */
  public static function hasType($val){
    return is_bool($val);
  }

  /**
   * @param boolean $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_bool($val)){
      throw InvalidArgumentException::getTypeException('boolean', gettype($val));
    }
    $this->val = $val;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return boolean Оборачиваемое значение.
   */
  public function getVal(){
    assert('is_bool($this->val)');
    return $this->val;
  }
}