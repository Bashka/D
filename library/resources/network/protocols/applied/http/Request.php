<?php
namespace D\library\resources\network\protocols\applied\http;

use D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет HTTP запрос клиента.
 * @author Artur Sh. Mamedbekov
 */
class Request extends Message{
  /**
   * Метод GET запроса.
   */
  const GET = 'GET';

  /**
   * Метод POST запроса.
   */
  const POST = 'POST';

  /**
   * @var string Метод запроса.
   */
  protected $method;

  /**
   * @var string URI запроса.
   */
  protected $URI;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    if(is_null($driver)){
      $driver = "\r\n";
    }

    return ['(' . self::GET . '|' . self::POST . ') ((?:' . FileSystemAddress::getMasks()[0] . '|\/)(?:\?' . self::getPatterns()['var'] . '(?:&' . self::getPatterns()['var'] . ')*)?) HTTP\/1.1' . $driver . '(' . Header::getMasks($driver)[0] . ')?' . $driver . '(.|\n|\r)*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['var' => '[A-Za-z0-9_]+=(?:[A-Za-z0-9_]+)?'];
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
    $m = parent::reestablish($string, $driver);
    $method = $m[1];
    $uri = $m[2];
    $header = Header::reestablish($m[4]);
    $host = $header->getParameterValue('Host');
    $body = $m[7];

    return new self($host, $uri, $method, $header, $body);
  }

  /**
   * @param string $host Узел и порт запроса.
   * @param string $URI Адрес ресурса.
   * @param string $method [optional] Метод запроса.
   * @param \D\library\resources\network\protocols\applied\http\Header $header [optional] Заголовок запроса.
   * @param string|array $body [optional] Тело запроса в виде строки или ассоциативного массива параметров.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра неверного типа.
   */
  function __construct($host, $URI, $method = self::GET, Header $header = null, $body = null){
    InvalidArgumentException::verify($host, 's', [1]);
    InvalidArgumentException::verify($URI, 's');
    InvalidArgumentException::verify($method, 's', [1]);
    if(empty($URI)){
      $URI = '/';
    }
    $this->URI = $URI;
    if($method == self::POST){
      parent::__construct($header, $body);
    }
    elseif($method == self::GET){
      parent::__construct($header, null);
      if(is_array($body) && count($body) > 0){
        $parameters = [];
        foreach($body as $name => $value){
          $parameters[] = urlencode($name) . '=' . urlencode($value);
        }
        $this->URI .= '?' . implode('&', $parameters);
      }
      else{
        parent::__construct($header, $body);
      }
    }
    $this->method = $method;
    $this->header->addParameterStr('Host', $host);
  }

  /**
   * Аргумент $driver определяет EOL.
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    if(is_null($driver)){
      $driver = "\r\n";
    }
    $generalHeader = $this->method . ' ' . $this->URI . ' HTTP/1.1';

    return $generalHeader . $driver . $this->header->interpretation($driver) . $driver . $this->body;
  }

  /**
   * Метод возвращает метод запроса.
   * @return string Метод запроса.
   */
  public function getMethod(){
    return $this->method;
  }

  /**
   * Метод возвращает URI запроса.
   * @return string URI запроса.
   */
  public function getURI(){
    return $this->URI;
  }
}