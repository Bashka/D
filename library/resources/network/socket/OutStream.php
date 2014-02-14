<?php
namespace D\library\resources\network\socket;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\io as io;

/**
 * Объекты данного класса представляют сокетное соединение в виде выходного потока.
 * Класс использует открытое сокетное соединение для формирования потока вывода.
 * Закрытые потоки не могут быть использованы или октрыты повторно.
 * @author Artur Sh. Mamedbekov
 */
class OutStream extends io\OutStream implements io\Closed{
  /**
   * @var boolean Флаг готовности потока. true - если поток открыт, false - если закрыт.
   */
  protected $isClose;

  /**
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function close(){
    if(!$this->isClose()){
      if(socket_shutdown($this->resource) === false){
        $code = socket_last_error($this->resource);
        throw new io\IOException('Ошибка закрытия сокета.' . socket_strerror($code), $code);
      }
      if(socket_close($this->resource) === false){
        $code = socket_last_error($this->resource);
        throw new io\IOException('Ошибка закрытия сокета.' . socket_strerror($code), $code);
      }
      $this->isClose = true;
    }
  }

  /**
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function isClose(){
    return $this->isClose;
  }

  /**
   * @prototype D\library\patterns\entity\io\Writer
   */
  public function write($data){
    if($this->isClose()){
      throw new io\IOException('Невозможно выполнить чтение из закрытого потока.');
    }
    InvalidArgumentException::verify($data, 's', [1]);
    $result = socket_write($this->resource, $data);
    if($result === false){
      $code = socket_last_error($this->resource);
      throw new io\IOException(socket_strerror($code), $code);
    }
    else{
      return $result;
    }
  }
}
