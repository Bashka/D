<?php
namespace D\library\patterns\entity\dataType;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс-обертка служит для верификации и представления строковых данных.
 * @author  Artur Sh. Mamedbekov
 */
class String extends Wrap implements \Iterator{
  /**
   * @var string Оборачиваемое значение.
   */
  private $val;

  /**
   * @var integer Позиция указателя текущего символа.
   */
  private $point;

  /**
   * @var integer Число символов в строке.
   */
  private $length;

  /**
   * @prototype \PPHP\tools\patterns\interpreter\TRestorable
   */
  public static function getMasks($driver = null){
    return ['.*'];
  }

  /**
   * Допустимый тип: любая строка.
   * @prototype \PPHP\tools\patterns\interpreter\Restorable
   */
  public static function reestablish($string, $driver = null){
    InvalidArgumentException::verify($string, 's');

    return new String((string) $string);
  }

  /**
   * Метод проверяет, имеет ли параметр тип string.
   * @param mixed $val Проверяемое значение.
   * @return boolean true - если параметр допустимого типа, иначе - false.
   */
  public static function hasType($val){
    return is_string($val);
  }

  /**
   * Метод определяет, имеет ли строка допустимую длину.
   * @param string $val Проверяемое значение.
   * @param integer $min Минимальная длина строки.
   * @param integer $max [optional] Максимальная длина строки.
   * @return boolean true - если строка имеет допустимую длину, иначе - false.
   */
  public static function hasLength($val, $min, $max = null){
    assert('is_string($val)');
    assert('is_integer($min) && $min >= 0');
    assert('is_null($max) || (is_integer($max) && $max > $min)');
    $length = mb_strlen($val, 'utf-8');
    if(is_null($max)){
      return ($length >= $min);
    }
    else{
      return ($length >= $min && $length <= $max);
    }
  }

  /**
   * @param string $val Оборачиваемое значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  public function __construct($val){
    if(!is_string($val)){
      throw InvalidArgumentException::getTypeException('string', gettype($val));
    }
    $this->val = $val;
    $this->point = 0;
    $this->length = mb_strlen($val, 'utf-8');
  }

  /**
   * Метод возвращает оборачиваемое значение.
   * @return string Оборачиваемое значение.
   */
  public function getVal(){
    assert('is_string($this->val)');
    return $this->val;
  }

  /**
   * Метод возвращает число символов в строке.
   * @return integer Число символов в строке.
   */
  public function getLength(){
    return $this->length;
  }

  /**
   * Метод возвращает символ, на который в данный момент указывает указатель.
   * @return string Текущий символ или пустая строка, если строка пуста.
   */
  public function current(){
    if($this->length == 0){
      return '';
    }
    return mb_substr($this->val, $this->point, 1, 'utf-8');
  }

  /**
   * Должен сдвигать внутренний указатель на следующий символ.
   */
  public function next(){
    // При максимальном сдвиге указатель указывает на последний символ.
    if($this->point < $this->length-1){
      $this->point++;
    }
    assert('$this->point < $this->length');
  }

  /**
   * Метод возвращает значение указателя.
   * @return integer Позиция текущего символа.
   */
  public function key(){
    return $this->point;
  }

  /**
   * Метод определяет, возможен ли очередной сдвиг указателя.
   * @return boolean true - если сдвиг возможен, иначе - false.
   */
  public function valid(){
    return ($this->point < $this->length-1);
  }

  /**
   * Метод устанавливает указатель на начало строки.
   */
  public function rewind(){
    $this->point = 0;
  }

  /**
   * Метод смещает внутренний указатель на указанную позицию.
   * @param integer $point Новая позиция указателя.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или при выходе указателя за границы строки.
   */
  public function jump($point){
    InvalidArgumentException::verify($point, 'i', [0, $this->length-1]);
    $this->point = $point;
  }

  /**
   * Метод выполняет поиск подстроки в строке начиная с текущей позиции и до конца строки.
   * @param string $needle Искомая подстрока.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или пустой строки.
   * @return integer|boolean Позиция искомой подстроки или false - если ничего не найдено.
   */
  public function search($needle){
    InvalidArgumentException::verify($needle, 's', [1]);
    return mb_strpos($this->val, $needle, $this->point, 'utf-8');
  }

  /**
   * Метод выполняет поиск подстроки в строке и устанавливает внутренний указатель на место найденой подстроки.
   * @param string $needle Искомая подстрока.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return boolean true - если подстрока найдена и смещение указателя выполнено успешно, иначе - false.
   */
  public function searchAndJump($needle){
    // Верификация параметра выполняется в вызываемых методах.
    $point = $this->search($needle);
    if(is_integer($point)){
      $this->jump($point);
      return true;
    }
    else{
      return false;
    }
  }

  /**
   * Метод возвращает подстроку, начиная от позиции указателя и до указанного числа символов, сдвигая текущий указатель.
   * @param integer $length [optional] Число отбираемых символов. Если параметр не передан, отбираются все символы до конца строки.
   * @return \D\library\patterns\entity\dataType\String Запрашиваемая подстрока.
   */
  public function get($length = null){
    InvalidArgumentException::verify($length, 'ni', [0, $this->length - $this->point]);
    $substr = new String(mb_substr($this->val, $this->point, $length, 'utf-8'));
    $newPositionPoint = $this->point + $length;
    if($newPositionPoint < $this->length){
      $this->jump($this->point + $length);
    }
    else{
      $this->jump($this->length - 1);
    }
    return $substr;
  }

  /**
   * Метод возвращает подстроку, начиная от позиции указателя и до позиции за указанной подстрокой, сдвигая текущий указатель.
   * @param string $needle Подстрока, ограничивающая выборку.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return \D\library\patterns\entity\dataType\String|boolean Запрашиваемая подстрока или false - если ограничивающая подстрока не найдена.
   */
  public function getUpTo($needle){
    // Верификация параметра выполняется в вызываемых методах.
    $point = $this->search($needle);
    if(!is_bool($point)){
      $substr = $this->get($point - $this->point);
      $this->jump($point + mb_strlen($needle, 'utf-8'));
      return $substr;
    }
    else{
      return false;
    }
  }
}