<?php
namespace D\library\patterns\entity\io;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Данный класс представляет классическую реализацию выходного потока данных.
 * Дочернему классу достаточно реализовать метод write, использующий определенный здесь указатель на ресурс.
 * @author  Artur Sh. Mamedbekov
 */
abstract class OutStream implements Writer{
  /**
   * @var resource Указатель на ресурс, с которым работает объект.
   */
  protected $resource;

  /**
   * @param resource $resource Указатель на ресурс.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или пустой строки.
   */
  public function __construct($resource){
    InvalidArgumentException::verify($resource, 'r');
    $this->resource = $resource;
  }
}
