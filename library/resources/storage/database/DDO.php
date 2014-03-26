<?php
namespace D\library\resources\storage\database;

use D\library\patterns\entity\SQL\operators\Operator;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Данный класс представляет расширенный PDO интерфейс.
 * Класс реагирует выбросом исключения в ответ на ошибку в запросе.
 * Класс позволяет выполнять запросы по средствам объектов пакета D\library\patterns\entity\SQL\operators.
 * @author Artur Sh. Mamedbekov
 */
class DDO extends \PDO{
  /**
   * Метод выполняет SQL запрос и возвращает результат его выполнения.
   * @param string $statement SQL запрос.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае появления ошибки во время исполнения запроса.
   * @return \PDOStatement Результат исполнения запроса.
   */
  public function query($statement){
    $resultQuery = parent::query($statement);
    if(((int) $this->errorCode()) != 0){
      throw new DDOException($this->errorInfo()[2], (int) $this->errorInfo()[0]);
    }

    return $resultQuery;
  }

  /**
   * Метод выполняет SQL запрос используя объектную SQL инструкцию пакета D\library\patterns\entity\SQL\operators.
   * @param \D\library\patterns\entity\SQL\operators\Operator $statement SQL оператор.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае появления ошибки во время исполнения запроса.
   * @return \PDOStatement Результат исполнения запроса.
   */
  public function objectQuery(Operator $statement){
    return $this->query($statement->interpretation($this->getAttribute(DDO::ATTR_DRIVER_NAME)));
  }

  /**
   * Метод выполняет транзакцию. При возникновении ошибки во время исполнения транзакции, она автоматически откатывается.
   * Если на момент вызова метода СУБД выполняет транзакцию, скрипт выполняется в контексте этой транзакции, но автоматически отменяет изменения в случае возникновения ошибки.
   * @param string[] $script Множество SQL запросов, входящих в транзакцию. Запросы выполняются поочередно.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае появления ошибки во время исполнения запроса.
   */
  public function multiQuery($script){
    InvalidArgumentException::verify($script, 'S', [1]);
    $inTransaction = $this->inTransaction();
    if(!$inTransaction){
      $this->beginTransaction();
    }
    /**
     * @var string $query
     */
    foreach($script as $query){
      try{
        $this->query($query);
      }
      catch(DDOException $exc){
        $this->rollBack();
        throw $exc;
      }
    }
    if(!$inTransaction){
      $this->commit();
    }
  }

  /**
   * Метод выполняет транзакцию из объектных SQL инструкций пакета D\library\patterns\entity\SQL\operators. При возникновении ошибки во время исполнения транзакции, она автоматически откатывается.
   * Если на момент вызова метода СУБД выполняет транзакцию, скрипт выполняется в контексте этой транзакции, но автоматически отменяет изменения в случае возникновения ошибки.
   * @param \D\library\patterns\entity\SQL\operators\Operator[] $script Множество объектных SQL инструкций, входящих в транзакцию. Запросы выполняются поочередно.
   * @throws \D\library\resources\storage\database\DDOException Выбрасывается в случае появления ошибки во время исполнения запроса.
   */
  public function multiObjectQuery($script){
    InvalidArgumentException::verify($script, 'O', [1]);
    $inTransaction = $this->inTransaction();
    if(!$inTransaction){
      $this->beginTransaction();
    }
    /**
     * @var Operator $query
     */
    foreach($script as $query){
      try{
        if($query instanceof Operator){
          $this->objectQuery($query);
        }
      }
      catch(DDOException $exc){
        $this->rollBack();
        throw $exc;
      }
    }
    if(!$inTransaction){
      $this->commit();
    }
  }
}