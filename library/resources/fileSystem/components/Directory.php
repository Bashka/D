<?php
namespace D\library\resources\fileSystem\components;


use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс представляет каталог файловой и предоставляет механизмы доступа к его содержимому.
 * @author  Artur Sh. Mamedbekov
 */
class Directory extends Component{
  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function rename($newName){
    $newAddress = $this->getLocation().'/'.$newName;
    if(file_exists($newAddress) && is_dir($newAddress)){
      throw new DuplicationException('Невозможно периименовать каталог ['.$this->getAddress().'], так как каталог с данным именем ['.$newAddress.'] уже существует.');
    }
    // Проверка типа выполняется на уровне родителя.

    parent::rename($newName);
  }

  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function move($location){
    $newAddress = $location.'/'.$this->getName();
    if(realpath($location) == $this->getAddress()){
      throw new NotExistsException('Ожидается отличный от данного каталога ['.$this->getAddress().'] каталог.');
    }
    if(file_exists($newAddress) && is_dir($newAddress)){
      throw new DuplicationException('Невозможно переместить каталог ['.$this->getAddress().'], так как в целевом каталоге ['.$location.'] каталог с данным именем уже существует.');
    }
    // Проверка типа выполняется на уровне родителя.

    parent::move($location);
  }

  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function copyPaste($location){
    InvalidArgumentException::verify($location, 's', [1]);
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе компонент ['.$this->getAddress().'] не найден.');
    }
    $newAddress = $location.'/'.$this->getName();
    // Проверка на дублирование выполняется в методе create.
    $copyDirRoot = new Directory($newAddress);
    try{
      $copyDirRoot->create();
    }
    catch(DuplicationException $e){
      throw new DuplicationException('Невозможно копировать каталог ['.$this->getAddress().'], так как в целевом каталоге ['.$location.'] каталог с данным именем уже существует.', 1, $e);
    }
    $iterator = $this->getDirectoryIterator();
    /**
     * @var \DirectoryIterator $component
     */
    foreach($iterator as $component){
      if($component == '.' || $component == '..'){
        continue;
      }
      // Перехват исключений не выполняется в связи невозможностью их появления
      if($component->isDir()){
        $this->getDir((string) $component)->copyPaste($newAddress);
      }
      elseif($component->isFile()){
        $this->getFile((string) $component)->copyPaste($newAddress);
      }
    }

    return $copyDirRoot;
  }

  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function getSize(){
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе компонент ['.$this->getAddress().'] не найден.');
    }
    $iterator = $this->getDirectoryIterator();
    $size = 0;
    /**
     * @var \DirectoryIterator $component
     */
    foreach($iterator as $component){
      if($component == '.' || $component == '..'){
        continue;
      }
      // Перехват исключений не выполняется в связи с невозможностью их появления
      if($component->isDir()){
        $size += $this->getDir((string) $component)->getSize();
      }
      elseif($component->isFile()){
        $size += $this->getFile((string) $component)->getSize();
      }
    }

    return $size;
  }

  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function isExists(){
    return file_exists($this->getAddress()) && is_dir($this->getAddress());
  }

  /**
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function delete(){
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе каталог ['.$this->getAddress().'] не найден.');
    }
    $this->clear();
    return rmdir($this->getAddress());
  }

  /**
   * Метод рекурсивно удаляет все содержимое каталога.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если вызываемого каталога не существует.
   */
  public function clear(){
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе каталог ['.$this->getAddress().'] не найден.');
    }
    $iterator = $this->getDirectoryIterator();
    /**
     * @var \DirectoryIterator $component
     */
    foreach($iterator as $component){
      if($component == '.' || $component == '..'){
        continue;
      }
      if($component->isDir()){
        $this->getDir((string) $component)->delete();
      }
      elseif($component->isFile()){
        $this->getFile((string) $component)->delete();
      }
    }
  }

  /**
   * Возвращает итератор для данного каталога.
   * @return \DirectoryIterator Итератор вызывающего каталога.
   */
  public function getDirectoryIterator(){
    return new \DirectoryIterator($this->getAddress());
  }

  /**
   * Метод пытается создать вызываемый каталог в файловой системе.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если создание компонента приведет к дублированию.
   */
  public function create(){
    if($this->isExists()){
      throw new DuplicationException('Невозможно выполнить действие. Файл с данным именем ['.$this->getAddress().'] уже существует.');
    }
    mkdir($this->getAddress());
  }

  /**
   * Метод проверяет, имеется ли в вызывающем каталоге заданный файл.
   * @param string $fileName Имя проверяемого файла.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если вызываемого каталога не существует.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - если в вызывающем каталоге имеется заданный файл, иначе - false.
   */
  public function hasFile($fileName){
    InvalidArgumentException::verify($fileName, 's', [1]);
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе каталог [' . $this->getAddress() . '] не найден.');
    }
    $fileAddress = $this->getAddress() . '/' . $fileName;

    return (file_exists($fileAddress) && is_file($fileAddress));
  }

  /**
   * Метод проверяет, имеется ли в вызывающем каталоге заданный каталог.
   * @param string $dirName Имя проверяемого каталога.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если вызываемого каталога не существует.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - если в вызывающем каталоге имеется заданный каталог, иначе - false.
   */
  public function hasDir($dirName){
    InvalidArgumentException::verify($dirName, 's', [1]);
    if(!$this->isExists()){
      throw new NotExistsException('Невозможно выполнить действие. В файловой системе каталог [' . $this->getAddress() . '] не найден.');
    }
    $dirAddress = $this->getAddress() . '/' . $dirName;

    return (file_exists($dirAddress) && is_dir($dirAddress));
  }

  /**
   * Возвращает файл, содержащийся в вызываемом каталоге, имя которого заданно в аргументе. Метод возвращает представление компонента, даже если его нет в файловой системе.
   * @param string $fileName Имя компонента.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return \D\library\resources\fileSystem\components\File Получаемый файл.
   */
  public function getFile($fileName){
    InvalidArgumentException::verify($fileName, 's', [1]);
    return new File($this->getAddress().'/'.$fileName);
  }

  /**
   * Возвращает каталог, содержащийся в вызываемом каталоге, имя которого заданно в аргументе. Метод возвращает представление компонента, даже если его нет в файловой системе.
   * @param string $dirName Имя компонента.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return \D\library\resources\fileSystem\components\Directory Получаемый каталог.
   */
  public function getDir($dirName){
    InvalidArgumentException::verify($dirName, 's', [1]);
    return new Directory($this->getAddress().'/'.$dirName);
  }
}