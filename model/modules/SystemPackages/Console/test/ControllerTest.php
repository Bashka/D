<?php
namespace D\model\modules\SystemPackages\Console\test;

use D\model\modules\SystemPackages\Console\Controller;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class ControllerTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var \D\model\modules\SystemPackages\Console\Controller
   */
  private $object;

  protected function setUp(){
    $this->object = Controller::getInstance();
  }

  /**
   * Должен возвращать строку, если параметры не переданы.
   * @covers D\model\modules\SystemPackages\Console\Controller::test
   */
  public function testShouldReturnString(){
    $this->assertEquals('The argument is transferred: .', $this->object->test());
  }

  /**
   * Должен возвращать строку с указанием переданных параметров, если они указаны.
   * @covers D\model\modules\SystemPackages\Console\Controller::test
   */
  public function testShouldReturnStringAndParameter(){
    $this->assertEquals('The argument is transferred: 1, 2, 3.', $this->object->test(1,2,3));
  }

  /**
   * Должен возвращать имени зарегистрированных в системе, конкретных модулей.
   * @covers D\model\modules\SystemPackages\Console\Controller::getModulesNames
   */
  public function testShouldReturnAllModulesNames(){
    $this->assertEquals(['Console', 'InstallerModules', 'InstallerScreens'], $this->object->getModulesNames());
  }

  /**
   * Должен возвращать все доступные методы модуля.
   * @covers D\model\modules\SystemPackages\Console\Controller::getModuleActions
   */
  public function testShouldReturnActivesModule(){
    $this->assertEquals(['getMethodArgs', 'getModuleActions', 'getModulesNames', 'test'], $this->object->getModuleActions(new Name('Console')));
  }

  /**
   * Должен возвращать аргументы метода модуля.
   * @covers D\model\modules\SystemPackages\Console\Controller::getMethodArgs
   */
  public function testShouldReturnArgsActiveModule(){
    $this->assertEquals(['module:Name', 'method:Name'], $this->object->getMethodArgs(new Name('Console'), new Name('getMethodArgs')));
  }
}
 