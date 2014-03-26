<?php
namespace D\library\resources\fileSystem\io;

use D\library\patterns\structure\observer\TSubject;

/**
 * Класс расширяет выходной файловый поток, добавляя ему механизмы регистрации и оповещения наблюдателей.
 * Использование шаблона "Наблюдатель" необходимо для информирования целевого файла о закрытии использующих его потоков и отключения блокировки.
 * @author Artur Sh. Mamedbekov
 */
class BlockingFileWriter extends FileWriter implements \SplSubject{
  use TSubject;

  /**
   * Метод дополнен оповещением наблюдателей о закрытии потока.
   * @prototype D\library\patterns\entity\io\Closed
   */
  public function close(){
    parent::close();
    $this->notify();
  }
}
