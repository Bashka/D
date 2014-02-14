<?php
namespace D\library\resources\storage\database\ORM\mappers\test;

use D\library\resources\storage\database\ORM\mappers\Insert;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class InsertTest extends \PHPUnit_Framework_TestCase{
  /**
   * Должен восстанавливать объект из другого объекта.
   * @covers D\library\resources\storage\database\ORM\mappers\Delete::metamorphose
   */
  public function testShouldMetamorphoseObject(){
    $o = new ChildMock;
    $o->f = ChildMock::getProxy('1');
    $ds = Insert::metamorphose($o, '2');
    $this->assertEquals('INSERT INTO `ChildTable` (`OID`,ChildTable.df,ChildTable.ef,ChildTable.ff,ChildTable.hf) VALUES ("2","4","5","$/D/library/resources/storage/database/ORM/mappers/test/ChildMock:1","")', $ds[0]->interpretation());
    $this->assertEquals('INSERT INTO `ParentTable` (`OID`,ParentTable.af) VALUES ("2","1")', $ds[1]->interpretation());
  }
}
