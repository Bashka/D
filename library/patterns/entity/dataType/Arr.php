<?php
namespace D\library\patterns\entity\dataType;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для верификации и представления массивов.
 * @author  Artur Sh. Mamedbekov
 */
class Arr extends Wrap{
  /**
   * @var array Оборачиваемое значение.
   */
  private $val;

  /**
   * Допустимый тип: любая строка.
   * @prototype \PPHP\tools\patterns\interpreter\Restorable
   */
  public static function reestablish($string, $driver = null){
    InvalidArgumentException::verify($string, 's');

    return new self([$string]);
  }

  /**
   * Метод проверяет, имеет ли параметр тип array.
   * @param mixed $val Проверяемое значение.
   * @param string $type [optional] Допустимый тип элементов массива.
   * @return boolean true - если параметр допустимого типа, иначе - false.
   */
  public static function hasType($val, $type = 'mixed'){
    assert('$type == "string" || $type == "integer" || $type == "float" || $type == "boolean" || $type == "mixed" || $type == "object" || $type == "array" || $type == "resource"');
    if(!is_array($val)){
      return false;
    }
    // Строгая типизация массива.
    if($type != 'mixed'){
      // Переименование float в double для сквозной проверки.
      if($type == 'float'){
        $type = 'double';
      }
      foreach($val as $element){
        if(gettype($element) != $type){
          return false;
        }
      }
      return true;
    }
    // Динамическая типизация массива.
    else{
      return true;
    }
  }

  /**
   * Метод определяет, имеет ли массив допустимый размер.
   * @param array $val Проверяемое значение.
   * @param integer $min Минимальное число элементов массива.
   * @param integer $max [optional] Максимальное число элементов массива.
   * @return boolean true - если массив имеет допустимый размер, иначе - false.
   */
  public static function hasLength(array $val, $min, $max = null){
    assert('is_integer($min) && $min >= 0');
    assert('is_null($max) || (is_integer($max) && $max > $min)');
    $length = count($val);
    if(is_null($max)){
      return ($length >= $min);
    }
    else{
      return ($length >= $min && $length <= $max);
    }
  }

  /**
   * @param array $val Оборачиваемое значение.
   */
  public function __construct(array $val){
    $this->val = $val;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return array Оборачиваемое значение.
   */
  public function getVal(){
    assert('is_array($this->val)');
    return $this->val;
  }
} 