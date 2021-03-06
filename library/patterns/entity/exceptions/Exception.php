<?php
namespace D\library\patterns\entity\exceptions;

/**
 * Корневой класс исключений системы.
 * Все используемые в системе классы исключений должны быть дочерними по отношению к данному классу.
 * Данный класс и его дочерние классы исключений могут быть сериализованы для передачи на уровень представления.
 * @author Artur Sh. Mamedbekov
 */
abstract class Exception extends \Exception implements \JsonSerializable{
  /**
   * @prototype \JsonSerializable
   */
  public function JsonSerialize(){
    $trace = $this->getTrace();
    foreach($trace as $k => $exc){
      $trace[$k] = new \stdClass();
      $trace[$k]->file = $exc['file'];
      $trace[$k]->line = $exc['line'];
    }

    return ['type' => 1, 'class' => get_called_class(), 'message' => $this->message, 'code' => $this->code, 'file' => $this->file, 'line' => $this->line, 'trace' => $trace];
  }
}