<?php
namespace D\library\patterns\entity\reflection;

use D\library\patterns\structure\metadata\Described;

/**
 * Отражение параметра метода, расширенное возможностью аннотирования.
 * @author  Artur Sh. Mamedbekov
 */
class ReflectionParameter extends \ReflectionParameter implements Described{
  use TDocMetadata;
}
