<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\resources\storage\database\ORM\mappers\Select;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class SelectTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Select::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $o = ParentMock::getProxy('1');
    $this->assertEquals('SELECT ParentTable.af AS a,ParentTable.OID AS OID FROM `ParentTable`  WHERE (ParentTable.OID = "1")', Select::metamorphose($o)->interpretation());
  }

  /**
   * Должен поддерживать иерархию наследования.
   * @covers D\library\resources\storage\database\ORM\mappers\Select::metamorphose
   */
  public function testShouldTakeIntoAccountInheritance(){
    $o = ChildMock::getProxy('1');
    $this->assertEquals('SELECT ChildTable.df AS d,ChildTable.ef AS e,ChildTable.ff AS f,ChildTable.hf AS h,ParentTable.af AS a,ChildTable.OID AS OID FROM `ChildTable` INNER JOIN `ParentTable` ON (ChildTable.OID = ParentTable.OID) WHERE (ChildTable.OID = "1")', Select::metamorphose($o)->interpretation());
  }

  /**
   * Должен восстанавливать ассоциацию на основании объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Select::metamorphoseAssociation
   */
  public function testShouldMetamorphoseAssociation(){
    $this->assertEquals('SELECT ChildTable.df AS d,ChildTable.ef AS e,ChildTable.ff AS f,ChildTable.hf AS h,ParentTable.af AS a,ChildTable.OID AS OID FROM `ChildTable` INNER JOIN `ParentTable` ON (ChildTable.OID = ParentTable.OID)', Select::metamorphoseAssociation(ChildMock::getReflectionClass())->interpretation());
    $this->assertEquals('SELECT ChildTable.df AS d,ChildTable.ef AS e,ChildTable.ff AS f,ChildTable.hf AS h,ParentTable.af AS a,ChildTable.OID AS OID FROM `ChildTable` INNER JOIN `ParentTable` ON (ChildTable.OID = ParentTable.OID) WHERE (ParentTable.af = "1")', Select::metamorphoseAssociation(ChildMock::getReflectionClass(), [['a', '=', '1']])->interpretation());
    $this->assertEquals('SELECT ChildTable.df AS d,ChildTable.ef AS e,ChildTable.ff AS f,ChildTable.hf AS h,ParentTable.af AS a,ChildTable.OID AS OID FROM `ChildTable` INNER JOIN `ParentTable` ON (ChildTable.OID = ParentTable.OID) WHERE ((ParentTable.af = "$/D/library/resources/storage/database/ORM/mappers/test/ParentMock:1") AND (ChildTable.df > "0"))', Select::metamorphoseAssociation(ChildMock::getReflectionClass(), [['a', '=', ParentMock::getProxy('1')], ['d', '>', 0]])->interpretation());
  }

  /**
   * Должен выбрасывать исключение, если условное свойство не существует.
   * @covers D\library\resources\storage\database\ORM\mappers\Select::metamorphoseAssociation
   */
  public function testShouldThrowExceptionIfPropertyNotExists(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException');
    Select::metamorphoseAssociation(ChildMock::getReflectionClass(), [['j', '=', '1']]);
  }

  /**
   * Должен выбрасывать исключение, если используется недопустимый оператор.
   * @covers D\library\resources\storage\database\ORM\mappers\Select::metamorphoseAssociation
   */
  public function testShouldThrowExceptionIfBedOperator(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    Select::metamorphoseAssociation(ChildMock::getReflectionClass(), [['h', '!', '1']]);
  }
}
