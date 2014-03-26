<?php
namespace D\library\resources\network\socket;

use D\library\patterns\entity\io as io;

/**
 * Класс представляет двунаправленный поток, используемый при сокетном соединении.
 * Объекты данного класса могут быть использованы как входной и выходной поток к удаленному сокету.
 * Класс является фассадным и делегирует свои полномочия входному и выходному потоку в отдельности.
 * Закрытие либого из потоков (входного или выходного) приведет к закрытию парного потока (выходного и входного соответственно).
 * @author Artur Sh. Mamedbekov
 */
class Stream implements io\Closed, io\Reader, io\Writer{
  /**
   * @var \D\library\resources\network\socket\InStream Входной поток от удаленного сокета.
   */
  protected $in;

  /**
   * @var \D\library\resources\network\socket\OutStream Выходной поток к удаленному сокету.
   */
  protected $out;

  /**
   * @param \D\library\resources\network\socket\InStream $in Входной поток.
   * @param \D\library\resources\network\socket\OutStream $out Выходной поток.
   */
  function __construct(InStream $in, OutStream $out){
    $this->in = $in;
    $this->out = $out;
  }

  /**
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function close(){
    return $this->in->close();
  }

  /**
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function isClose(){
    return $this->in->isClose();
  }

  /**
   * @prototype D\library\patterns\entity\io\Reader
   */
  public function read(){
    return $this->in->read();
  }

  /**
   * @prototype D\library\patterns\entity\io\Reader
   */
  public function readString($length){
    return $this->in->readString($length);
  }

  /**
   * @prototype D\library\patterns\entity\io\Reader
   */
  public function readLine($EOLSymbol = "\r\n"){
    return $this->in->readLine();
  }

  /**
   * @prototype D\library\patterns\entity\io\Reader
   */
  public function readAll(){
    return $this->in->readAll();
  }

  /**
   * @prototype D\library\resources\network\socket\InStream
   */
  public function readPackage($length){
    return $this->in->readPackage($length);
  }

  /**
   * @prototype D\library\patterns\entity\io\Writer
   */
  public function write($data){
    return $this->out->write($data);
  }

  /**
   * Метод возвращает входной поток к удаленному сокету.
   * @return \D\library\resources\network\socket\InStream Входной поток к удаленному сокету.
   */
  public function getIn(){
    return $this->in;
  }

  /**
   * Метод возвращает выходной поток к удаленному сокету.
   * @return \D\library\resources\network\socket\OutStream Выходной поток к удаленному сокету.
   */
  public function getOut(){
    return $this->out;
  }

  /**
   * @prototype D\library\resources\network\socket\InStream
   */
  public function setReadTimeout($readTimeout){
    $this->in->setReadTimeout($readTimeout);
  }

  /**
   * @prototype D\library\resources\network\socket\InStream
   */
  public function getReadTimeout(){
    return $this->in->getReadTimeout();
  }
}
