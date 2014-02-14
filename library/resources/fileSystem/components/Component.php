<?php
namespace D\library\resources\fileSystem\components;

use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс является представлением любого компонента файловой системы и определяет основные механизмы манипулирования им.
 * @author  Artur Sh. Mamedbekov
 */
abstract class Component{
  /**
   * @var string Расположение компонента от корня файлавой системы.
   */
  protected $location;

  /**
   * @var string Имя компонента.
   */
  protected $name;

  /**
   * Метод копирует компонент в указанный каталог.
   * @param string $location Абсолютный или относительный адрес целевого каталога.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если целевой каталог уже содержит копируемый компонент.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если на момент вызова метода компонента или целевого каталога не существовало.
   * @return \D\library\resources\fileSystem\components\Component Представление копии компонента.
   */
  public abstract function copyPaste($location);

  /**
   * Метод удаляет текущий компонент из файловой системы.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если удаляемого компонента не существует.
   */
  public abstract function delete();

  /**
   * Метод возвращает размер в байтах данного компонента.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если вызываемого компонента не существует.
   * @return integer Размер компонента в байтах.
   */
  public abstract function getSize();

  /**
   * Метод определяет, существует ли вызывающий компонент на момент вызова метода.
   * @return boolean true - если компонент на момент вызова метода существует в файловой системе, иначе - false.
   */
  public abstract function isExists();

  /**
   * Метод изменяет имя компонента на заданное.
   * @param string $newName Новое имя компонента.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если переименование компонента приведет к дублированию.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если вызываемого компонента не существует.
   */
  public function rename($newName){
    InvalidArgumentException::verify($newName, 's', [1]);
    if(strpos($newName, '/') !== false){
      throw InvalidArgumentException::getValidException('[^/]', $newName);
    }
    if(!$this->isExists()){
      throw new NotExistsException('Используемый компонент [' . $this->getAddress() . '] не найден в файловой системе.');
    }
    // Проверка на дублирование выполняется в конкретных классах.
    $newAddress = $this->getLocation() . '/' . $newName;
    if(rename($this->getAddress(), $newAddress)){
      $this->name = $newName;
    }
  }

  /**
   * Метод перемещает компонент в данный каталог.
   * @param string $location Целевой каталог.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если перемещение компонента приведет к дублированию.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если вызываемого компонента не существует.
   */
  public function move($location){
    InvalidArgumentException::verify($location, 's', [1]);
    if(!$this->isExists()){
      throw new NotExistsException('Используемый компонент [' . $this->getAddress() . '] не найден в файловой системе.');
    }
    // Проверка на дублирование выполняется в конкретных классах.
    // Проверка на рекурсию выполняется в конкретных классах.
    $location = realpath($location);
    if(rename($this->getAddress(), $location . '/' . $this->getName())){
      $this->location = $location;
    }
  }

  /**
   * @param string $address Абсолютный или относительный адрес компонента.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function __construct($address){
    InvalidArgumentException::verify($address, 's', [1]);
    $fullAddress = realpath($address);
    if($fullAddress === false){
      if($address[0] == '/'){
        $fullAddress = $address;
      }
      else{
        $fullAddress = getcwd().'/'.$address;
      }
    }
    $this->location = dirname($fullAddress);
    $this->name = basename($fullAddress);
  }

  /**
   * Метод возвращает абсолютный адрес компонента.
   * @return string Абсолютный адрес компонента относительно корня файловой системы.
   */
  public function getAddress(){
    if($this->name == ''){
      return $this->location;
    }
    return $this->location . '/' . $this->name;
  }

  /**
   * Метод возвращает расположение компонента от корня файлавой системы.
   * @return string Расположение компонента от корня файлавой системы.
   */
  public function getLocation(){
    return $this->location;
  }

  /**
   * Метод возвращает имя компонента.
   * @return string Имя компонента или пустая строка, если вызывается корневой каталог.
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Метод возвращает представление каталога, содержащего вызываемый компонент.
   * @return \D\library\resources\fileSystem\components\Directory Каталог, содержащий вызываемый компонент.
   */
  public function getLocationDirectory(){
    return new Directory($this->location);
  }
} 