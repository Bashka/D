<?php
namespace D\library\patterns\entity\dataType\special\system;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации GUID.
 * Допустимый тип: соответствие требованиям формата GUID v.4 - xxxxxxxx-xxxx-4xxx-[89AaBb]xxx-xxxxxxxxxxxx.
 * @author Artur Sh. Mamedbekov
 */
class GUID4 extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-4[0-9a-fA-F]{3}\-[89AaBb][0-9a-fA-F]{3}\-[0-9a-fA-F]{12}'];
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
   * Метод генерирует и возвращает GUID v.4.
   * @author Jack http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
   * @return \D\library\patterns\entity\dataType\special\system\GUID4 GUID v.4.
   */
  public static function generate(){
    $data = openssl_random_pseudo_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
  }

  /**
   * @param string $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_string($val)){
      throw InvalidArgumentException::getTypeException('name', gettype($val));
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