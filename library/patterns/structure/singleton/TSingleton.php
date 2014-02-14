<?php
namespace D\library\patterns\structure\singleton;

use D\library\patterns\entity\exceptions\semanticExceptions\LackException;

/**
 * Классическая реализация интерфейса Singleton.
 * Важно помнить, что данная реализация использует конструктор класса без аргументов, потому использование конструктора с обязательными аргументами может привести к ошибке.
 * @author Artur Sh. Mamedbekov
 */
trait TSingleton{
  /**
   * @var array Множество экземпляров класса, относящихся к различным уровням иерархии наследования.
   */
  protected static $instance = [];

  /**
   * @prototype \D\library\patterns\structure\singleton\Singleton
   */
  public final static function getInstance(){
    // Определение целевого класса в иерархии наследования.
    $calledClass = get_called_class();
    if(!isset(self::$instance[$calledClass])){
      self::$instance[$calledClass] = new static;
    }

    return self::$instance[$calledClass];
  }

  /**
   * Попытка клонирования приводит объекта данного класса приводит к выбросу исключения.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается при вызове метода.
   */
  final function __clone(){
    throw new LackException('Невозможно клонировать класс, реализующий интерфейс Singleton');
  }

  /**
   * Конструктор класса закрыт для использования.
   */
  private function __construct(){
  }
}
