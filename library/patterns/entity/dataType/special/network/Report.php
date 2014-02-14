<?php
namespace D\library\patterns\entity\dataType\special\network;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации имен протоколов обмена данными.
 * Допустимый тип: имя одного из доступных протоколов за которым следует последовательность ://
 * @author Artur Sh. Mamedbekov
 */
class Report extends Wrap{
  /**
   * Протокол HTTP.
   */
  const HTTP = 'http';

  /**
   * Протокол HTTPS.
   */
  const HTTPS = 'https';

  /**
   * Протокол FTP.
   */
  const FTP = 'ftp';

  /**
   * Протокол DNS.
   */
  const DNS = 'dns';

  /**
   * Протокол SSH.
   */
  const SSH = 'ssh';

  /**
   * Протокол POP3.
   */
  const POP3 = 'pop3';

  /**
   * Протокол SMTP.
   */
  const SMTP = 'smtp';

  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var string Имя протокола.
   */
  protected $name;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['(' . self::HTTP . '|' . self::HTTPS . '|' . self::FTP . '|' . self::DNS . '|' . self::SSH . '|' . self::POP3 . '|' . self::SMTP . '):\/\/'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $o = new self($string);
    $o->name = $m[1];

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
   * Метод возвращает имя протокола.
   * @return string Имя протокола.
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
