<?php
namespace D\library\patterns\structure\memento;

use D\library\patterns\entity\exceptions\SemanticException;

/**
 * Данное исключение свидетельствует о том, что производится попытка доступа к закрытым свойствам объекта.
 * @author Artur Sh. Mamedbekov
 */
class AccessException extends SemanticException{
}
