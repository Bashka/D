<?php
namespace D\services\localisation\test;

use D\library\resources\storage\session\SessionProvider;
use D\services\localisation\LocalisationManager;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
session_start();
class LocalisationManagerTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен получать текущую локализацию из сессии.
   * @covers D\services\localisation\LocalisationManager::__construct
   */
  public function testShouldGetCurrentLocalWithSession(){
    /**
     * @var SessionProvider $session
     */
    $session = SessionProvider::getInstance();
    $session->start();
    $session->set('LocalisationManager::localise', LocalisationManager::ENGLISH);
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals($lm::ENGLISH, $lm->getLocalise());
    $session->remove('LocalisationManager::localise');
  }

  /**
   * Должен устанавливать локализацию по умолчанию, если в сессии отсутствует информация.
   * @covers D\services\localisation\LocalisationManager::__construct
   */
  public function testShouldSetDefaultLocalIfSessionNotExists(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals($lm::RUSSIA, $lm->getLocalise());
  }

  /**
   * Должен возвращать массив доступных языков локализации.
   * @covers D\services\localisation\LocalisationManager::getLanguages
   */
  public function testShouldLocaliseLanguages(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals([$lm::ENGLISH, $lm::RUSSIA], $lm->getLanguages());
  }

  /**
   * Должен возвращать используемый по умолчанию язык локализации.
   * @covers D\services\localisation\LocalisationManager::getDefaultLanguage
   */
  public function testShouldReturnDefaultLanguage(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals($lm::RUSSIA, $lm->getDefaultLanguage());
  }

  /**
   * Должен устанавливать текущий язык локализации.
   * @covers D\services\localisation\LocalisationManager::setLocalise
   */
  public function testShouldSetLocaliseLanguage(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $lm->setLocalise($lm::ENGLISH);
    $this->assertEquals($lm::ENGLISH, $lm->getLocalise());
    $lm->setLocalise($lm::RUSSIA);
  }

  /**
   * Должен выбрасывать исключение если целевой язык недопустим.
   * @covers D\services\localisation\LocalisationManager::setLocalise
   */
  public function testShouldThrowExceptionIfLanguageNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $lm->setLocalise('testLang');
  }

  /**
   * Должен возвращать используемый язык локализации.
   * @covers D\services\localisation\LocalisationManager::getLocalise
   */
  public function testShouldReturnLocaliseLanguage(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals($lm::RUSSIA, $lm->getLocalise());
  }

  /**
   * Должен локализовывать указанное сообщение.
   * @covers D\services\localisation\LocalisationManager::localiseMessage
   */
  public function testShouldLocaliseMessage(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals('Свойство', $lm->localiseMessage('/D/services/localisation/test/ObjectMock_ru.local', 'property'));
  }

  /**
   * Должен возвращать целевое сообщение, если локализация не найдена.
   * @covers D\services\localisation\LocalisationManager::localiseMessage
   */
  public function testShouldReturnMessageIfLocaliseNotFound(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals('Test', $lm->localiseMessage('/D/services/localisation/test/ObjectMock_ru.local', 'Test'));
  }

  /**
   * Должен локализовывать имя класса.
   * @covers D\services\localisation\LocalisationManager::localiseClass
   */
  public function testShouldLocaliseClassName(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals('Тестовый объект', $lm->localiseClass(new \ReflectionClass('D\services\localisation\test\ObjectMock')));
  }

  /**
   * Должен локализовывать свойство класса.
   * @covers D\services\localisation\LocalisationManager::localiseProperty
   */
  public function testShouldLocaliseProperty(){
    /**
     * @var LocalisationManager $lm
     */
    $lm = LocalisationManager::getInstance();
    $this->assertEquals('Свойство', $lm->localiseProperty(new \ReflectionClass('D\services\localisation\test\ObjectMock'), 'property'));
  }
}
 