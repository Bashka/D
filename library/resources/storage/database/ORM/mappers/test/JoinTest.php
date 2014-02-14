<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\resources\storage\database\ORM\mappers\Join;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class JoinTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Join::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $this->assertEquals('INNER JOIN `ParentTable` ON (ChildTable.OID = ParentTable.OID)', Join::metamorphose(ChildMock::getReflectionClass(), ParentMock::getReflectionClass())->interpretation());
  }

  /**
   * Должен возвращать представление Primary Key поля.
   * @covers D\library\resources\storage\database\ORM\mappers\Join::getPKField
   */
  public function testShouldReturnPKField(){
    $this->assertEquals('`OID`', Join::getPKField(ParentMock::getReflectionClass())->interpretation());
  }
}
