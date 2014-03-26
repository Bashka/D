<?php
namespace D\model\modules\SystemPackages\test;

use D\model\modules\SystemPackages\ReflectionModule;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class ReflectionUtilityTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен получать ссылку на файл состояния компонента.
   * @covers D\model\modules\SystemPackages\ReflectionUtility::__construct
   */
  public function testShouldSetStateFile(){
    $rm = new ReflectionModule('SystemPackages');
    $this->assertEquals('SystemPackages', $rm->getStateFile()->get('Component', 'name'));
  }

  /**
   * В случае отсутствия каталога компонента должен выбрасывать исключение.
   * @covers D\model\modules\SystemPackages\ReflectionUtility::__construct
   */
  public function testShouldThrowExceptionIfDirComponentNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    new ReflectionModule('NotExistsModule');
  }

  /**
   * Должен возвращать имя компонента.
   * @covers D\model\modules\SystemPackages\ReflectionUtility::getName
   */
  public function testShouldReturnName(){
    $rm = new ReflectionModule('SystemPackages');
    $this->assertEquals('SystemPackages', $rm->getName());
  }

  /**
   * Должен возвращать файл состояния компонента.
   * @covers D\model\modules\SystemPackages\ReflectionUtility::getStateFile
   */
  public function testShouldReturnState(){
    $rm = new ReflectionModule('SystemPackages');
    $state = $rm->getStateFile();
    $this->assertInstanceOf('D\library\resources\fileSystem\components\special\ini\INI', $state);
    $this->assertEquals('SystemPackages', $state->get('Component', 'name'));
  }
}
