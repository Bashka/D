<?php
namespace D\library\resources\network\socket;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\io as io;

/**
 * Объекты данного класса представляют сокетное соединение в виде входного потока.
 * Класс использует открытое сокетное соединение для формирования потока ввода.
 * Закрытые потоки не могут быть использованы или октрыты повторно.
 * @author Artur Sh. Mamedbekov
 */
class InStream extends io\InStream implements io\Closed{
  /**
   * @var boolean Флаг готовности потока. true - если поток открыт, false - если закрыт.
   */
  protected $isClose = false;

  /**
   * @var integer|null Таймаут ожидания при чтении данных (сек).
   */
  protected $readTimeout = 1;

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
   * Учитывайте то, что данный метод использует задержку для определения окончания потока, это может привести к преждевременному завершению чтения из за слабого соединения.
   * Используйте пакеты с известным объемом данных и метод readPackage чтобы избежать потери данных при передаче.
   * @prototype D\library\patterns\entity\io\Reader
   */
  public function read(){
    if($this->isClose()){
      throw new io\IOException('Невозможно выполнить чтение из закрытого потока.');
    }
    socket_set_option($this->resource, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->readTimeout, 'usec' => 1]);
    $char = socket_read($this->resource, 1);
    socket_set_block($this->resource); // Остановка выполнения до выполнения чтения из потока
    if($char === false){
      $code = socket_last_error($this->resource);
      // В случае превышения интервала ожидания, предполагается конец потока
      if($code == 11){
        return '';
      }
      throw new io\IOException('Невозможно выполнть чтение из потока (' . $code . ': ' . socket_strerror($code) . '). Возможно сокетное соединение было сброшено.');
    }
    else{
      return $char;
    }
  }

  /**
   * Метод выполняет блокирующее чтение пакета указанной длины.
   * Если в потоке недостаточно данных для чтения, процесс ожидает получения этих данных.
   * @param integer $length Размер пакета в байтах.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае возникновения ошибки при чтении из поток.
   * @return string Прочитанная строка или пустая строка если нет данных для чтения.
   */
  public function readPackage($length){
    if($this->isClose()){
      throw new io\IOException('Невозможно выполнить чтение из закрытого потока.');
    }
    InvalidArgumentException::verify($length, 'i', [1]);
    $char = socket_read($this->resource, $length);
    socket_set_block($this->resource); // Остановка выполнения до выполнения чтения из потока
    if($char === false){
      $code = socket_last_error($this->resource);
      throw new io\IOException('Невозможно выполнть чтение из потока (' . $code . ': ' . socket_strerror($code) . '). Возможно сокетное соединение было сброшено.');
    }
    else{
      return $char;
    }
  }

  /**
   * Метод устанавливает время ожидания данных при чтении.
   * @param integer|null $readTimeout Время ожидания данных в секундах. null - если блокировка не выполняется.
   */
  public function setReadTimeout($readTimeout){
    $this->readTimeout = $readTimeout;
  }

  /**
   * Метод возвращает время ожидания данных при чтении.
   * @return integer|null Время ожидания данных в секундах или null - если блокировка не выполняется.
   */
  public function getReadTimeout(){
    return $this->readTimeout;
  }
}