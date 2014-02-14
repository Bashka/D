<?php
namespace D\library\patterns\entity\dataType\test;

use D\library\patterns\entity\dataType\Integer;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class IntegerTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать true - если параметр имеет тип integer, иначе - false.
   * @covers D\library\patterns\entity\dataType\Integer::hasType
   */
  public function testShouldReturnTrueIfArgInteger(){
    $this->assertTrue(Integer::hasType(1));
    $this->assertFalse(Integer::hasType(''));
  }

  /**
   * Должен возвращать true - если параметр входит в допустимый диапазон, иначе - false.
   * @covers D\library\patterns\entity\dataType\Integer::hasLength
   */
  public function testShouldReturnTrueIfArgGoodLength(){
    $this->assertTrue(Integer::hasLength(0, 0));
    $this->assertTrue(Integer::hasLength(1, 0));
    $this->assertTrue(Integer::hasLength(0, 0, 10));
    $this->assertFalse(Integer::hasLength(-1, 0));
    $this->assertFalse(Integer::hasLength(2, 0, 1));
  }

  /**
   * Должен выбрасывать исключение, если параметр не типа integer.
   * @covers D\library\patterns\entity\dataType\Integer::__construct
   */
  public function testShouldThrowExceptionIfArgNotInteger(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Integer('');
  }
}
 