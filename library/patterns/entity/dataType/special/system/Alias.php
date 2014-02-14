<?php
namespace D\library\patterns\entity\dataType\special\system;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации описательных имен.
 * Допустимый тип: только латинские буквы любого регистра, знак подчеркивания, пробел и цифры, но не на месте первого символа.
 * @author Artur Sh. Mamedbekov
 */
class Alias extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['[A-Za-z_][A-Za-z_0-9 ]*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);

    return new self($string);
  }

  /**
   * @param string $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_string($val)){
      throw InvalidArgumentException::getTypeException('string', gettype($val));
    }
    $this->val = $val;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    assert('is_string($this->val)');
    return $this->val;
  }
} 