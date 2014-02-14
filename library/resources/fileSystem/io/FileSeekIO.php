<?php
namespace D\library\resources\fileSystem\io;

use D\library\patterns\entity\io\IOException;

/**
 * Реализация интерфейса SeekIO для файловых потоков.
 * @author Artur Sh. Mamedbekov
 */
trait FileSeekIO{
  /**
   * @prototype D\library\patterns\entity\io\SeekIO
   */
  public function setPosition($position){
    fseek($this->resource, $position);
  }

  /**
   * @prototype D\library\patterns\entity\io\SeekIO
   */
  public function getPosition(){
    $result = ftell($this->resource);
    if($result === false){
      throw new IOException('Ошибка использования потока ввода.');
    }

    return $result;
  }
}
