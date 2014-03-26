<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\resources\storage\database\ORM\mappers\Delete;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class DeleteTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Delete::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $ds = Delete::metamorphose(ChildMock::getProxy('1'));
    $this->assertEquals('DELETE FROM `ChildTable` WHERE (`OID` = "1")', $ds[0]->interpretation());
    $this->assertEquals('DELETE FROM `ParentTable` WHERE (`OID` = "1")', $ds[1]->interpretation());
  }

  /**
   * Второй аргумент должен использоваться для задания условия отбора.
   * @covers D\library\resources\storage\database\ORM\mappers\Delete::metamorphose
   */
  public function testSecondArgWhereComponent(){
    $ds = Delete::metamorphose(ChildMock::getProxy('1'), new Where(new LogicOperation(new Field('af'), '>', 0)));
    $this->assertEquals('DELETE FROM `ChildTable` WHERE (`af` > "0")', $ds[0]->interpretation());
    $this->assertEquals('DELETE FROM `ParentTable` WHERE (`af` > "0")', $ds[1]->interpretation());
  }
}
