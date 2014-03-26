<?php
namespace D\library\patterns\structure\conversion;

use D\library\patterns\entity\exceptions\dataExceptions\StructureDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Реализация интерфейса Restorable по средствам шаблонов и их сочетаний.
 * Данная реализация использует шаблоны регулярных выражений для поиска и обработки лексем строки-основания.
 * @author Artur Sh. Mamedbekov
 */
trait TRestorable{
  /**
   * Метод последовательно применяет доступные шаблоны верификации к строке-основанию с целью определения шаблона, которому она соответствует, и поиска лексем.
   * @param string $string Строка-основание.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику работы метода.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return string[] Массив лексем, созданных первым подходящим шаблоном верификации. В качестве элемента с ключем key хранится ключ соответствующего шаблона верификации. В случае отсутствия подходящего шаблона верификации метод возвращает пустой массив.
   */
  private static function searchMask($string, $driver = null){
    InvalidArgumentException::verify($string, 's');
    static::updateString($string);
    assert('is_string($string)');
    $masks = static::getMasks($driver);
    assert('is_array($masks)');
    foreach($masks as $key => $mask){
      $matches = [];
      if(preg_match('/^' . $mask . '$/u', $string, $matches)){
        $matches['key'] = $key;
        assert('is_array($matches)');

        return $matches;
      }
    }

    return [];
  }

  /**
   * Метод возвращает массив шаблонов верификации, любому из которых должна соответствовать строка-основание.
   * В случае отсутствия соответствия, восстановление считается невозможным.
   * Возвращаемые шаблоны так же могут разделять строку-основание на лексемы для дальнейшей обработки.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику работы метода.
   * @return string[] Шаблоны верификации.
   */
  public static function getMasks($driver = null){
    return [];
  }

  /**
   * Метод может возвращать массив именованных лексем.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику работы метода.
   * @return string[] Массив именованных лексем.
   */
  public static function getPatterns($driver = null){
    return [];
  }

  /**
   * Метод последовательно применяет доступные шаблоны верификации к строке-основанию для поиска соответствия.
   * @prototype \PPHP\tools\patterns\interpreter\Restorable
   */
  public static function isReestablish($string, $driver = null){
    InvalidArgumentException::verify($string, 's');
    return (count(static::searchMask($string, $driver)) != 0);
  }

  /**
   * Данный метод должен быть переопределен в реализующем классе для уточнения механизма восстановления.
   * Метод определяет подхощящий шаблон верификации и возвращает массив лексем, полученных из строки-основания. В качестве элемента с ключем key этого массива, указывается ключ первого подходящего шаблона верификации.
   * @prototype \PPHP\tools\patterns\interpreter\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Верификация параметров выполняется в вызываемых методах.
    $mask = static::searchMask($string, $driver);
    if(empty($mask)){
      throw new StructureDataException('Недопустимая структура для объекта ' . get_called_class() . ' [' . $string . '].');
    }

    return $mask;
  }

  /**
   * Данный метод вызывается автоматически методом searchMask и служит для подготовки строки-основания к верификации и поиску лексем.
   * Метод может быть переопределен конкретным классом, использующим данную реализацию.
   * Данный метод вызывается от имени вызываемого объекта.
   * @param string $string Строка-основание.
   */
  public static function updateString(&$string){
  }
}