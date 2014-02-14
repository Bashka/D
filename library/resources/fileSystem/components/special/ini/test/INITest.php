<?php
namespace D\library\resources\fileSystem\components\special\ini\test;

use D\library\resources\fileSystem\components\special\ini\INI;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class INITest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен считывать информацию из файла.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::__construct
   */
  public function testShouldReadINI(){
    $ini = new INI('ini');
    $this->assertEquals('valueA', $ini->get('sectionA', 'keyA'));
  }

  /**
   * Должен выбрасывать исключение, если файла не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::__construct
   */
  public function testShouldThrowExceptionIfFileNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    new INI('notExistsIni');
  }

  /**
   * Должен возвращать значение свойства.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::get
   */
  public function testShouldReturnValue(){
    $ini = new INI('ini');
    $this->assertEquals('valueA', $ini->get('sectionB', 'keyA'));
  }

  /**
   * Должен выбрасывать исключение если секции не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::get
   */
  public function testShouldThrowExceptionIfSectionNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $ini = new INI('ini');
    $ini->get('sectionC', 'valueA');
  }

  /**
   * Должен выбрасывать исключение если свойства не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::get
   */
  public function testShouldThrowExceptionIfDataNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $ini = new INI('ini');
    $ini->get('sectionA', 'valueC');
  }

  /**
   * Должен возвращать секцию.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::getSection
   */
  public function testShouldReturnSection(){
    $ini = new INI('ini');
    $this->assertEquals(['keyA' => 'valueA', 'keyB' => 'valueB'], $ini->getSection('sectionA'));
  }

  /**
   * Должен выбрасывать исключение, если секции не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::getSection
   */
  public function testShouldThrowExceptionIfSectionNotExists2(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    $ini = new INI('ini');
    $ini->getSection('sectionC');
  }

  /**
   * Должен возвращать все данные из файла.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::getAll
   */
  public function testShouldReturnAllData(){
    $ini = new INI('ini');
    $this->assertEquals(['sectionA' => ['keyA' => 'valueA', 'keyB' => 'valueB'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB']], $ini->getAll());
  }

  /**
   * Должен устанавливать значение свойству, но не перезаписывать файл.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::set
   */
  public function testShouldSetValue(){
    $ini = new INI('ini');
    $ini->set('sectionA', 'keyA', 'newValue');
    $this->assertEquals('newValue', $ini->get('sectionA', 'keyA'));
    $this->assertEquals(['sectionA' => ['keyA' => 'valueA', 'keyB' => 'valueB'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB']], parse_ini_file('ini', true));
  }

  /**
   * Должен добавлять свойство, если оно не существовало.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::set
   */
  public function testShouldAddProperty(){
    $ini = new INI('ini');
    $ini->set('sectionA', 'keyC', 'newValue');
    $this->assertEquals(['sectionA' => ['keyA' => 'valueA', 'keyB' => 'valueB', 'keyC' => 'newValue'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB']], $ini->getAll());
  }

  /**
   * Должен добавлять секцию, если она не существовала.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::set
   */
  public function testShouldAddSection(){
    $ini = new INI('ini');
    $ini->set('sectionC', 'keyA', 'valueA');
    $this->assertEquals(['sectionA' => ['keyA' => 'valueA', 'keyB' => 'valueB'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB'], 'sectionC' => ['keyA' => 'valueA']], $ini->getAll());
  }

  /**
   * Должен удалять указанное свойство, но не перезаписывать файл.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::remove
   */
  public function testShouldRemoveData(){
    $ini = new INI('ini');
    $ini->remove('sectionA', 'valueA');
    $this->assertFalse($ini->hasKey('sectionA', 'valueA'));
    $this->assertEquals(['sectionA' => ['keyA' => 'valueA', 'keyB' => 'valueB'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB']], parse_ini_file('ini', true));
  }

  /**
   * Ничего не должен делать, если свойства или секции не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::remove
   */
  public function testShouldSilentIfDataOrSectionNotExists(){
    $ini = new INI('ini');
    $ini->remove('sectionA', 'valueC');
    $ini->remove('sectionC', 'valueA');
  }

  /**
   * Должен удалять указанную секцию, но не перезаписывать файл.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::removeSection
   */
  public function testShouldRemoveSection(){
    $ini = new INI('ini');
    $ini->removeSection('sectionA');
    $this->assertFalse($ini->hasSection('sectionC'));
    $this->assertEquals(['sectionA' => ['keyA' => 'valueA', 'keyB' => 'valueB'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB']], parse_ini_file('ini', true));
  }

  /**
   * Ничего не должен делать, если секции не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::removeSection
   */
  public function testShouldSilentIfSectionNotExists(){
    $ini = new INI('ini');
    $ini->removeSection('sectionC');
  }

  /**
   * Должен возвращать true - если свойство существует, иначе - false.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::hasKey
   */
  public function testShouldReturnTrueIfDataExists(){
    $ini = new INI('ini');
    $this->assertTrue($ini->hasKey('sectionA', 'keyA'));
    $this->assertFalse($ini->hasKey('sectionA', 'keyC'));
  }

  /**
   * Должен возвращать false - если секции не существует.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::hasKey
   */
  public function testShouldReturnFalseIfSectionNotExists(){
    $ini = new INI('ini');
    $this->assertFalse($ini->hasKey('sectionC', 'keyA'));
  }

  /**
   * Должен возвращать true - если секция существует, иначе - false.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::hasSection
   */
  public function testShouldReturnTrueIfSectionExists(){
    $ini = new INI('ini');
    $this->assertTrue($ini->hasSection('sectionA'));
    $this->assertFalse($ini->hasSection('sectionC'));
  }

  /**
   * Должен перезаписывать файл.
   * @covers D\library\resources\fileSystem\components\special\ini\INI::rewrite
   */
  public function testShouldRewriteFile(){
    $ini = new INI('ini');
    $ini->set('sectionA', 'keyA', 'newValue');
    $ini->rewrite();
    $this->assertEquals(['sectionA' => ['keyA' => 'newValue', 'keyB' => 'valueB'], 'sectionB' => ['keyA' => 'valueA', 'keyB' => 'valueB']], parse_ini_file('ini', true));
    $ini->set('sectionA', 'keyA', 'valueA');
    $ini->rewrite();
  }
}
 