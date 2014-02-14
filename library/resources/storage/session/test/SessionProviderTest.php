<?php
namespace D\library\resources\storage\session\test;

use D\library\resources\storage\session\SessionProvider;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';

ob_start();
class SessionProviderTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var SessionProvider
   */
  protected $object;

  public static function tearDownAfterClass(){
    ob_end_flush();
  }

  protected function setUp(){
    $this->object = SessionProvider::getInstance();
  }

  /**
   * Должен открывать сессию.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::start
   */
  public function testShouldOpenSession(){
    $this->object->start();
    $this->assertEquals([], $_SESSION);
    $this->object->destroy();
  }

  /**
   * Должен установить указанное имя сессии, если оно переданно.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::start
   */
  public function testShouldSetSessionName(){
    $this->object->start('MySession');
    $this->assertEquals('MySession', session_name());
    $this->object->destroy();
  }

  /**
   * Должен установить идентификатор сессии, если он передан.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::start
   */
  public function testShouldSetSessionID(){
    $this->object->start('MySession', 'sessionID');
    $this->assertEquals('sessionID', session_id());
    $this->object->destroy();
  }

  /**
   * Должен возвращать идентификатор сессии.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::getID
   */
  public function testShouldReturnIDSession(){
    $this->object->start('MySession', 'sessionID');
    $this->assertEquals('sessionID', $this->object->getID());
    $this->object->destroy();
  }

  /**
   * Должен возвращать пустую строку, если сессия не открыта.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::getID
   */
  public function testShouldReturnEmptyStringIfSessionClose(){
    $this->assertEquals('', $this->object->getID());
  }

  /**
   * Должен возвращать имя текущей сессии.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::getName
   */
  public function testShouldReturnSessionName(){
    $this->object->start('MySession');
    $this->assertEquals('MySession', $this->object->getName());
    $this->object->destroy();
  }

  /**
   * Должен возвращать пустую строку, если сессия не открыта.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::getName
   */
  public function testShouldReturnEmptyStringIfSessionClose2(){
    $this->assertEquals('', $this->object->getName());
  }

  /**
   * Должен закрывать сессию и уничтожать всю связанную с ней информацию.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::destroy
   */
  public function testShouldDestroySession(){
    $this->object->start();
    $this->object->destroy();
    $this->assertTrue(session_status() == PHP_SESSION_NONE);
  }

  /**
   * Должен устанавливать значение в сессии.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::set
   */
  public function testShouldSetValueSession(){
    $this->object->start();
    $this->object->set('key', 'value');
    $this->assertEquals('value', $_SESSION['key']);
    $this->object->destroy();
  }

  /**
   * Должен вернуть данные из сессии.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::get
   */
  public function testShouldGetValueSession(){
    $this->object->start();
    $_SESSION['key'] = 'value';
    $this->assertEquals('value', $this->object->get('key'));
    $this->object->destroy();
  }

  /**
   * Должен удалить данные из сессии.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::remove
   */
  public function testShouldRemoveValueSession(){
    $this->object->start();
    $_SESSION['key'] = 'value';
    $this->object->remove('key');
    $this->assertFalse(array_key_exists('key', $_SESSION));
    $this->object->destroy();
  }

  /**
   * Должен возвращать true - если заданный ключ установлен в сессии.
   * @covers D\library\resources\storage\sessionsession\SessionProvider::hasKey
   */
  public function testShouldReturnTrueIfValueExists(){
    $this->object->start();
    $this->assertFalse($this->object->hasKey('key'));
    $_SESSION['key'] = 'value';
    $this->assertTrue($this->object->hasKey('key'));
    $this->object->destroy();
  }
}
