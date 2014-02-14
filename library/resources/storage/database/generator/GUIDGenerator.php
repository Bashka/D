<?php
namespace D\library\resources\storage\database\generator;

use D\library\patterns\entity\dataType\special\system\GUID4;
use D\library\patterns\structure\identification\OIDGenerator;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;

/**
 * Генератор OID по средствам GUID v.4.
 * @author Artur Sh. Mamedbekov
 */
class GUIDGenerator implements OIDGenerator, Singleton{
use TSingleton;

  /**
   * @prototype D\library\patterns\structure\identification\OIDGenerator
   */
  public function generateOID(){
    return GUID4::generate()->getVal();
  }
}