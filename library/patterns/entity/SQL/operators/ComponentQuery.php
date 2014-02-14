<?php
namespace D\library\patterns\entity\SQL\operators;

use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\conversion\Restorable;
use D\library\patterns\structure\conversion\TRestorable;

/**
 * Классы, наследующие поведение от данного класса являются объектными SQL инструкциями или компонентами.
 * @author Artur Sh. Mamedbekov
 */
abstract class ComponentQuery implements Interpreter, Restorable{
  use TRestorable;

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function updateString(&$string){
    $string = preg_replace('/(\n|\t|\r)/u', '', $string);
    $string = preg_replace('/(  +)/u', ' ', $string);
  }
}
