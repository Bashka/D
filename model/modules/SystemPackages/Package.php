<?php
namespace D\model\modules\SystemPackages;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Объекты данного класса представляют отражения пакетов, используемых для установки компонентов системы.
 * @author Artur Sh. Mamedbekov
 */
abstract class Package{
  /**
   * Имя файла конфигурации пакета.
   */
  const CONF_FILE_NAME = 'conf.ini';

  /**
   * @var \ZipArchive Обрабатываемый архив пакета.
   */
  protected $archive;

  /**
   * @var string[][] Ассоциативный массив, содержащий конфигурацию архива компонента. Массив имеет следующую структуру: [имяСекции => [имяСвойства => значение, ...], ...].
   */
  protected $conf;

  /**
   * Метод возвращает значение указанного свойства файла конфигурации пакета.
   * @param string $section Имя раздела.
   * @param string $name Имя свойства.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия свойства конфигурации.
   * @return string Значение свойства.
   */
  protected function get($section, $name){
    if(isset($this->conf[$section][$name])){
      return $this->conf[$section][$name];
    }
    else{
      throw new NotFoundDataException('Целевое свойство ['.$section.'::'.$name.'] отсутствует в файле конфигурации пакета.');
    }
  }

  /**
   * @param string $archiveAddress Адрес архива компонента.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого архива или его файла конфигурации.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function __construct($archiveAddress){
    InvalidArgumentException::verify($archiveAddress, 's', [1]);
    $this->archive = new \ZipArchive;
    if(file_exists($archiveAddress)){
      $this->archive->open($archiveAddress);
      if($this->archive->statName(self::CONF_FILE_NAME) === false){
        throw new NotExistsException('Отсутствует файл конфигурации архива [' . $archiveAddress . '].');
      }
      $this->conf = parse_ini_string($this->archive->getFromName(self::CONF_FILE_NAME), true);
    }
    else{
      throw new NotExistsException('Запрашиваемый архив [' . $archiveAddress . '] не найден.');
    }
  }

  /**
   * Метод возвращает имя компонента.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string Имя компонента.
   */
  public function getName(){
    if(isset($this->conf['Component']['name'])){
      return $this->conf['Component']['name'];
    }
    else{
      throw new NotFoundDataException('Целевое свойство [Component::name] отсутствует в файле конфигурации пакета.');
    }
  }

  /**
   * Метод возвращает конфигурацию пакета.
   * @return string[][] Ассоциативный массив, содержащий конфигурацию пакета. Массив имеет следующую структуру: [имяСекции => [имяСвойства => значение, ...], ...].
   */
  public function getConf(){
    return $this->conf;
  }

  /**
   * Метод возвращает архив пакета.
   * @return \ZipArchive Обрабатываемый архив.
   */
  public function getArchive(){
    return $this->archive;
  }
} 