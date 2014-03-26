<?php
namespace D\library\patterns\entity\dataType\special\people;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации телефонных номеров.
 * Допустимый тип: символ + за которым следует числовая последовательность, за которой следует открывающая скобка, числовая последовательность и закрывающая скобка, за которой следует числовая последовательность.
 * @author Artur Sh. Mamedbekov
 */
class PhoneNumber extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var string Код региона.
   */
  protected $region;

  /**
   * @var string Код города.
   */
  protected $code;

  /**
   * @var string Номер.
   */
  protected $number;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['\+([1-9][0-9]*)\(([0-9]+)\)([0-9]+)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $o = new self($string);
    $o->region = $m[1];
    $o->code = $m[2];
    $o->number = $m[3];

    return $o;
  }

  /**
   * @param boolean $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_string($val)){
      throw InvalidArgumentException::getTypeException('string', gettype($val));
    }
    $this->val = $val;
  }

  /**
   * Метод возвращает код города.
   * @return string Код города.
   */
  public function getCode(){
    return $this->code;
  }

  /**
   * Метод возвращает номер.
   * @return string Номер.
   */
  public function getNumber(){
    return $this->number;
  }

  /**
   * Метод возвращает код региона.
   * @return string Код региона.
   */
  public function getRegion(){
    return $this->region;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
