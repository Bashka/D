<?php
namespace D\library\patterns\entity\dataType\test;

use D\library\patterns\entity\dataType\Float;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class FloatTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать true - если параметр имеет тип float, иначе - false.
   * @covers D\library\patterns\entity\dataType\Float::hasType
   */
  public function testShouldReturnTrueIfArgFloat(){
    $this->assertTrue(Float::hasType(1.0));
    $this->assertFalse(Float::hasType(''));
  }

  /**
   * Должен возвращать true - если параметр входит в допустимый диапазон, иначе - false.
   * @covers D\library\patterns\entity\dataType\Float::hasLength
   */
  public function testShouldReturnTrueIfArgGoodLength(){
    $this->assertTrue(Float::hasLength(0.0, 0.0));
    $this->assertTrue(Float::hasLength(1.0, 0));
    $this->assertTrue(Float::hasLength(0.0, 0.0, 10));
    $this->assertFalse(Float::hasLength(-1.0, 0.0));
    $this->assertFalse(Float::hasLength(2.0, 0.0, 1.0));
  }

  /**
   * Должен выбрасывать исключение, если параметр не типа float.
   * @covers D\library\patterns\entity\dataType\Float::__construct
   */
  public function testShouldThrowExceptionIfArgNotFloat(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Float('');
  }
}
 
