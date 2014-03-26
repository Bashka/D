<?php
namespace D\model\test;

use D\library\patterns\entity\dataType\Integer;
use D\model\CentralController;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class CentralControllerTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать контроллер модуля.
   * @covers D\model\CentralController::getController
   */
  public function testShouldReturnController(){
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $this->assertInstanceOf('D\model\classes\ModuleController', $cc->getController('Console'));
  }

  /**
   * Должен выбрасывать исключение если нет контроллера модуля.
   * @covers D\model\CentralController::getController
   */
  public function testShouldThrowExceptionIfControllerNotFound(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $cc->getController('TestModule');
  }

  /**
   * Должен вызывать указанный метод модуля передавая ему параметры.
   * @covers D\model\CentralController::run
   */
  public function testShouldRunActiveModule(){
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $this->assertEquals('The argument is transferred: 1, 2, 3.', $cc->run('Console', 'test', [1, 2, 3]));
  }

  /**
   * Должен выполнять верификацию параметров и возвращать подготовленный массив.
   * @covers D\model\CentralController::verifyParams
   */
  public function testShouldVerifyParams(){
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $this->assertEquals([new Integer(1)], $cc->verifyParams('Console', 'strongArgs', [1]));
  }

  /**
   * Должен обрабатывать динамические аргументы.
   * @covers D\model\CentralController::verifyParams
   */
  public function testShouldVerifyDynamicParams(){
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $this->assertEquals([1,2,3], $cc->verifyParams('Console', 'dynamicArgs', [1,2,3]));
  }

  /**
   * Должен выбрасывать исключение, если не переданы все обязательные параметры.
   * @covers D\model\CentralController::verifyParams
   */
  public function testShouldThrowExceptionIfParamNotFound(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $cc->verifyParams('Console', 'strongArgs', []);
  }

  /**
   * Должен обрабатывать необязательные аргументы.
   * @covers D\model\CentralController::verifyParams
   */
  public function testShouldVerifyOptionalParams(){
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $this->assertEquals([], $cc->verifyParams('Console', 'optionalArgs', []));
  }

  /**
   * Должен исключать пустые параметры.
   * @covers D\model\CentralController::verifyParams
   */
  public function testShouldUnsetEmptyParam(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    /**
     * @var CentralController $cc
     */
    $cc = CentralController::getInstance();
    $cc->verifyParams('Console', 'strongArgs', ['']);
  }
}
 
