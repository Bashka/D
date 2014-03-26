<?php
namespace D\model\modules\SystemPackages\test;

use D\model\modules\SystemPackages\ModulePackage;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class PackageTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен получать файл конфигурации пакета.
   * @covers D\model\modules\SystemPackages\Package::__construct
   */
  public function testShouldGetConfPackage(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertInstanceOf('\ZipArchive', $p->getArchive());
    $this->assertEquals('1.0', $p->getConf()['Component']['version']);
  }

  /**
   * Должен выбрасывать исключение если пакет или его файл конфигурации не найдены.
   * @covers D\model\modules\SystemPackages\Package::__construct
   */
  public function testShouldThrowExceptionIfPackageOrConfNotFound(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    new ModulePackage('NotExistsPackage.zip');
  }

  /**
   * Должен возвращать имя компонента.
   * @covers D\model\modules\SystemPackages\Package::getName
   */
  public function testShouldReturnName(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals('ModulePackageMock', $p->getName());
  }

  /**
   * Должен возвращать конфигурацию пакета.
   * @covers D\model\modules\SystemPackages\Package::getConf
   */
  public function testShouldReturnConf(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertEquals(['Component' => ['name' => 'ModulePackageMock', 'version' => '1.0', 'type' => 'specific', 'platformVersion' => '0.0-10.0'], 'Depending' => ['parent' => 'SystemPackages:0.0-10.0', 'used' => 'Console:0.0-10.0,InstallerModules:0.0-10.0'], 'Access' => ['test' => 'Default user role,User role,Moderator role']], $p->getConf());
  }

  /**
   * Должен возвращать архив пакета.
   * @covers D\model\modules\SystemPackages\Package::getArchive
   */
  public function testShouldReturnArchive(){
    $p = new ModulePackage('ModulePackageMock_1.0.zip');
    $this->assertInstanceOf('\ZipArchive', $p->getArchive());
  }
}
 
