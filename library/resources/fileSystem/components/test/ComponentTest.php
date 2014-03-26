<?php
namespace D\library\resources\fileSystem\components\test;

use D\library\resources\fileSystem\components\Directory;
use D\library\resources\fileSystem\components\File;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class ComponentTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен правильно обрабатывать относительные адреса не существующих компонентов.
   * @covers D\library\resources\fileSystem\components\Component::__construct
   */
  public function testShouldSetCurrentDir(){
    $f = new File('dir/notExistsFile');
    $this->assertEquals(__DIR__.'/dir', $f->getLocation());
  }

  /**
   * Должен возвращать абсолютный адрес компонента.
   * @covers D\library\resources\fileSystem\components\Component::getAddress
   */
  public function testShouldReturnAddress(){
    $f = new File('file');
    $this->assertEquals(__DIR__.'/file', $f->getAddress());

    $f = new File(__DIR__.'/file');
    $this->assertEquals(__DIR__.'/file', $f->getAddress());
  }

  /**
   * Должен возвращать расположение компонента.
   * @covers D\library\resources\fileSystem\components\Component::getLocation
   */
  public function testShouldReturnLocation(){
    $f = new File('file');
    $this->assertEquals(__DIR__, $f->getLocation());

    $f = new File(__DIR__.'/file');
    $this->assertEquals(__DIR__, $f->getLocation());
  }

  /**
   * Должен возвращать имя компонента.
   * @covers D\library\resources\fileSystem\components\Component::getName
   */
  public function testShouldReturnName(){
    $f = new File('file');
    $this->assertEquals('file', $f->getName());

    $f = new File(__DIR__.'/file');
    $this->assertEquals('file', $f->getName());
  }

  /**
   * Должен возвращать родительский каталог.
   * @covers D\library\resources\fileSystem\components\Component::getLocationDirectory
   */
  public function testShouldReturnLocationDirectory(){
    $f = new File('file');
    $ld = $f->getLocationDirectory();
    $this->assertInstanceOf('D\library\resources\fileSystem\components\Directory', $ld);
    $this->assertEquals('test', $ld->getName());
    $this->assertEquals(__DIR__, $ld->getAddress());
  }

  /**
   * Должен возвращать самого себя если вызывается корневой каталог.
   * @covers D\library\resources\fileSystem\components\Component::getLocationDirectory
   */
  public function testShouldReturnSelfIfRootDir(){
    $f = new Directory('/');
    $ld = $f->getLocationDirectory();;
    $this->assertInstanceOf('D\library\resources\fileSystem\components\Directory', $ld);
    $this->assertEquals('', $ld->getName());
    $this->assertEquals('/', $ld->getLocation());
    $this->assertEquals('/', $ld->getAddress());
  }

  /**
   * Должен выполнять переименование компонента.
   * @covers D\library\resources\fileSystem\components\Component::rename
   */
  public function testShouldRenameComponent(){
    $f = new File('file');
    $f->rename('renameFile');
    $this->assertTrue(file_exists('renameFile'));
    $this->assertEquals('renameFile', $f->getName());
    $f->rename('file');
  }

  /**
   * Должен выбрасывать исключение, если в имени компонента присутсвует символ обратного слеша.
   * @covers D\library\resources\fileSystem\components\Component::rename
   */
  public function testShouldThrowExceptionIfSlashName(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    $f = new File('file');
    $f->rename('/renameFile');
  }

  /**
   * Должен выбрасывать исключение, если компонента нет в файловой системы.
   * @covers D\library\resources\fileSystem\components\Component::rename
   */
  public function testShouldThrowExceptionIfComponentNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $f = new File('notExistsFile');
    $f->rename('renameFile');
  }

  /**
   * Должен перемещать компонент в указанный каталог.
   * @covers D\library\resources\fileSystem\components\Component::move
   */
  public function testShouldMoveComponent(){
    $f = new File('file');
    $f->move('dir');
    $this->assertTrue(file_exists('dir/file'));
    $this->assertEquals(__DIR__.'/dir', $f->getLocation());
    $f->move(__DIR__);
  }

  /**
   * Должен перемещать компонент по относительному адресу.
   * @covers D\library\resources\fileSystem\components\Component::move
   */
  public function testShouldMoveComponent2(){
    $f = new File('file');
    $f->move('./dir');
    $this->assertTrue(file_exists('dir/file'));
    $this->assertEquals(__DIR__.'/dir', $f->getLocation());
    $f->move('.');
    $this->assertEquals(__DIR__, $f->getLocation());
  }

  /**
   * Должен выбрасывать исключение, если компонента нет в файловой системы.
   * @covers D\library\resources\fileSystem\components\Component::move
   */
  public function testShouldThrowExceptionIfComponentNotExists2(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $f = new File('notExistsFile');
    $f->move('dir');
  }
}
 
