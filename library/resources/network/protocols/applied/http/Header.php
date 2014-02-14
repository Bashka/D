<?php
namespace D\library\resources\network\protocols\applied\http;

use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\conversion\RestorableAdapter;

/**
 * Класс представляет заголовок HTTP запроса.
 * @author Artur Sh. Mamedbekov
 */
class Header extends RestorableAdapter implements Interpreter{
  /**
   * @var \D\library\resources\network\protocols\applied\http\Parameter[] Используемые в заголовке параметры.
   */
  protected $parameters = [];

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    if(is_null($driver)){
      $driver = "\r\n";
    }

    return ['(?:' . $driver . ')|(?:(?:' . Parameter::getMasks()[0] . $driver . ')+)'];
  }

  /**
   * Аргумент $driver определяет EOL.
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    if(is_null($driver)){
      $driver = "\r\n";
    }
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string, $driver);
    $o = new self;
    $string = trim($string);
    if($string !== ''){
      $parameters = explode($driver, $string);
      foreach($parameters as $param){
        $o->addParameter(Parameter::reestablish($param));
      }
    }

    return $o;
  }

  /**
   * Метод добавляет указанный параметр в заголовок.
   * @param \D\library\resources\network\protocols\applied\http\Parameter $component Добавляемый параметр.
   */
  public function addParameter(Parameter $component){
    $this->parameters[$component->getName()] = $component;
  }

  /**
   * Метод создает из строки и добавляет указанный параметр в заголовок.
   * @param string $name Имя параметра.
   * @param string $value Значение параметра.
   */
  public function addParameterStr($name, $value){
    $this->parameters[$name] = new Parameter($name, $value);
  }

  /**
   * Метод определяет, присуствует ли указанный параметр заголовка.
   * @param string $name Имя параметра.
   * @return boolean true - если параметр присуствует, иначе - false.
   */
  public function hasParameter($name){
    return array_key_exists($name, $this->parameters);
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    if(is_null($driver)){
      $driver = "\r\n";
    }
    if(count($this->parameters) == 0){
      return $driver;
    }
    $result = '';
    foreach($this->parameters as $parameter){
      $result .= $parameter->interpretation($driver) . $driver;
    }

    return $result;
  }

  /**
   * Метод возрващает все указанные в данном заголовке параметры.
   * @return \D\library\resources\network\protocols\applied\http\Parameter[]
   */
  public function getParameters(){
    return $this->parameters;
  }

  /**
   * Метод возвращает указанный параметр заголовка, если он задан.
   * @param string $name Имя целевого параметра.
   * @return null|\D\library\resources\network\protocols\applied\http\Parameter Запрашиваемый параметр или null - если он не был задан.
   */
  public function getParameter($name){
    if(!$this->hasParameter($name)){
      return null;
    }

    return $this->parameters[$name];
  }

  /**
   * Метод возвращает значение указанного параметра заголовка.
   * @param string $name Целевой параметр.
   * @return null|string Значение целевого параметра заголовка или null - если он не был задан.
   */
  public function getParameterValue($name){
    $parameter = $this->getParameter($name);

    return ($parameter)? $parameter->getValue() : null;
  }
}
