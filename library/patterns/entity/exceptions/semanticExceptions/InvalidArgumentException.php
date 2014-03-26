<?php
namespace D\library\patterns\entity\exceptions\semanticExceptions;

use D\library\patterns\entity\dataType\Arr;
use D\library\patterns\entity\dataType\Boolean;
use D\library\patterns\entity\dataType\Float;
use D\library\patterns\entity\dataType\Integer;
use D\library\patterns\entity\dataType\String;
use D\library\patterns\entity\exceptions\SemanticException;

/**
 * Исключение, свидетельствующее о получении параметра недопустимого типа или невалидного значения.
 * @author Artur Sh. Mamedbekov
 */
class InvalidArgumentException extends SemanticException{
  /**
   * Метод выполняет верификация данных (как правило, параметров) и выбрасывает исключение InvalidArgumentException, если верификация не пройдена.
   * @param mixed $val Проверяемые данные.
   * @param string $type Допустимый тип данных. Данный аргумент должен состоять из маркеров допустимых типов. В случае, если проверяемые данные не соответствуют ни одному из перечисленных типов, выбрасывается исключение. Доступны следующие маркеры: n - допустим null, s - допустим string, i - допустим integer, f - допустим float, b - допустим boolean, a - допустим array, o - допустим объект, r - допустим resource, S - допустим массив string, I - допустим массив integer, F - допустим массив float, B - допустим массив boolean, A - допустим многомерный массив, O - допустим массив объектов, R - допустим массив ресурсов.
   * @param integer[] $length Диапазон длины значения. Диапазон должен состоять из одного или двух целочисленных или дробных значений, и определять допустимое значение процеряемых данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при нарушении правил верификации проверяемым значением.
   */
  public static function verify($val, $type, array $length = null){
    assert('is_string($type)');
    assert('is_null($length) || (count($length) >= 1 && count($length) <= 2)');
    // Обеспечение сквозной проверки для второго аргумента метода hasLength.
    if(!is_null($length) && !isset($length[1])){
      $length[1] = null;
    }
    /**
     * @var string[] $errsType Список несоответствий допустимым типам.
     */
    $errsType = [];
    /**
     * @var string|null $errLength Флаг несоответствия допустимой длины.
     */
    $errLength = null;
    $countTypes = strlen($type);
    for($i = 0; $i < $countTypes; $i++){
      switch($type[$i]){
        case 'n':
          if(!is_null($val)){
            $errsType[] = 'null';
          }
          break;
        case 's':
          if(!String::hasType($val)){
            $errsType[] = 'string';
          }
          else if(!is_null($length) && !String::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'i':
          if(!Integer::hasType($val)){
            $errsType[] = 'integer';
          }
          else if(!is_null($length) && !Integer::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'f':
          if(!Float::hasType($val)){
            $errsType[] = 'float';
          }
          else if(!is_null($length) && !Float::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'b':
          if(!Boolean::hasType($val)){
            $errsType[] = 'boolean';
          }
          break;
        case 'a':
          if(!Arr::hasType($val)){
            $errsType[] = 'array';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'o':
          if(!is_object($val)){
            $errsType[] = 'object';
          }
          break;
        case 'r':
          if(!is_resource($val)){
            $errsType[] = 'resource';
          }
          break;
        case 'S':
          if(!Arr::hasType($val, 'string')){
            $errsType[] = 'string[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'I':
          if(!Arr::hasType($val, 'integer')){
            $errsType[] = 'integer[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'F':
          if(!Arr::hasType($val, 'float')){
            $errsType[] = 'float[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'B':
          if(!Arr::hasType($val, 'boolean')){
            $errsType[] = 'boolean[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'A':
          if(!Arr::hasType($val, 'array')){
            $errsType[] = 'array[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'O':
          if(!Arr::hasType($val, 'object')){
            $errsType[] = 'object[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        case 'R':
          if(!Arr::hasType($val, 'resource')){
            $errsType[] = 'resource[]';
          }
          else if(!is_null($length) && !Arr::hasLength($val, $length[0], $length[1])){
            $errLength = $length[0].', '.$length[1];
          }
          break;
        default:
          throw self::getValidException('n|s|i|f|b|a|o|r|S|I|F|B|A|O|R', $type[$i]);
      }
    }
    // Если нет ни одного соответствия, выброс исключения.
    if(count($errsType) === $countTypes){
      throw self::getTypeException(implode('|', $errsType), gettype($val));
    }
    // Если нет соответствия длины, выброс исключения.
    if(!is_null($errLength)){
      throw self::getValidException($errLength, (string) $val);
    }
  }

  /**
   * Метод возвращает объект данного класса, устанавливая ему сообщение о недопустимом типе параметра.
   * @param string $assertType Ожидаемый тип параметра в виде строки.
   * @param string $actualType Реальный тип параметра.
   * @param integer $code [optional] Код ошибки.
   * @param \Exception $previous [optional] Причина.
   * @return \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Объект данного типа с предустановленным сообщением.
   */
  public static function getTypeException($assertType, $actualType, $code = 1, \Exception $previous = null){
    assert('is_string($assertType)');
    assert('is_string($actualType)');
    assert('is_integer($code)');

    return new InvalidArgumentException('Недопустимый тип параметра. Ожидается [' . $assertType . '] вместо [' . $actualType . '].', $code, $previous);
  }

  /**
   * Метод возвращает объект данного класса, устанавливая ему сообщение о недопустимом значении параметра.
   * @param string $mask Маска верификации значения параметра. Здесь могут использоваться верификаторы регулярных выражений или логические операторы.
   * @param mixed $actualData Реальное значение параметра.
   * @param integer $code [optional] Код ошибки.
   * @param \Exception $previous [optional] Причина.
   * @return \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Объект данного типа с предустановленным сообщением.
   */
  public static function getValidException($mask, $actualData, $code = 1, \Exception $previous = null){
    assert('is_string($mask)');
    assert('is_string($actualData)');
    assert('is_integer($code)');

    return new InvalidArgumentException('Недопустимое значение параметра. Ожидается соответствие маске [' . $mask . '] вместо [' . $actualData . '].', $code, $previous);
  }
}