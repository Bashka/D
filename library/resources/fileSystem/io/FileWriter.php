<?php
namespace D\library\resources\fileSystem\io;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\io\IOException;
use D\library\patterns\entity\io\OutStream;
use D\library\patterns\entity\io\SeekIO;
use D\library\patterns\entity\io\Closed;

/**
 * Класс представляет выходной поток в файл.
 * @author Artur Sh. Mamedbekov
 */
class FileWriter extends OutStream implements SeekIO, Closed{
  use FileClosed, FileSeekIO;

  /**
   * @prototype D\library\patterns\entity\io\Writer
   */
  public function write($data){
    InvalidArgumentException::verify($data, 's', [1]);
    $result = fwrite($this->resource, $data);
    if($result === false){
      throw new IOException('Ошибка использования потока вывода.');
    }

    return $result;
  }

  /**
   * Метод отчищает содержимое потока (файл).
   * @return boolean true - в случае устеха, иначе - false.
   */
  public function clean(){
    if(!ftruncate($this->resource, 0)){
      return false;
    }
    $this->setPosition(0);

    return true;
  }
}
