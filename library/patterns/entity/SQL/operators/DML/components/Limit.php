<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Ограничитель выборки. Платформо-зависимый компонент.
 * @author Artur Sh. Mamedbekov
 */
class Limit extends ComponentQuery{
  /**
   * @var integer|null Число отбираемых записей или null - если значение параметризовано.
   */
  private $countRow;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['LIMIT ((' . self::getPatterns()['value'].')|(\?))'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['value' => '[1-9][0-9]*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    $components = explode(' ', $string);

    if($components[1] == '?'){
      return new static;
    }
    else{
      return new static((int) $components[1]);
    }
  }

  /**
   * @param integer $countRow [optional] Число отбираемых записей или null - если значение параметризовано.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct($countRow = null){
    InvalidArgumentException::verify($countRow, 'in', [1]);
    $this->countRow = $countRow;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);
    if(is_null($this->countRow)){
      $this->countRow = '?';
    }
    switch($driver){
      case 'sqlsrv': // MS SQL Server
        return 'TOP ' . $this->countRow;
      case 'firebird': // Firebird
        return 'FIRST ' . $this->countRow;
      case 'oci': // Oracle
        return 'ROWNUM <= ' . $this->countRow;
      case 'mysql': // MySQL
      case 'pgsql': // PostgreSQL
        return 'LIMIT ' . $this->countRow;
      case 'ibm': // DB2
        return 'FETCH FIRST ' . $this->countRow . ' ROWS ONLY';
      default:
        throw InvalidArgumentException::getValidException('sqlsrv|firebird|oci|mysql|pgsql|ibm', $driver);
    }
  }

  /**
   * Метод возвращает число отбираемых записей.
   * @return integer|null Число отбираемых записей или null - если значение параметризовано.
   */
  public function getCountRow(){
    return $this->countRow;
  }
}
