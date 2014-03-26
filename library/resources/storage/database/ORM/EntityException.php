<?php
namespace D\library\resources\storage\database\ORM;

use D\library\patterns\entity\exceptions\DataExceptions;

/**
 * Данное исключение свидетельствует о том, что персистентный объект имеет недопустимую структуру и не может быть обработан.
 */
class EntityException extends DataExceptions{

}