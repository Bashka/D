<?php
namespace D\services\config\test;

use D\services\cache\Cache;
use D\services\config\SystemConf;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class SystemConfTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен возвращать указанное свойство из файла, если кэширование не используется.
   * @covers D\services\config\SystemConf::get
   */
  public function testShouldReturnPropertyOfFileIfCacheNotRun(){
    if(Cache::DRIVER != 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо отключить кэширование.');
    }
    /**
     * @var SystemConf $sc
     */
    $sc = SystemConf::getInstance();
    $this->assertEquals('2.0.0', $sc->get('System', 'version'));
  }

  /**
   * Должен возвращать указанное свойство из файла и заполнять кэш, если кэширование используется, но кэш не заполнен.
   * @covers D\services\config\SystemConf::get
   */
  public function testShouldReturnPropertyOfFileIfCacheRunAndNotFull(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    $c = Cache::getInstance();
    if(!is_null($c->get('SystemConf::System::version'))){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо отчистить (на пример перезагрузить службу кэширования) секцию кэша [SystemConf].');
    }
    /**
     * @var SystemConf $sc
     */
    $sc = SystemConf::getInstance();
    $this->assertEquals('2.0.0', $sc->get('System', 'version'));
    $this->assertEquals('2.0.0', $c->get('SystemConf::System::version'));
  }

  /**
   * Должен возвращать указанное свойство из кэша, если кэширование используется и кэш заполнен.
   * @covers D\services\config\SystemConf::get
   */
  public function testShouldReturnPropertyOfCacheIfCacheRunAndFull(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    $c = Cache::getInstance();
    if(is_null($version = $c->get('SystemConf::System::version'))){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо заполнить секцию кэша [SystemConf] данными.');
    }
    $c->set('SystemConf::System::version', '0');
    /**
     * @var SystemConf $sc
     */
    $sc = SystemConf::getInstance();
    $this->assertEquals('0', $sc->get('System', 'version'));
    $c->set('SystemConf::System::version', $version);
  }
}
