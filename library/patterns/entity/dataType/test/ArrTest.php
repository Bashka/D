<?php
namespace D\library\patterns\entity\dataType\test;

use D\library\patterns\entity\dataType\Arr;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class ArrTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать true - если параметр имеет тип array, иначе - false.
   * @covers D\library\patterns\entity\dataType\Arr::hasType
   */
  public function testShouldReturnTrueIfArgArr(){
    $this->assertTrue(Arr::hasType([]));
    $this->assertFalse(Arr::hasType(''));
  }

  /**
   * Должен возвращать true - если все элементы массива имеют указанный тип, иначе - false.
   * @covers D\library\patterns\entity\dataType\Arr::hasType
   */
  public function testShouldReturnTrueIfAllElementsArrVerifyType(){
    $this->assertTrue(Arr::hasType(['a', 'b', 'c'], 'string'));
    $this->assertTrue(Arr::hasType([1, 2, 3], 'integer'));
    $this->assertTrue(Arr::hasType([1.0, 2.0, 3.0], 'float'));
    $this->assertTrue(Arr::hasType([true, true, false], 'boolean'));
    $this->assertTrue(Arr::hasType([[], [], []], 'array'));
    $this->assertTrue(Arr::hasType([new \stdClass, new \stdClass, new \stdClass], 'object'));
    $this->assertFalse(Arr::hasType(['a', 'b', 1], 'string'));
    $this->assertFalse(Arr::hasType([1, 2, 3.0], 'integer'));
    $this->assertFalse(Arr::hasType([1.0, 2.0, 3], 'float'));
    $this->assertFalse(Arr::hasType([true, true, 0], 'boolean'));
    $this->assertFalse(Arr::hasType([[], [], ''], 'array'));
    $this->assertFalse(Arr::hasType([new \stdClass, new \stdClass, ''], 'object'));
  }

  /**
   * Должен возвращать true - если размер массива входит в допустимый диапазон, иначе - false.
   * @covers D\library\patterns\entity\dataType\Arr::hasLength
   */
  public function testShouldReturnTrueIfArgGoodLength(){
    $this->assertTrue(Arr::hasLength([], 0));
    $this->assertTrue(Arr::hasLength([1], 0));
    $this->assertTrue(Arr::hasLength([1,2,3], 0, 5));
    $this->assertFalse(Arr::hasLength([], 1));
    $this->assertFalse(Arr::hasLength([1,2,3], 0, 1));
  }
}
 