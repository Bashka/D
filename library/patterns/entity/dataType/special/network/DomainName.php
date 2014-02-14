<?php
namespace D\library\patterns\entity\dataType\special\network;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации доменных имен.
 * Допустимый тип: должно начинаться латинской буквой или цифрой, а заканчиваться буквой, цифрой или знаком тире. Может содержать точки, но не идущие подряд и обязательно обрамленые знаком тире, латинской буквой или цифрой.
 * @author Artur Sh. Mamedbekov
 */
class DomainName extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;
  
  /**
   * @var string[] Компоненты адреса.
   */
  protected $subDomains = [];

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['[A-Za-z0-9][A-Za-z0-9-]*(?:(?:\.[A-Za-z0-9-]+)*|\.)[A-Za-z0-9]'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    $o = new self($string);
    $o->subDomains = array_reverse(explode('.', $string));

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
   * @param integer $index Индекс компонента в диапазоне от 0 до порядкового номера поддомена.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае передаче параметра недопустимого типа.
   * @return string Значение компонента адреса.
   */
  public function getComponent($index){
    InvalidArgumentException::verify($index, 'i', [0, count($this->subDomains)]);

    return $this->subDomains[$index];
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
