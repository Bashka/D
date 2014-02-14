<?php
namespace D\library\resources\fileSystem\components\special\csv\test;

use D\library\resources\fileSystem\components\special\csv\DSV;
use D\library\resources\fileSystem\components\special\csv\Index;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class IndexTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен формировать индекс.
   * @covers D\library\resources\fileSystem\components\special\csv\Index::update
   */
  public function testShouldCreateIndex(){
    $object = new DSV('data.csv');
    $index = new Index(0);
    $object->attach($index);
    $object->load();
    $this->assertEquals([0, 2], $index->getIndex('Иванов'));
  }

  /**
   * Должен формировать мультииндекс.
   * @covers D\library\resources\fileSystem\components\special\csv\Index::update
   */
  public function testShouldCreateMultiIndex(){
    $object = new DSV('data.csv');
    $index = new Index(0, 2);
    $object->attach($index);
    $object->load();
    $this->assertEquals([0], $index->getIndex('Иванов', 'Алексеевич'));
  }
}
 