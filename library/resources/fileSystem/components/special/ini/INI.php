<?php
namespace D\library\resources\fileSystem\components\special\ini;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\resources\fileSystem\components\File;

/**
 * Класс представляет файл, данные в котором структурированны согласно правилам initialization file.
 * Важно помнить, что объекты данного класса кэшируют полученные данные, что может привести к рассогласованию состояний двух объектов, использующих один и тот же файл, если один из этих объектов изменит состояние файла.
 * @author  Artur Sh. Mamedbekov
 */
class INI extends File{
  /**
   * @var string[][] Содержимое файла в виде ассоциативного массива.
   */
  private $content;

  /**
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если файла не существует в файловой системе.
   * @prototype D\library\resources\fileSystem\components\Component
   */
  public function __construct($address){
    parent::__construct($address);
    if(!$this->isExists()){
      throw new NotExistsException('Запрашиваемого файла ['.$this->getAddress().'] не существует.');
    }
    $this->content = parse_ini_file($this->getAddress(), true);
  }

  /**
   * Метод записывает изменения в файл. Данный метод должен вызываться после внесения изменений в файл для фиксации их на диске.
   */
  public function rewrite(){
    $writer = $this->getWriter(); // Проверка блокировки не выполняется из за автоматического ожидания разблокировки.
    $writer->clean();
    /**
     * @var string[] $sectionData
     */
    foreach($this->content as $sectionName => $sectionData){
      $writer->write('[' . $sectionName . "]\r\n");
      foreach($sectionData as $k => $v){
        $writer->write($k . "=" . $v . "\r\n");
      }
    }
    $writer->close();
  }

  /**
   * Метод возвращает значение указанного свойства.
   * @param string $section Целевая секция.
   * @param string $key Имя свойства.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия указанного свойства.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string Значение свойства.
   */
  public function get($section, $key){
    InvalidArgumentException::verify($section, 's', [1]);
    InvalidArgumentException::verify($key, 's', [1]);
    if(!isset($this->content[$section]) || !isset($this->content[$section][$key])){
      throw new NotFoundDataException('Запрашиваемое свойство ['.$section.'::'.$key.'] отсутствует.');
    }
    return $this->content[$section][$key];
  }

  /**
   * Метод возвращает все свойства секции.
   * @param string $section Целевая секция.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия указанной секции.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string[] Свойства секции в виде ассоциативного массива, ключами которого являются имена свойств, а значениями их значения.
   */
  public function getSection($section){
    InvalidArgumentException::verify($section, 's', [1]);
    if(!isset($this->content[$section])){
      throw new NotFoundDataException('Запрашиваемая секция ['.$section.'] отсутствует.');
    }
    return $this->content[$section];
  }

  /**
   * Метод возвращает все секции в файле.
   * @return string[][] Двумерный ассоциативный массив, имеющий следующую структуру: [имяСекции => [имяСвойства => значение, ...], ...].
   */
  public function getAll(){
    return $this->content;
  }

  /**
   * Метод устанавливает значение свойству.
   * @param string $section Целевая секция.
   * @param string $key Имя свойства.
   * @param string $value Устанавливаемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function set($section, $key, $value){
    InvalidArgumentException::verify($section, 's', [1]);
    InvalidArgumentException::verify($key, 's', [1]);
    InvalidArgumentException::verify($value, 's');
    $this->content[$section][$key] = $value;
  }

  /**
   * Метод удаляет свойство.
   * @param string $section Целевая секция.
   * @param string $key Имя свойства.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function remove($section, $key){
    InvalidArgumentException::verify($section, 's', [1]);
    InvalidArgumentException::verify($key, 's', [1]);
    if(isset($this->content[$section]) && isset($this->content[$section][$key])){
      unset($this->content[$section][$key]);
    }
  }

  /**
   * Метод удаляет секцию.
   * @param string $section Целевая секция.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function removeSection($section){
    InvalidArgumentException::verify($section, 's', [1]);
    if(isset($this->content[$section])){
      unset($this->content[$section]);
    }
  }

  /**
   * Метод проверяе, существует ли свойство.
   * @param string $section Целевая секция.
   * @param string $key Имя свойства.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - если свойство существует, иначе - false.
   */
  public function hasKey($section, $key){
    InvalidArgumentException::verify($section, 's', [1]);
    InvalidArgumentException::verify($key, 's', [1]);
    if(!isset($this->content[$section])){
      return false;
    }
    return isset($this->content[$section][$key]);
  }

  /**
   * Метод проверяет, существует ли секция.
   * @param string $section Целевая секция.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - если секция существует, иначе - false.
   */
  public function hasSection($section){
    InvalidArgumentException::verify($section, 's', [1]);
    return isset($this->content[$section]);
  }
} 