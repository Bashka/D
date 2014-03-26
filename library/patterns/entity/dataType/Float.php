<?php
namespace D\library\patterns\entity\dataType;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для верификации и представления дробных чисел.
 * @author  Artur Sh. Mamedbekov
 */
class Float extends Wrap{
  /**
   * @var float Оборачиваемое значение.
   */
  private $val;

  /**
   * @prototype \PPHP\tools\patterns\interpreter\TRestorable
   */
  public static function getMasks($driver = null){
    return ['-?(?:(?:0)|(?:[1-9]))([0-9]*)(.[0-9]+)'];
  }

  /**
   * Допустимый тип: строка, содержащая только цифры, дробную точку с цифрами на месте дробной части или/и ведущий символ минуса.
   * @prototype \PPHP\tools\patterns\interpreter\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);

    return new self((float) $string);
  }

  /**
   * Метод проверяет, имеет ли параметр тип float.
   * @param mixed $val Проверяемое значение.
   * @return boolean true - если параметр допустимого типа, иначе - false.
   */
  public static function hasType($val){
    return is_double($val);
  }

  /**
   * Метод определяет, входит ли число в допустимый диапазон.
   * @param float $val Проверяемое значение.
   * @param integer|float $min Нижняя граница диапазона.
   * @param integer|float $max [optional] Верхняя граница диапазона.
   * @return boolean true - если число входит в допустимый диапазон, иначе - false.
   */
  public static function hasLength($val, $min, $max = null){
    assert('is_double($val)');
    assert('is_double($min) || is_integer($min)');
    assert('is_null($max) || ((is_double($max) || is_integer($max)) && $max > $min)');
    if(is_null($max)){
      return ($val >= $min);
    }
    else{
      return ($val >= $min && $val <= $max);
    }
  }

  /**
   * @param float $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_double($val)){
      throw InvalidArgumentException::getTypeException('float', gettype($val));
    }
    $this->val = $val;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return float Оборачиваемое значение.
   */
  public function getVal(){
    assert('is_double($this->val)');
    return $this->val;
  }
} 