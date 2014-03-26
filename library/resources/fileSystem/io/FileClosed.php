<?php
namespace D\library\resources\fileSystem\io;

/**
 * Реализация интерфейса Closed для файловых потоков.
 * @author Artur Sh. Mamedbekov
 */
trait FileClosed{
  /**
   * @var boolean Флаг доступности потока.
   */
  private $closed = false;

  /**
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function close(){
    $this->closed = fclose($this->resource);
  }

  /**
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function isClose(){
    return $this->closed;
  }
}
