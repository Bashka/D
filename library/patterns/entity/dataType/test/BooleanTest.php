<?php
namespace D\library\patterns\entity\dataType\test;

use D\library\patterns\entity\dataType\Boolean;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class BooleanTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен возвращать true - если параметр имеет тип boolean, иначе - false.
   * @covers D\library\patterns\entity\dataType\Boolean::hasType
   */
  public function testShouldReturnTrueIfArgBoolean(){
    $this->assertTrue(Boolean::hasType(true));
    $this->assertTrue(Boolean::hasType(false));
    $this->assertFalse(Boolean::hasType(''));
  }

  /**
   * Должен выбрасывать исключение, если параметр не типа boolean.
   * @covers D\library\patterns\entity\dataType\Boolean::__construct
   */
  public function testShouldThrowExceptionIfArgNotBoolean(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    new Boolean('');
  }
}
 
