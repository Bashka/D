<?php
namespace D\library\patterns\entity\dataType\special\fileSystem;

use D\library\patterns\entity\dataType\String;
use D\library\patterns\entity\dataType\Wrap;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для представления и верификации имен файлов и папок.
 * Допустимый тип: любые символы кроме / : * ? " < > | \0 \
 * @author Artur Sh. Mamedbekov
 */
class FileSystemName extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var string Имя ресурса файловой системы.
   */
  protected $name;

  /**
   * @var string Расширение ресурса файловой системы.
   */
  protected $expansion;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return [FileSystemName::getPatterns()['fieldName']];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['fieldName' => '[^\/:*?"<>\|\0\\\]+'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    $o = new self($string);
    $strObject = new String($string);
    $name = $strObject->getUpTo('.');
    if($name === false){
      $o->name = $string;
    }
    else{
      $o->name = $name->getVal();
      $o->expansion = $strObject->get()->getVal();
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
   * Метод возвращает расширение ресурса файловой системы.
   * @return string Расширение ресурса файловой системы.
   */
  public function getExpansion(){
    return $this->expansion;
  }

  /**
   * Метод возвращает имя ресурса файловой системы.
   * @return string Имя ресурса файловой системы.
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
