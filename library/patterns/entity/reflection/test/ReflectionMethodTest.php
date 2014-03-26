<?php
namespace D\library\patterns\entity\reflection\test;

use D\library\patterns\entity\reflection\ReflectionMethod;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class ReflectionMethodTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать отражение указанного параметра.
   * @covers D\library\patterns\entity\reflection\ReflectionMethod::getParameter
   */
  public function testShouldReturnParameter(){
    $rm = new ReflectionMethod('D\library\patterns\entity\reflection\test\DescribedMock', 'method');
    $ra = $rm->getParameter('arg');
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionParameter', $ra);
    $this->assertEquals('arg', $ra->getName());
  }

  /**
   * Должен выбрасывать исключение, если параметр отсутствует
   * @covers D\library\patterns\entity\reflection\ReflectionMethod::getParameter
   */
  public function testShouldThrowExceptionIfParameterNotFound(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\LackException');
    $rm = new ReflectionMethod('D\library\patterns\entity\reflection\test\DescribedMock', 'method');
    $rm->getParameter('not');
  }
}
 
