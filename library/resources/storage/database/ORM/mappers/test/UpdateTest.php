<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\patterns\entity\SQL\operators\DML\components\condition\LogicOperation;
use D\library\patterns\entity\SQL\operators\DML\components\Field;
use D\library\patterns\entity\SQL\operators\DML\components\Where;
use D\library\resources\storage\database\ORM\mappers\Update;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class UpdateTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Insert::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $o = ChildMock::getProxy('1');
    $o->f = ChildMock::getProxy('2');
    $ds = Update::metamorphose($o);
    $this->assertEquals('UPDATE `ChildTable` SET ChildTable.df = "4",ChildTable.ef = "5",ChildTable.ff = "$/D/library/resources/storage/database/ORM/mappers/test/ChildMock:2",ChildTable.hf = "" WHERE (`OID` = "1")', $ds[0]->interpretation());
    $this->assertEquals('UPDATE `ParentTable` SET ParentTable.af = "1" WHERE (`OID` = "1")', $ds[1]->interpretation());
  }

  /**
   * Второй аргумент должен использоваться для задания условия отбора.
   * @covers D\library\resources\storage\database\ORM\mappers\Insert::metamorphose
   */
  public function testSecondArgWhereComponent(){
    $o = ChildMock::getProxy('1');
    $o->f = ChildMock::getProxy('2');
    $ds = Update::metamorphose($o, new Where(new LogicOperation(new Field('af'), '>', 0)));
    $this->assertEquals('UPDATE `ChildTable` SET ChildTable.df = "4",ChildTable.ef = "5",ChildTable.ff = "$/D/library/resources/storage/database/ORM/mappers/test/ChildMock:2",ChildTable.hf = "" WHERE (`af` > "0")', $ds[0]->interpretation());
    $this->assertEquals('UPDATE `ParentTable` SET ParentTable.af = "1" WHERE (`af` > "0")', $ds[1]->interpretation());
  }
}
