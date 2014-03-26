<?php
namespace D\model\modules\SystemPackages\ModulesManager\test;

use D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress;
use D\library\patterns\entity\dataType\special\system\Alias;
use D\library\patterns\entity\dataType\special\system\GUID4;
use D\library\patterns\entity\dataType\special\system\Name;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\resources\fileSystem\components\Directory;
use D\library\resources\fileSystem\components\special\ini\INI;
use D\library\resources\storage\database\ORM\UncertaintyException;
use D\model\modules\System\Users\Access\LinkageRoleRule;
use D\model\modules\System\Users\Access\Role;
use D\model\modules\System\Users\Access\Rule;
use D\model\modules\SystemPackages\ModulesManager\Controller;
use D\model\modules\SystemPackages\ReflectionModule;
use D\services\database\EntityManagerFabric;
use D\services\module\Router;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class ControllerTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var Controller
   */
  private static $object;

  public static function setUpBeforeClass(){
    self::$object = Controller::getInstance();
  }

  /**
   * Должен возвращать пустой массив, если все зависимости удовлетворены.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::platformDepControl
   */
  public function testShouldReturnEmptyArrayIfDepSuccess(){
    $this->assertEquals([], self::$object->platformDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_platformOk.zip')));
  }

  /**
   * Должен возвращать информацию о неудовлетворенной зависимости платформы.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::platformDepControl
   */
  public function testShouldReturnEmptyArrayIfDepFailure(){
    $this->assertEquals(['assert' => ['0.0.0', '0.0.1'], 'actual' => '2.0.0'], self::$object->platformDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_platformBed.zip')));
  }

  /**
   * Должен возвращать пустой массив, если все зависимости удовлетворены.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::parentDepControl
   */
  public function testShouldReturnEmptyArrayIfDepSuccess2(){
    $this->assertEquals([], self::$object->parentDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_parentOk.zip')));
  }

  /**
   * Должен возвращать информацию о неудовлетворенной зависимости родительского модуля.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::parentDepControl
   */
  public function testShouldReturnEmptyArrayIfDepFailure2(){
    $this->assertEquals(['assert' => ['0.0', '0.1'], 'actual' => '4.0'], self::$object->parentDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_parentBed.zip')));
  }

  /**
   * Должен возвращать пустую строку в качестве актуальной версии модуля, если модуль не установлен.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::parentDepControl
   */
  public function testShouldEmptyStringIfModuleNotFound(){
    $this->assertEquals(['assert' => ['0.0', '0.1'], 'actual' => ''], self::$object->parentDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_parentNotExists.zip')));
  }

  /**
   * Должен возвращать пустой массив, если все зависимости удовлетворены.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::usedDepControl
   */
  public function testShouldReturnEmptyArrayIfDepSuccess3(){
    $this->assertEquals([], self::$object->usedDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_usedOk.zip')));
  }

  /**
   * Должен возвращать информацию о неудовлетворенной зависимости используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::usedDepControl
   */
  public function testShouldReturnEmptyArrayIfDepFailure3(){
    $this->assertEquals(['Console' => ['assert' => ['0.0', '0.1'], 'actual' => '2.0']], self::$object->usedDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_usedBed.zip')));
  }

  /**
   * Должен возвращать пустую строку в качестве актуальной версии модуля, если модуль не установлен.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::usedDepControl
   */
  public function testShouldEmptyStringIfModuleNotFound2(){
    $this->assertEquals(['ModulePackageMock' => ['assert' => ['0.0', '10.0'], 'actual' => '']], self::$object->usedDepControl(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_usedNotExists.zip')));
  }

  /**
   * Должен регистрировать модуль в роутере.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::register
   */
  public function testShouldRegisterModule(){
    self::$object->register(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_platformOk.zip'));
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $this->assertTrue($router->hasModule('ModulePackageMock'));
    $this->assertEquals('SystemPackages/ModulePackageMock', $router->get('ModulePackageMock'));
    $router->remove('ModulePackageMock');
  }

  /**
   * Должен выбрасывать исключение, если модуль уже зарегистрирован.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::register
   */
  public function testShouldThrowExceptionIfModuleRegister(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    self::$object->register(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/Console_2.0.zip'));
  }

  /**
   * Должен исключать модуль из роутера.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::unregister
   */
  public function testShouldUnregisterModule(){
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $router->add('ModulePackageMock', 'SystemPackages');
    self::$object->unregister(new Name('ModulePackageMock'));
    $this->assertFalse($router->hasModule('ModulePackageMock'));
  }

  /**
   * Должен выбрасывать исключение если модуль еще не зарегистрирован.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::unregister
   */
  public function testShouldThrowExceptionIfModuleNotRegister(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    self::$object->unregister(new Name('ModulePackageMock'));
  }

  /**
   * Должен добавлять модуль в хранилище модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::expand
   */
  public function testShouldExpandModule(){
    $location = new Directory($_SERVER['DOCUMENT_ROOT'].'/'.Router::MODULES_DIR.'/SystemPackages/ModulePackageMock');
    self::$object->expand(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_parentOk.zip'));
    $this->assertTrue($location->isExists());
    $this->assertTrue($location->getFile('Controller.php')->isExists());
    $this->assertTrue($location->getDir('testDir')->isExists());
    $this->assertTrue($location->getFile('state.ini')->isExists());
    $stateFile = new INI($location->getFile('state.ini')->getAddress());
    $this->assertEquals('', $stateFile->get('Depending', 'destitute'));
    $this->assertEquals('', $stateFile->get('Depending', 'children'));
    $location->delete();
  }

  /**
   * Должен выбрасывать исключение, если модуль уже имеется в хранилище модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::expand
   */
  public function testShouldThrowExceptionIfModuleExpand(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    self::$object->expand(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/Console_2.0.zip'));
  }

  /**
   * Должен удалять модуль из хранилища модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::delete
   */
  public function testShouldDeleteModule(){
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $router->add('ModulePackageMock', 'SystemPackages');
    $location = new Directory($_SERVER['DOCUMENT_ROOT'].'/'.Router::MODULES_DIR.'/SystemPackages/ModulePackageMock');
    self::$object->expand(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_parentOk.zip'));
    $this->assertTrue($location->isExists());
    self::$object->delete(new Name('ModulePackageMock'));
    $this->assertFalse($location->isExists());
    $router->remove('ModulePackageMock');
  }

  /**
   * Должен выбрасывать исключение, если модуль отсутствует в хранилище модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::delete
   */
  public function testShouldThrowExceptionIfModuleNotExists(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    self::$object->delete(new Name('ModulePackageMock'));
  }

  /**
   * Должен добавлять информацию о модуле в файл состояния родителя.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::addDepParent
   */
  public function testShouldAddModuleInParent(){
    self::$object->addDepParent(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_parentOk.zip'));
    $parent = new ReflectionModule('SystemPackages');
    $this->assertTrue(array_search('ModulePackageMock', $parent->getChild()) !== false);
    $parent->removeChild('ModulePackageMock');
  }

  /**
   * Должен удалять информацию о модуле из файла состояния родителя.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::removeDepParent
   */
  public function testShouldRemoveModuleInParent(){
    self::$object->removeDepParent(new Name('ModulesManager'));
    $parent = new ReflectionModule('SystemPackages');
    $this->assertTrue(array_search('ModulesManager', $parent->getChild()) === false);
    $parent->addChild('ModulesManager');
  }

  /**
   * Должен добавлять информацию о модуле в файлы состояний используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::addDepUsed
   */
  public function testShouldAddModuleInUsed(){
    self::$object->addDepUsed(new FileSystemAddress('/D/model/modules/SystemPackages/ModulesManager/test/ModulePackageMock_1.0_usedOk.zip'));
    $used = new ReflectionModule('Console');
    $this->assertTrue(array_search('ModulePackageMock', $used->getDestitute()) !== false);
    $used->removeDestitute('ModulePackageMock');
  }

  /**
   * Должен удалять информацию о модуле из файлов состояний используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::removeDepUsed
   */
  public function testShouldRemoveModuleInUsed(){
    self::$object->removeDepUsed(new Name('ModuleMock'));
    $used = new ReflectionModule('ModuleMock');
    $this->assertTrue(array_search('ModuleMock', $used->getDestitute()) === false);
    $used->addDestitute('ModuleMock');
  }

  /**
   * Должен выполнять внутреннюю инсталляцию модуля.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::runInstaller
   */
  public function testShouldRunInstallerModule(){
    $this->assertEquals('install', self::$object->runInstaller(new Name('ModuleMock')));
  }

  /**
   * Должен выполнять внутреннюю деинсталляцию модуля.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::runUninstaller
   */
  public function testShouldRunUninstallerModule(){
    $this->assertEquals('uninstall', self::$object->runUninstaller(new Name('ModuleMock')));
  }

  /**
   * Должен добавлять права доступа на основании файла состояния модуля.
   * В случае отсутствия модуля Access, тест не выполняется.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::addAccessRules
   */
  public function testShouldAddAccessRules(){
    try{
      $access = new ReflectionModule('Access');
    }
    catch(NotExistsException $e){
      return;
    }
    $access = $access->getController();
      /**
       * @var \D\model\modules\System\Users\Access\Controller $access
       */
    $em = EntityManagerFabric::getInstance();
    self::$object->addAccessRules(new Name('ModuleMock'));
    $rule = $access->getOIDRule(new Name('ModuleMock'), new Name('test'));
    /**
     * @var Rule $rule
     */
    $rule = Rule::getProxy($rule);
    /**
     * @var LinkageRoleRule[] $rules
     */
    $rules = $em->recoverGroupFinding(LinkageRoleRule::getReflectionClass(), [['rule', '=', $rule]]);
    $countRules = 0;
    /**
     * @var LinkageRoleRule $link
     */
    foreach($rules as $link){
      if($rule->getOID() == $link->getRule()->getOID()){
        $countRules++;
        $em->delete($link);
      }
    }
    $access->removeRule(new GUID4($rule->getOID()));
    if($countRules != 2){
      throw new \Exception();
    }
  }

  /**
   * Должен удалять права доступа на основании файла состояния модуля.
   * В случае отсутствия модуля Access, тест не выполняется.
   * @covers D\model\modules\SystemPackages\ModulesManager\Controller::removeAccessRules
   */
  public function testShouldRemoveAccessRules(){
    try{
      $access = new ReflectionModule('Access');
    }
    catch(NotExistsException $e){
      return;
    }
    $access = $access->getController();
    /**
     * @var \D\model\modules\System\Users\Access\Controller $access
     */
    self::$object->addAccessRules(new Name('ModuleMock'));
    self::$object->removeAccessRules(new Name('ModuleMock'));
    try{
      $access->getOIDRule(new Name('ModuleMock'), new Name('test'));
    }
    catch(UncertaintyException $e){
      return;
    }
    throw new \Exception;
  }
}
 
