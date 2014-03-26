<?php
namespace D\library\patterns\entity\SQL\builder;

use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Table;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\patterns\entity\SQL\operators\DML as DML;

/**
 * Класс представляет фабрику объектной SQL инструкции Insert.
 * @author  Artur Sh. Mamedbekov
 */
class Insert implements Singleton{
  use TSingleton;

  /**
   * @var \D\library\patterns\entity\SQL\operators\DML\Insert Объектная SQL инструкция Insert.
   */
  protected $insert;

  /**
   * Метод создает новую объектную SQL инструкцию Insert.
   * @param string $table Имя целевой таблицы.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа.
   * @return \D\library\patterns\entity\SQL\builder\Insert Вызываемый объект.
   */
  public function table($table){
    InvalidArgumentException::verify($table, 's', [1]);
    $this->insert = new DML\Insert(new Table($table));

    return $this;
  }

  /**
   * Метод добавляет строку в инструкцию.
   * @param array $data Добавляемые данные в виде ассоциативного массива, ключами которого являются имена полей, а значениями входные данные.
   * @return \D\library\patterns\entity\SQL\builder\Insert Вызываемый объект.
   */
  public function data(array $data){
    foreach($data as $field => $value){
      $this->insert->addData(new Field($field), $value);
    }

    return $this;
  }

  /**
   * Метод возвращает полученную объектную SQL инструкцию Insert.
   * @return \D\library\patterns\entity\SQL\operators\DML\Insert Результат работы фабрики.
   */
  public function get(){
    return $this->insert;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    return $this->get()->interpretation($driver);
  }
}