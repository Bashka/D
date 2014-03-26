<?php
namespace D\library\patterns\entity\io;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Данный класс представляет классическую реализацию входного потока данных.
 * Дочернему классу достаточно реализовать метод read, использующий определенный здесь указатель на ресурс, остальные методы реализуются через использование этого метода.
 * @author  Artur Sh. Mamedbekov
 */
abstract class InStream implements Reader{
  /**
   * @var resource Указатель на ресурс, с которым работает объект.
   */
  protected $resource;

  /**
   * @param resource $resource Указатель на ресурс.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или пустой строки.
   */
  function __construct($resource){
    InvalidArgumentException::verify($resource, 'r');
    $this->resource = $resource;
  }

  /**
   * @prototype \PPHP\tools\patterns\io\Reader
   */
  public function readString($length){
    InvalidArgumentException::verify($length, 'i', [1]);
    $result = '';
    // Последовательное получение байт из потока.
    while($length--){
      $char = $this->read();
      $result .= $char;
      // Обнаружен конец потока.
      if($char === ''){
        break;
      }
    }

    return $result;
  }

  /**
   * @prototype \PPHP\tools\patterns\io\Reader
   */
  public function readLine($EOLSymbol = "\r\n"){
    InvalidArgumentException::verify($EOLSymbol, 's', [1]);
    $EOLLength = strlen($EOLSymbol); // Длина символа EOL.
    $result = '';
    // Последовательное получение байт из потока.
    while(($char = $this->read()) != ''){
      $result .= $char;
      if(substr($result, -$EOLLength) == $EOLSymbol){
        return substr($result, 0, -$EOLLength);
      }
    }
    return $result;
  }

  /**
   * @prototype \PPHP\tools\patterns\io\Reader
   */
  public function readAll(){
    $result = '';
    // Последовательное получение байт из потока.
    do{
      $currentByte = $this->read();
      $result .= $currentByte;
    } while($currentByte !== '');

    return $result;
  }
}
