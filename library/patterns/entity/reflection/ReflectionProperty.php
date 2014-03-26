<?php
namespace D\library\patterns\entity\reflection;

use D\library\patterns\structure\metadata\Described;

/**
 * Отражение свойства класса, расширенное возможностью аннотирования.
 * @author  Artur Sh. Mamedbekov
 */
class ReflectionProperty extends \ReflectionProperty implements Described{
  use TDocMetadata;
}
