<?php
namespace D\library\patterns\entity\dataType\test;

use D\library\patterns\entity\dataType\String;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class StringTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать true - если параметр имеет тип string, иначе - false.
   * @covers D\library\patterns\entity\dataType\String::hasType
   */
  public function testShouldReturnTrueIfArgString(){
    $this->assertTrue(String::hasType(''));
    $this->assertFalse(String::hasType(1));
  }

  /**
   * Должен возвращать true - если длина строки входит в допустимый диапазон, иначе - false.
   * @covers D\library\patterns\entity\dataType\String::hasLength
   */
  public function testShouldReturnTrueIfArgGoodLength(){
    $this->assertTrue(String::hasLength('', 0));
    $this->assertTrue(String::hasLength('A', 1));
    $this->assertTrue(String::hasLength('Hello', 0, 5));
    $this->assertTrue(String::hasLength('ая', 0, 2));
    $this->assertFalse(String::hasLength('', 1));
    $this->assertFalse(String::hasLength('Hello', 0, 1));
  }

  /**
   * Должен выбрасывать исключение, если параметр не типа string.
   * @covers D\library\patterns\entity\dataType\String::__construct
   */
  public function testShouldThrowExceptionIfArgNotString(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new String(1);
  }

  /**
   * Должен возвращать число символов в строке для кодировки UTF-8.
   * @covers D\library\patterns\entity\dataType\String::getLength
   */
  public function testShouldReturnLengthString(){
    $o = new String('a1я');
    $this->assertEquals(3, $o->getLength());
  }

  /**
   * Должен возвращать 0, если строка пуста.
   * @covers D\library\patterns\entity\dataType\String::getLength
   */
  public function testShouldReturnZeroIfEmptyString(){
    $o = new String('');
    $this->assertEquals(0, $o->getLength());
  }

  /**
   * Должен возвращать текущий символ.
   * @covers D\library\patterns\entity\dataType\String::current
   */
  public function testShouldReturnCurrentChar(){
    $o = new String('ая');
    $this->assertEquals('а', $o->current());
  }

  /**
   * Должен возвращать пусую строку, если строка пуста.
   * @covers D\library\patterns\entity\dataType\String::current
   */
  public function testShouldReturnEmptyStringIfStringEmpty(){
    $o = new String('');
    $this->assertEquals('', $o->current());
  }

  /**
   * Должен сдвигать указатель на следующий символ.
   * @covers D\library\patterns\entity\dataType\String::next
   */
  public function testShouldMovePoint(){
    $o = new String('abcабв');
    $this->assertEquals(0, $o->key());
    $o->next();
    $this->assertEquals(1, $o->key());
    $o->next();
    $o->next();
    $o->next();
    $o->next();
    $this->assertEquals(5, $o->key());
  }

  /**
   * Максимальным значением указателя является последний символ.
   * @covers D\library\patterns\entity\dataType\String::next
   */
  public function testMaxPointLastSymbol(){
    $o = new String('abcабв');
    $o->next();
    $o->next();
    $o->next();
    $o->next();
    $o->next();
    $o->next();
    $o->next();
    $this->assertEquals(5, $o->key());
  }

  /**
   * Должен возвращать текущее значение указателя.
   * @covers D\library\patterns\entity\dataType\String::key
   */
  public function testShouldReturnPoint(){
    $o = new String('abcабв');
    $this->assertEquals(0, $o->key());
    $o->next();
    $this->assertEquals(1, $o->key());
  }

  /**
   * Должен определять, возможен ли очередной сдвиг указателя.
   * @covers D\library\patterns\entity\dataType\String::valid
   */
  public function testShouldValidPoint(){
    $o = new String('abcабв');
    $this->assertTrue($o->valid());
    $o->next();
    $this->assertTrue($o->valid());
    $o->next();
    $o->next();
    $o->next();
    $o->next();
    $this->assertFalse($o->valid());
  }

  /**
   * Должен устанавливать внутренний указатель на начало строки.
   * @covers D\library\patterns\entity\dataType\String::rewind
   */
  public function testShouldRewindPoint(){
    $o = new String('abc');
    $o->next();
    $this->assertEquals(1, $o->key());
    $o->rewind();
    $this->assertEquals(0, $o->key());
  }

  /**
   * Должен сдвигать указатель на указанную позицию.
   * @covers D\library\patterns\entity\dataType\String::jump
   */
  public function testShouldJumpPoint(){
    $o = new String('abc');
    $this->assertEquals(0, $o->key());
    $o->jump(2);
    $this->assertEquals(2, $o->key());
  }

  /**
   * Должен выбрасывать исключение, при выходе за границы строки.
   * @covers D\library\patterns\entity\dataType\String::jump
   */
  public function testShouldThrowExceptionIfOutOfBoundsStr(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $o = new String('abc');
    $o->jump(3);
  }

  /**
   * Должен выполнять поиск подстроки в строке.
   * @covers D\library\patterns\entity\dataType\String::search
   */
  public function testShouldSearchNeedle(){
    $o = new String('abcабв');
    $this->assertEquals(1, $o->search('b'));
    $this->assertEquals(4, $o->search('б'));
  }

  /**
   * Должен возвращать false, если подстрока не найдена.
   * @covers D\library\patterns\entity\dataType\String::search
   */
  public function testShouldReturnFalseIfNeedleNotFound(){
    $o = new String('abcабв');
    $this->assertFalse($o->search('d'));
  }

  /**
   * Должен выбрасывать исключение, если выполняется поиск пустой строки.
   * @covers D\library\patterns\entity\dataType\String::search
   */
  public function testShouldThrowExceptionIfNeedleEmpty(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $o = new String('abcабв');
    $o->search('');
  }

  /**
   * Должен выполнять смещение указателя на место найденой подстроки.
   * @covers D\library\patterns\entity\dataType\String::searchAndJump
   */
  public function testShouldJumpInSearchSubstr(){
    $o = new String('abc');
    $this->assertTrue($o->searchAndJump('b'));
    $this->assertEquals(1, $o->key());
  }

  /**
   * Не должен смещать указатель если строка не найдена.
   * @covers D\library\patterns\entity\dataType\String::searchAndJump
   */
  public function testShouldReturnFalseIfSubstrNotFound(){
    $o = new String('abc');
    $this->assertFalse($o->searchAndJump('d'));
    $this->assertEquals(0, $o->key());
  }

  /**
   * Должен возвращать подстроку сдвигая указатель.
   * @covers D\library\patterns\entity\dataType\String::get
   */
  public function testShouldReturnSubstr(){
    $o = new String('abcабв');
    $this->assertEquals('ab', $o->get(2)->getVal());
    $this->assertEquals('c', $o->get(1)->getVal());
    $this->assertEquals('', $o->get(0)->getVal());
    $this->assertEquals('абв', $o->get(3)->getVal());
    $o->rewind();
    $o->next();
    $this->assertEquals('b', $o->get(1)->getVal());
    $this->assertEquals('cабв', $o->get()->getVal());

  }

  /**
   * Должен выбрасывать исключение при выходе за границы строки.
   * @covers D\library\patterns\entity\dataType\String::get
   */
  public function testShouldThrowExceptionIfGetLengthOutOfBoundStr(){
    $o = new String('abcабв');
    $o->get(6);
  }

  /**
   * Должен возвращать подстроку до искомой строки.
   * @covers D\library\patterns\entity\dataType\String::getUpTo
   */
  public function testShouldReturnSubstrToNeedle(){
    $o = new String('abcабв');
    $this->assertEquals('abcа', $o->getUpTo('б')->getVal());
  }

  /**
   * Должен возвращать false, если искомой строки не найдено.
   * @covers D\library\patterns\entity\dataType\String::getUpTo
   */
  public function testShouldReturnFalseIfNeedleNotFound2(){
    $o = new String('abc');
    $this->assertFalse($o->getUpTo('d'));
  }

  /**
   * Должен сдвигать указатель.
   * @covers D\library\patterns\entity\dataType\String::getUpTo
   */
  public function testShouldMovePoint2(){
    $o = new String('abcабв');
    $o->getUpTo('аб');
    $this->assertEquals(5, $o->key());
  }
}
 