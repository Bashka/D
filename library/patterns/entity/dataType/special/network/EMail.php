<?php
namespace D\library\patterns\entity\dataType\special\network;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации адресов электронной почты.
 * Допустимый тип: только латинские буквы, цифры, знак подчеркивания и тире, за которым следует знак @ за которым следует доменное имя.
 * @author Artur Sh. Mamedbekov
 */
class EMail extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var string Локальное имя пользователя электронной почты.
   */
  protected $local;

  /**
   * @var \D\library\patterns\entity\dataType\special\network\DomainName Домен электронной почты.
   */
  protected $domain;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['([A-Za-z0-9_-]+)@(' . DomainName::getMasks()[0] . ')'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $o = new self($string);
    $o->local = $m[1];
    $o->domain = DomainName::reestablish($m[2]);

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
   * Метод возвращает домен электронной почты.
   * @return \D\library\patterns\entity\dataType\special\network\DomainName Домен электронной почты.
   */
  public function getDomain(){
    return $this->domain;
  }

  /**
   * Метод возвращает локальное имя пользователя электронной почты.
   * @return string Локальное имя пользователя электронной почты.
   */
  public function getLocal(){
    return $this->local;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
