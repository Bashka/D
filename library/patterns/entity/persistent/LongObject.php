<?php
namespace D\library\patterns\entity\persistent;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\reflection\Reflect;
use D\library\patterns\entity\reflection\TDocMetadata;
use D\library\patterns\entity\reflection\TReflect;
use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\conversion\RestorableAdapter;
use D\library\patterns\structure\identification\OID;
use D\library\patterns\structure\identification\TOID;
use D\library\patterns\structure\memento\Originator;
use D\library\patterns\structure\memento\TOriginator;
use D\library\patterns\structure\metadata\Described;

/**
 * Корневой родительский класс для всех персистентных объектов.
 * Данный класс может быть преобразован в строку и восстановлен из нее, способен возвращать отражения своих членов, имеет уникальный идентификатор, может быть аннотирован и возвращать свое состояние через хранителей.
 * Дочерние классы должны быть аннотированы согласно требованиям DataMapper, используемом для сохранения и восстановления объектов.
 * Дочерние классы должны реализовать методы getSavedState и setSavedState для правильного получения и записи состояния их экземпляров, согласно trait TOriginator.
 * @author Artur Sh. Mamedbekov
 */
abstract class LongObject extends RestorableAdapter implements Reflect, Described, OID, Originator, Interpreter{
  use TReflect, TDocMetadata, TOID, TOriginator;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getMasks($driver = null){
    return ['\$([A-Z\/a-z_]+):([0-9a-zA-Z_\-]+)'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    /**
     * @var string $m Лексемы.
     */
    $m = parent::reestablish($string);
    /**
     * @var LongObject $o Имя класса восстанавливаемого объекта.
     */
    $o = str_replace('/', '\\', $m[1]);

    return $o::getProxy($m[2]);
  }

  /**
   * Метод создает объектную ссылку вида: $/имяКласса:идентификаторОбъекта. В имени класса для разделение пакетов используется символ /.
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    if(!$this->isOID()){
      throw new NotFoundDataException('Объект не идентифицирован.');
    }

    return '$/' . str_replace('\\', '/', get_class($this)) . ':' . $this->getOID();
  }
}
