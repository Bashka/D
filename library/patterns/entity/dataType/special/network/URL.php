<?php
namespace D\library\patterns\entity\dataType\special\network;

use D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress;
use D\library\patterns\entity\dataType\Wrap;

/**
 * Класс-обертка служит для представления и верификации URL адреса.
 * Допустимый тип: совокупность следующих элементов: <протокол><IP|домен>[:<порт>][/адрес в файловой системе]
 * @author Artur Sh. Mamedbekov
 */
class URL extends Wrap{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var \D\library\patterns\entity\dataType\special\network\Report Протокол.
   */
  protected $report;

  /**
   * @var \D\library\patterns\entity\dataType\special\network\IPAddress|\D\library\patterns\entity\dataType\special\network\DomainName Адрес ресурса.
   */
  protected $address;

  /**
   * @var \D\library\patterns\entity\dataType\special\network\Port Порт.
   */
  protected $port;

  /**
   * @var \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress Физический адрес ресурса.
   */
  protected $fileSystemAddress;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return [Report::getMasks()[0] . '((?:' . DomainName::getMasks()[0] . ')|(?:' . IPAddress4::getMasks()[0] . '))(?::(' . Port::getMasks()[0] . '))?(' . FileSystemAddress::getMasks()[0] . ')?'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    $m = parent::reestablish($string);
    $o = new self($string);
    $o->report = Report::reestablish($m[1] . '://');
    if(DomainName::isReestablish($m[2])){
      $o->address = DomainName::reestablish($m[2]);
    }
    else{
      $o->address = IPAddress4::reestablish($m[2]);
    }
    $o->port = Port::reestablish($m[7]);
    $o->fileSystemAddress = FileSystemAddress::reestablish($m[8]);

    return $o;
  }

  /**
   * @param boolean $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_string($val)){
      throw InvalidArgumentException::getTypeException('string', gettype($val));
    }
    $this->val = $val;
  }

  /**
   * Метод возвращает адрес ресурса.
   * @return \D\library\patterns\entity\dataType\special\network\IPAddress|\D\library\patterns\entity\dataType\special\network\DomainName Адрес ресурса.
   */
  public function getAddress(){
    return $this->address;
  }

  /**
   * Метод возвращает физический адрес ресурса.
   * @return \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress|null Физический адрес ресурса.
   */
  public function getFileSystemAddress(){
    return $this->fileSystemAddress;
  }

  /**
   * Метод возвращает порт ресурса.
   * @return \D\library\patterns\entity\dataType\special\network\Port|null Порт.
   */
  public function getPort(){
    return $this->port;
  }

  /**
   * Метод возвращает используемый протокол.
   * @return \D\library\patterns\entity\dataType\special\network\Report Протокол.
   */
  public function getReport(){
    return $this->report;
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    return $this->val;
  }
}
