<?php
namespace D\library\patterns\structure\identification;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Классическая реализация интерфейса OID
 * @author Artur Sh. Mamedbekov
 */
trait TOID{
  /**
   * @var string|null Идентификатор объекта или null - если объект не идентифицирован.
   */
  private $OID = null;

  /**
   * Данная реализация использует конструктор класса не передавая ему параметров, это может привести к ошибке в случае, если конструктор ожидает параметры при вызове. Для обхода этого ограничения можно переопределить метод.
   * @prototype \PPHP\tools\patterns\database\identification\OID
   */
  public static function getProxy($OID){
    // Верификация параметра выполняется в setOID.
    /**
     * @var OID $proxy Proxy вызываемого объекта.
     */
    $proxy = new static;
    assert('is_a($proxy, "D\library\patterns\structure\identification\OID")');
    $proxy->setOID($OID);

    return $proxy;
  }

  /**
   * @prototype \PPHP\tools\patterns\database\identification\OID
   */
  public final function getOID(){
    return $this->OID;
  }

  /**
   * @prototype \PPHP\tools\patterns\database\identification\OID
   */
  public final function setOID($OID){
    if($this->isOID()){
      throw new OIDException('Предотвращение коллизии.');
    }
    InvalidArgumentException::verify($OID, 's', [1]);
    $this->OID = $OID;
  }

  /**
   * @prototype \PPHP\tools\patterns\database\identification\OID
   */
  public final function isOID(){
    return !is_null($this->OID);
  }
}
