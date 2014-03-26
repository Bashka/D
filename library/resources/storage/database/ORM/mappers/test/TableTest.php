<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\resources\storage\database\ORM\mappers\Table;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class TableTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Table::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $this->assertEquals('ParentTable', Table::metamorphose(ParentMock::getReflectionClass())->interpretation());
    $this->assertEquals('ChildTable', Table::metamorphose(ChildMock::getReflectionClass())->interpretation());
  }
}
