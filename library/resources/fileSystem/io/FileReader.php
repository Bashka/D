<?php
namespace D\library\resources\fileSystem\io;

use D\library\patterns\entity\io\IOException;
use D\library\patterns\entity\io\InStream;
use D\library\patterns\entity\io\SeekIO;
use D\library\patterns\entity\io\Closed;

/**
 * Класс представляет входной поток из файла.
 * @author Artur Sh. Mamedbekov
 */
class FileReader extends InStream implements SeekIO, Closed{
  use FileSeekIO, FileClosed;

  /**
   * @prototype D\library\patterns\entity\io\Reader
   */
  public function read(){
    $result = fread($this->resource, 1);
    if($result === false){
      throw new IOException('Ошибка использования потока ввода.');
    }

    return $result;
  }
}
