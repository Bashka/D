<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\resources\storage\database\ORM\mappers\Field;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class FieldTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Field::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $this->assertEquals('ParentTable.af', Field::metamorphose(ParentMock::getReflectionClass(), 'a')->interpretation());
    $this->assertEquals('ParentTable.OID', Field::metamorphose(ParentMock::getReflectionClass(), 'OID')->interpretation());
    $this->assertEquals('ChildTable.df', Field::metamorphose(ChildMock::getReflectionClass(), 'd')->interpretation());
  }

  /**
   * Должен выбрасывать исключение в случае отсутстия аннотирования целевого свойства.
   * @covers D\library\resources\storage\database\ORM\mappers\Field::metamorphose
   */
  public function testShouldThrowExceptionIfMetadataNotFound(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    Field::metamorphose(ParentMock::getReflectionClass(), 'c');
  }

  /**
   * Должен выбрасывать исключение в случае отсутстия целевого свойства.
   * @covers D\library\resources\storage\database\ORM\mappers\Field::metamorphose
   */
  public function testShouldThrowExceptionIfPropertyNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    Field::metamorphose(ParentMock::getReflectionClass(), 'd');
  }
}
