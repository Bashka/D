<?php
namespace D\library\patterns\entity\reflection;

use D\library\patterns\structure\metadata\Described;

/**
 * Отражение класса, расширенное возможностью аннотирования.
 * @author  Artur Sh. Mamedbekov
 */
class ReflectionClass extends \ReflectionClass implements Described{
  use TDocMetadata;
}
