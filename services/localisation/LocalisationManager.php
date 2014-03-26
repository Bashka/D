<?php
namespace D\services\localisation;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\resources\storage\session\SessionProvider;
use D\services\config\SystemConf;

/**
 * Класс позволяет локализовать сообщения в соответствии с файлами локализации.
 * Файл локализации толжен находится в том же каталоге, что и локализуемый класс, начинаться с того же имени, иметь расширение local, а так же иметь постфикс в соответствии с локализацией (так для английской локализации постфикс должен иметь вид _en).
 * Пример файла локализации: stdClass_ru.local
 * @author Artur Sh. Mamedbekov
 */
class LocalisationManager implements Singleton{
  use TSingleton;

  /**
   * Английская локализация.
   */
  const ENGLISH = 'en';

  /**
   * Русская локализация.
   */
  const RUSSIA = 'ru';

  /**
   * @var string Текущая локализация.
   */
  private $currentLocalise;

  private function __construct(){
    /**
     * @var SessionProvider $session
     */
    $session = SessionProvider::getInstance();
    $session->start();
    if($session->hasKey('LocalisationManager::localise')){
      $this->setLocalise($session->get('LocalisationManager::localise'));
    }
    else{
      $this->setLocalise($this->getDefaultLanguage());
    }
  }

  /**
   * Метод возвращает адрес файла локализации для данного класса.
   * @param \ReflectionClass $class Целевой класс.
   * @return string Адрес файла локализации относительно корня системы.
   */
  private function getLocaliseFileAddress(\ReflectionClass $class){
    return '/' . str_replace('\\', '/', $class->getName()) . '_' . $this->currentLocalise . '.local';
  }

  /**
   * Метод возвращает маски всех возможных языков локализации.
   * @return string[] Массив масок всех возможных языков локализации.
   */
  public function getLanguages(){
    return [self::ENGLISH, self::RUSSIA];
  }

  /**
   * Метод возвращает локализацию по умолчанию.
   * @return string Локализация по умолчанию.
   */
  public function getDefaultLanguage(){
    /**
     * @var SystemConf $conf
     */
    $conf = SystemConf::getInstance();

    return $conf->get('Localisation', 'defaultLanguage');
  }

  /**
   * Метод устанавливает текущую локализацию.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @param string $localise Одна из констант класса, определяющая локализацию.
   */
  public function setLocalise($localise){
    $languages = $this->getLanguages();
    if(array_search($localise, $languages) === false){
      throw InvalidArgumentException::getValidException(implode(', ', $languages), $localise);
    }
    $this->currentLocalise = $localise;
  }

  /**
   * Метод возвращает используемый язык локализации.
   * @return string Язык локализации.
   */
  public function getLocalise(){
    return $this->currentLocalise;
  }

  /**
   * Метод локализует сообщение в соответствии с файлом локализации.
   * @param string $file Адрес файла локализации относительно корня системы.
   * @param string $message Локализуемое сообщение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string Локализованное сообщение или входящее сообщение, если для него не определены данные для локализации или файла локализации не найдено.
   */
  public function localiseMessage($file, $message){
    InvalidArgumentException::verify($file, 's', [1]);
    InvalidArgumentException::verify($message, 's', [1]);
    $file = $_SERVER['DOCUMENT_ROOT'].$file;
    if(!file_exists($file)){
      return $message;
    }
    $localData = parse_ini_file($file);
    if(isset($localData[$message])){
      return $localData[$message];
    }

    return $message;
  }

  /**
   * Метод локализует имя класса.
   * @param \ReflectionClass $class Локализуемый класс.
   * @return string Локализованное имя класса или оригинальное имя класса, если для него не определены данные для локализации.
   */
  public function localiseClass(\ReflectionClass $class){
    return $this->localiseMessage($this->getLocaliseFileAddress($class), $class->getShortName());
  }

  /**
   * Метод локализует свойство класса
   * @param \ReflectionClass $class Класс, членом которого является локализуемое свойство.
   * @param string $property Имя локализуемого свойства.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string Локализованное имя свойства класса или оригинальное имя свойства класса, если для него не определены данные для локализации
   */
  public function localiseProperty(\ReflectionClass $class, $property){
    InvalidArgumentException::verify($property, 's', [1]);
    return $this->localiseMessage($this->getLocaliseFileAddress($class), $property);
  }
}