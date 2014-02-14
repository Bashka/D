<?php
namespace D\library\patterns\entity\dataType\special\fileSystem;

use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации адресов файлов и папок в файловой системе.
 * Допустимый тип: любые символы кроме  : * ? " < > | \0 \ и без ведущего / символа, а так же без двух и более / символов, следующих один за другим.
 * @author Artur Sh. Mamedbekov
 */
class FileSystemAddress extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var boolean Флаг абсолютного адреса. true - если адрес абсолютный, иначе - false.
   */
  protected $isRoot;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['(\/)?(?:' . FileSystemName::getPatterns()['fieldName'] . ')(?:\/' . FileSystemName::getPatterns()['fieldName'] . ')*(?:\/)?'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $o = new self($string);
    if(isset($m[1])){
      $o->isRoot = true;
    }
    else{
      $o->isRoot = false;
    }

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
   * Метод определяет, является адрес абсолютным или относительным.
   * @return boolean true - если адрес абсолютный, иначе - false.
   */
  public function isRoot(){
    return $this->isRoot;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
