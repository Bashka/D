<?php
namespace D\model\modules\SystemPackages\test;

use D\model\modules\SystemPackages\ModulePackage;
use D\model\modules\SystemPackages\ReflectionModule;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class ModulePackageTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать версию модуля.
   * @covers D\model\modules\SystemPackages\ModulePackage::getVersion
   */
  public function testShouldReturnVersion(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals('1.0', $p->getVersion());
  }

  /**
   * Должен возвращать тип модуля.
   * @covers D\model\modules\SystemPackages\ModulePackage::getType
   */
  public function testShouldReturnType(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals(ReflectionModule::SPECIFIC, $p->getType());
  }

  /**
   * Должен возвращать диапазон допустимых версий платформы.
   * @covers D\model\modules\SystemPackages\ModulePackage::getAllowablePlatformVersion
   */
  public function testShouldReturnAllowablePlatformVersion(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals(['0.0', '10.0'], $p->getAllowablePlatformVersion());
  }

  /**
   * Должен возвращать имя родительского модуля.
   * @covers D\model\modules\SystemPackages\ModulePackage::getParent
   */
  public function testShouldReturnParentName(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals('SystemPackages', $p->getParent());
  }

  /**
   * Должен возвращать пустую строку, если модуль не имеет родителя.
   * @covers D\model\modules\SystemPackages\ModulePackage::getParent
   */
  public function testShouldReturnEmptyStringIfParentNotExists(){
    $p = new ModulePackage('ModulePackageMock_1.0_NoParent.zip');
    $this->assertEquals('', $p->getParent());
  }

  /**
   * Должен возвращать диапазон допустимых версий родительского модуля.
   * @covers D\model\modules\SystemPackages\ModulePackage::getAllowableVersionParentModule
   */
  public function testShouldReturnAllowableVersionParentModule(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals(['0.0', '10.0'], $p->getAllowableVersionParentModule());
  }

  /**
   * Должен возвращать диапазон допустимых версий родительского модуля.
   * @covers D\model\modules\SystemPackages\ModulePackage::getAllowableVersionParentModule
   */
  public function testShouldReturnEmptyArrayIfParentNotExists(){
    $p = new ModulePackage('ModulePackageMock_1.0_NoParent.zip');
    $this->assertEquals([], $p->getAllowableVersionParentModule());
  }

  /**
   * Должен возвращать имена используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulePackage::getUsed
   */
  public function testShouldReturnUsedModulesNames(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals(['Console', 'InstallerModules'], $p->getUsed());
  }

  /**
   * Должен возвращать пустой массив в случае отсутствия используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulePackage::getUsed
   */
  public function testShouldReturnEmptyArrayIfUsedNotExists(){
    $p = new ModulePackage('ModulePackageMock_1.0_NoUsed.zip');
    $this->assertEquals([], $p->getUsed());
  }

  /**
   * Должен возвращать диапазоны допустимых версий используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulePackage::getAllowableVersionUsedModules
   */
  public function testShouldReturnAllowableVersionUsedModules(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals(['Console' => ['0.0', '10.0'], 'InstallerModules' => ['0.0', '10.0']], $p->getAllowableVersionUsedModules());
  }

  /**
   * Должен возвращать пустой массив в случае отсутствия используемых модулей.
   * @covers D\model\modules\SystemPackages\ModulePackage::getAllowableVersionUsedModules
   */
  public function testShouldReturnEmptyArrayIfUsedNotExists2(){
    $p = new ModulePackage('ModulePackageMock_1.0_NoUsed.zip');
    $this->assertEquals([], $p->getAllowableVersionUsedModules());
  }
}
 