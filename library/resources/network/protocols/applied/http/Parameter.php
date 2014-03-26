<?php
namespace D\library\resources\network\protocols\applied\http;

use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\conversion\RestorableAdapter;

/**
 * Класс представляет параметр заголовка HTTP запроса.
 * @author Artur Sh. Mamedbekov
 */
class Parameter extends RestorableAdapter implements Interpreter{
  /**
   * @var string Имя параметра.
   */
  protected $name;

  /**
   * @var string Значение параметра.
   */
  protected $value;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    if(is_null($driver)){
      $driver = "\r\n";
    }
    return ['([A-Za-z0-9_\-]+):([^'.$driver.']*)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);

    return new self($m[1], trim($m[2]));
  }

  function __construct($name, $value){
    $this->name = $name;
    $this->value = $value;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    return $this->name . ':' . $this->value;
  }

  /**
   * Метод возвращает имя параметра.
   * @return string Имя параметра.
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Метод возвращает значение параметра.
   * @return string Значение параметра.
   */
  public function getValue(){
    return $this->value;
  }
}
