<?php
namespace D\library\resources\fileSystem\components;

use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\environmentExceptions\LockException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\resources\fileSystem\io\BlockingFileReader;
use D\library\resources\fileSystem\io\BlockingFileWriter;

/**
 * Класс представляет файл файловой системы и предоставляет входные/выходные потоки для работы с содержимым.
 * @author  Artur Sh. Mamedbekov
 */
class File extends Component implements \SplObserver{
  /**
   * @var \D\library\resources\fileSystem\io\BlockingFileReader Текущий входной поток данного файла.
   */
  protected $reader;

  /**
   * @var \D\library\resources\fileSystem\io\BlockingFileWriter Текущий выходной поток данного файла.
   */
  protected $writer;

  /**
   * Метод снимает блокировку файла при закрытии потока. Метод реагирует только в том случае, если он был вызван текущим блокирующим потоком.
   * @param \SplSubject $subject Закрываемый блокирующий поток.
   */
  public function update(\SplSubject $subject){
    if(isset($this->reader) && $subject === $this->reader && $this->reader->isClose()){
      unset($this->reader);
    }
    elseif(isset($this->writer) && $subject === $this->writer && $this->writer->isClose()){
      unset($this->writer);
    }
  }

  /**
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если переименовываемый файл заблокирован.
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function rename($newName){
    $newAddress = $this->getLocation().'/'.$newName;
    if(!empty($this->reader) || !empty($this->writer)){
      throw new LockException('Доступ к данному компоненту ['.$this->getAddress().'] запрещен.');
    }
    if(file_exists($newAddress) && is_file($newAddress)){
      throw new DuplicationException('Невозможно периименовать файл ['.$this->getAddress().'], так как файл с данным именем ['.$newAddress.'] уже существует.');
    }
    // Проверка типа выполняется на уровне родителя.

    parent::rename($newName);
  }

  /**
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если перемещаемый файл заблокирован.
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function move($location){
    $newAddress = $location.'/'.$this->getName();
    if(!empty($this->reader) || !empty($this->writer)){
      throw new LockException('Доступ к данному компоненту ['.$this->getAddress().'] запрещен.');
    }
    if(file_exists($newAddress) && is_file($newAddress)){
      throw new DuplicationException('Невозможно переместить файла ['.$this->getAddress().'], так как в целевом каталоге ['.$location.'] файл с данным именем уже существует.');
    }
    // Проверка типа выполняется на уровне родителя.

    parent::move($location);
  }

  /**
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если копируемый файл заблокирован.
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function copyPaste($location){
    if(!empty($this->writer)){
      throw new LockException('Доступ к данному компоненту ['.$this->getAddress().'] запрещен.');
    }
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе файл ['.$this->getAddress().'] не найден.');
    }
    $newAddress = $location.'/'.$this->getName();
    if(file_exists($newAddress) && is_file($newAddress)){
      throw new DuplicationException('Невозможно копировать файла ['.$this->getAddress().'], так как в целевом каталоге ['.$location.'] файл с данным именем уже существует.');
    }
    $result = copy($this->getAddress(), $newAddress);
    assert('$result === true');
    return new File($newAddress);
  }

  /**
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если целевой файл заблокирован.
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function getSize(){
    if(!empty($this->writer)){
      throw new LockException('Доступ к данному компоненту ['.$this->getAddress().'] запрещен.');
    }
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе компонент ['.$this->getAddress().'] не найден.');
    }

    return filesize($this->getAddress());
  }

  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function isExists(){
    return file_exists($this->getAddress()) && is_file($this->getAddress());
  }

  /**
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если удаляемый файл заблокирован.
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function delete(){
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе файл ['.$this->getAddress().'] не найден.');
    }
    if(!empty($this->reader) || !empty($this->writer)){
      throw new LockException('Доступ к данному компоненту ['.$this->getAddress().'] запрещен.');
    }

    return unlink($this->getAddress());
  }

  /**
   * Метод пытается создать вызываемый файл в файловой системе.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если создание компонента приведет к дублированию.
   */
  public function create(){
    if($this->isExists()){
      throw new DuplicationException('Невозможно выполнить действие. Файл с данным именем ['.$this->getAddress().'] уже существует.');
    }
    fclose(fopen($this->getAddress(), 'a+'));
  }

  /**
   * Метод возвращает входной поток для данного файла и блокирует его разделяемой блокировкой. В сдучае, если полученный поток будет закрыт, блокировка снимется автоматически.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если невозможно вернуть поток из за того, что уже был открыт выходной поток.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если на момент вызова метода  файла не существовало.
   * @return \D\library\resources\fileSystem\io\BlockingFileReader Файловый поток ввода.
   */
  public function getReader(){
    if(!empty($this->reader)){
      return $this->reader;
    }
    if(!empty($this->writer)){
      throw new LockException('Доступ к данному файлу ['.$this->getAddress().'] запрещен.');
    }
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе файл ['.$this->getAddress().'] не найден.');
    }
    $des = fopen($this->getAddress(), 'r+b');
    assert('is_resource($des)');
    flock($des, 1);
    $this->reader = new BlockingFileReader($des);
    $this->reader->attach($this);

    return $this->reader;
  }

  /**
   * Метод возвращает выходной поток для данного файла и блокирует его исключительной блокировкой. В сдучае, если полученный поток будет закрыт, блокировка снимется автоматически. Указатель в потоке устанавливается на конец потока.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае, если невозможно вернуть поток из за того, что уже был открыт выходной поток.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если на момент вызова метода файла не существовало.
   * @return \D\library\resources\fileSystem\io\BlockingFileWriter Файловый поток вывода.
   */
  public function getWriter(){
    if(!empty($this->writer)){
      return $this->writer;
    }
    if(!empty($this->reader)){
      throw new LockException('Доступ к данному файлу ['.$this->getAddress().'] запрещен.');
    }
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе файл ['.$this->getAddress().'] не найден.');
    }
    $des = fopen($this->getAddress(), 'r+b');
    assert('is_resource($des)');
    flock($des, 2);
    fseek($des, $this->getSize());
    $this->writer = new BlockingFileWriter($des);
    $this->writer->attach($this);

    return $this->writer;
  }
}
