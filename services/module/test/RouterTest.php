<?php
namespace D\services\module\test;

use D\services\cache\Cache;
use D\services\module\Router;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class RouterTest extends \PHPUnit_Framework_TestCase {
  /**
   * При использовании не инициализированного кэша, должен его заполнять.
   * @covers D\services\module\Router::__construct
   */
  public function testShouldFullCache(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    $c = Cache::getInstance();
    if(!is_null($version = $c->get('ModuleRouter::init'))){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо отчистить (на пример перезагрузить службу кэширования) секцию кэша [ModuleRouter::init].');
    }
    Router::getInstance();
    $this->assertEquals('1', $c->get('ModuleRouter::init'));
    $this->assertEquals('SystemPackages', $c->get('ModuleRouter::SystemPackages'));
  }

  /**
   * Должен возвращать true если модуль зарегистрирован при использовании кэша.
   * @covers D\services\module\Router::hasModule
   */
  public function testShouldReturnTrueIfModuleRegisterAndCacheRun(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $this->assertTrue($r->hasModule('SystemPackages'));
    $this->assertFalse($r->hasModule('TestModule'));
  }

  /**
   * Должен возвращать true если модуль зарегистрирован при не использовании кэша.
   * @covers D\services\module\Router::hasModule
   */
  public function testShouldReturnTrueIfModuleRegisterAndCacheNotRun(){
    if(Cache::DRIVER != 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо отключить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $this->assertTrue($r->hasModule('SystemPackages'));
    $this->assertFalse($r->hasModule('TestModule'));
  }

  /**
   * Должен возвращать адрес модуля при использовании кэша.
   * @covers D\services\module\Router::get
   */
  public function testShouldReturnAddressModuleIfCacheRun(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $this->assertEquals('SystemPackages', $r->get('SystemPackages'));
  }

  /**
   * Должен возвращать адрес модуля при не использовании кэша.
   * @covers D\services\module\Router::get
   */
  public function testShouldReturnAddressModuleIfCacheNotRun(){
    if(Cache::DRIVER != 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо отключить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $this->assertEquals('SystemPackages', $r->get('SystemPackages'));
  }

  /**
   * Должен возвращать имена всех зарегистрированных модулей при использовании кэша.
   * @covers D\services\module\Router::getAllNames
   */
  public function testShouldReturnModulesNamesIfCacheRun(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $this->assertEquals(array_keys(parse_ini_file('../map.ini', true)['Map']), $r->getAllNames());
  }

  /**
   * Должен возвращать имена всех зарегистрированных модулей при использовании кэша.
   * @covers D\services\module\Router::getAllNames
   */
  public function testShouldReturnModulesNamesIfCacheNotRun(){
    if(Cache::DRIVER != 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо отключить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $this->assertEquals(array_keys(parse_ini_file('../map.ini', true)['Map']), $r->getAllNames());
  }

  /**
   * Должен добавлять информацию о модуле в файл роутинга и кэш.
   * @covers D\services\module\Router::add
   */
  public function testShouldAddModuleInFileAndCache(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $r->add('TestModule');
    $this->assertEquals('TestModule', parse_ini_file('../map.ini', true)['Map']['TestModule']);
    $c = Cache::getInstance();
    $this->assertEquals('TestModule', $c->get('ModuleRouter::TestModule'));
    $this->assertTrue(array_search('TestModule', unserialize($c->get('ModuleRouter::modules'))) !== false);
    $r->remove('TestModule');
  }

  /**
   * Должен добавлять информацию о модуле и его родителе.
   * @covers D\services\module\Router::add
   */
  public function testShouldAddModuleAndParent(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $r->add('TestModule', 'SystemPackages');
    $this->assertEquals('SystemPackages/TestModule', parse_ini_file('../map.ini', true)['Map']['TestModule']);
    $c = Cache::getInstance();
    $this->assertEquals('SystemPackages/TestModule', $c->get('ModuleRouter::TestModule'));
    $this->assertTrue(array_search('TestModule', unserialize($c->get('ModuleRouter::modules'))) !== false);
    $r->remove('TestModule');
  }

  /**
   * Должен выбрасывать исключение, если модуль уже зарегистрирован.
   * @covers D\services\module\Router::add
   */
  public function testShouldThrowExceptionIfModuleRegister(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $r->add('SystemPackages');
  }

  /**
   * Должен удалять информацию о модулей из файла роутинга и кэша.
   * @covers D\services\module\Router::remove
   */
  public function testShouldRemoveModuleOfFileAndCache(){
    if(Cache::DRIVER == 'NullAdapter'){
      throw new \Exception('Невозможно выполнить тестирование. Необходимо включить кэширование.');
    }
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $r->add('TestModule');
    $r->remove('TestModule');
    $this->assertFalse(array_key_exists('TestModule', parse_ini_file('../map.ini', true)['Map']));
    $c = Cache::getInstance();
    $this->assertEquals(null, $c->get('ModuleRouter::TestModule'));
    $this->assertFalse(array_search('TestModule', unserialize($c->get('ModuleRouter::modules'))));
  }

  /**
   * Должен выбрасывать исключение, если модуль не зарегистрирован.
   * @covers D\services\module\Router::add
   */
  public function testShouldThrowExceptionIfModuleNotRegister(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    /**
     * @var Router $r
     */
    $r = Router::getInstance();
    $r->remove('TestModule');
  }
}
