<?php
namespace D\library\resources\storage\session;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;

/**
 * Класс представляет интерфейс управления сессиями.
 * @author Artur Sh. Mamedbekov
 */
class SessionProvider implements Singleton{
  use TSingleton;

  /**
   * Имя сессии по умолчанию.
   */
  const DEFAULT_SESSION_NAME = 'PHPSESSID';

  /**
   * Метод открывает сессию.
   * @param string $sessionName [optional] Имя сессии.
   * @param string $id [optional] Идентификатор сессии.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - в случае успешного завершения операции, иначе - false.
   */
  public function start($sessionName = self::DEFAULT_SESSION_NAME, $id = null){
    InvalidArgumentException::verify($sessionName, 's', [1]);
    InvalidArgumentException::verify($id, 'ns', [1]);
    if(session_status() == PHP_SESSION_NONE){
      if(!is_null($id)){
        session_id($id);
      }
      session_name($sessionName);

      return session_start();
    }

    return true;
  }

  /**
   * Метод возвращает идентификатор текущей сессии.
   * @return string Идентификатор сессии или пустая строка, если сессия не открыта.
   */
  public function getID(){
    return session_id();
  }

  /**
   * Метод возвращает имя текущей сессии.
   * @return string Имя текущей сессии или пустая строк, если сессия не открыта.
   */
  public function getName(){
    if(session_status() != PHP_SESSION_ACTIVE){
      return '';
    }

    return session_name();
  }

  /**
   * Метод уничтожает сессию.
   */
  public function destroy(){
    if(session_status() == PHP_SESSION_ACTIVE){
      $_SESSION = [];
      unset($_COOKIE[session_name()]);
      session_destroy();
    }
  }

  /**
   * Метод записывает данные в сессию.
   * @param string $key Ключ.
   * @param string|number|boolean $value Значение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function set($key, $value){
    InvalidArgumentException::verify($key, 's', [1]);
    $_SESSION[$key] = $value;
  }

  /**
   * Метод возвращает данные из сессии.
   * @param string $key Ключ.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return string|null Возвращает значение ключа сессии или null в случае отсутствия данных в сессии.
   */
  public function get($key){
    InvalidArgumentException::verify($key, 's', [1]);

    return isset($_SESSION[$key])? $_SESSION[$key] : null;
  }

  /**
   * Метод удаляет данные из сессии.
   * @param string $key Ключ.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function remove($key){
    InvalidArgumentException::verify($key, 's', [1]);
    if(isset($_SESSION[$key])){
      unset($_SESSION[$key]);
    }
  }

  /**
   * Метод определяет имеются ли данные под заданным ключем в сессии.
   * @param string $key Ключ.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return boolean true - если данные имеются, иначе - false.
   */
  public function hasKey($key){
    InvalidArgumentException::verify($key, 's', [1]);

    return isset($_SESSION[$key]);
  }
}
