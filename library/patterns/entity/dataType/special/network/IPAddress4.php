<?php
namespace D\library\patterns\entity\dataType\special\network;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации IP-адреса 4 версии.
 * Допустимый тип: четыре цифры в диапазоне от 0 до 255 идущие подряд, разделеные точками.
 * @author Artur Sh. Mamedbekov
 */
class IPAddress4 extends Wrap implements IPAddress{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var integer[] Компоненты адреса.
   */
  protected $trio = [0, 0, 0, 0];

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['(' . self::getPatterns()['component'] . ')\.(' . self::getPatterns()['component'] . ')\.(' . self::getPatterns()['component'] . ')\.(' . self::getPatterns()['component'] . ')'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['component' => '(?:[0-9])|(?:[1-9][0-9])|(?:1[0-9][0-9])|(?:2[0-5][0-5])'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $o = new self($string);
    $o->trio[0] = $m[1];
    $o->trio[1] = $m[2];
    $o->trio[2] = $m[3];
    $o->trio[3] = $m[4];

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
   * Метод возвращает указанное значение компонента адреса.
   * @param integer $index Индекс компонента в диапазоне от 0 до 3.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае передаче параметра недопустимого типа.
   * @return integer Значение компонента адреса.
   */
  public function getTrio($index){
    InvalidArgumentException::verify($index, 'i', [0, 3]);

    return $this->trio[$index];
  }

  /**
   * Метод возвращает значение компонента адреса в двоичной форме.
   * @param integer $index Индекс компонента в диапазоне от 0 до 3.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае передаче параметра недопустимого типа.
   * @return string Значение компонента адреса в двоичной форме.
   */
  public function getTrioBin($index){
    return decbin($this->getTrio($index));
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
