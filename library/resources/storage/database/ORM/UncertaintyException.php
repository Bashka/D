<?php
namespace D\library\resources\storage\database\ORM;

use D\library\patterns\entity\exceptions\EnvironmentException;

/**
 * Данное исключение свидетельствует о том, что востановление объекта из базы данных невозможно из-за отсутствия данных либо наличия множества данных с данным идентификатором.
 */
class UncertaintyException extends EnvironmentException{
}
