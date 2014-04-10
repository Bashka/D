<?php
namespace D\library\patterns\entity\handler;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Класс реализует механизм обработки различных ошибок и оповещений системы с использованием пользовательских функций-обработчиков. Класс использует статичный интерфейс взаимодействия из за особенностей реализации обработчика завершения скрипта register_shutdown_function, не позволяющего работать с Singleton.
 * @author Artur Sh. Mamedbekov
 */
class Handler{
  /**
   * @var callable[] Функции-обработчики фатальных ошибок.
   */
  private static $errorListeners = [];

  /**
   * @var callable[] Функции-обработчики предупреждений.
   */
  private static $warningListeners = [];

  /**
   * @var callable[] Функции-обработчики уведомлений.
   */
  private static $noticeListeners = [];

  /**
   * Метод регистрирует очередную функцию-обработчик фатальных ошибок.
   * Функция может принимать два аргумента: массив, возвращаемый функцией error_get_last и содержимое буфера вывода на момент появления ошибки.
   * После вызова всех зарегистрированных функций-обработчиков фатальных ошибок, работа скрипта прекращается, уведомляя клиента об ошибке 500.
   * Функции-обработчики вызываются в порядке их регистрации.
   * @param callable $listener Функция-обработчик фатальных ошибок.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public static function registerErrorListener($listener){
    if(!is_callable($listener)){
      throw InvalidArgumentException::getTypeException('function', gettype($listener));
    }
    self::$errorListeners[] = $listener;
  }

  /**
   * Метод регистрирует очередную функцию-обработчик предупреждений.
   * Функция может принимать четыре аргумента: код ошибки, сообщение ошибки, адрес файла, в котором произошла ошибка и номер строки, в которой произошла ошибка.
   * После вызова всех зарегистрированных функций-обработчиков предупреждений, класс проверяет возвратила ли хоть одна из них false, и если да, работа скрипта прекращается передавая обработку стандартному обработчику PHP.
   * Функции-обработчики вызываются в порядке их регистрации.
   * @param callable $listener Функция-обработчик предупреждений.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public static function registerWarningListener($listener){
    if(!is_callable($listener)){
      throw InvalidArgumentException::getTypeException('function', gettype($listener));
    }
    self::$warningListeners[] = $listener;
  }

  /**
   * Метод регистрирует очередную функцию-обработчик уведомлений.
   * Функция может принимать четыре аргумента: код ошибки, сообщение ошибки, адрес файла, в котором произошла ошибка и номер строки, в которой произошла ошибка.
   * После вызова всех зарегистрированных функций-обработчиков уведомлений, класс проверяет возвратила ли хоть одна из них false, и если да, работа скрипта прекращается передавая обработку стандартному обработчику PHP.
   * Функции-обработчики вызываются в порядке их регистрации.
   * @param callable $listener Функция-обработчик уведомлений.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public static function registerNoticeListener($listener){
    if(!is_callable($listener)){
      throw InvalidArgumentException::getTypeException('function', gettype($listener));
    }
    self::$noticeListeners[] = $listener;
  }

  /**
   * Метод вызывается при появлении фатальной ошибки.
   * @param array $error Данные, возвращаемые функцией error_get_last.
   * @param string $buffer Содержимое буфера вывода.
   */
  public static function notifyError(array $error, $buffer){
    foreach(self::$errorListeners as $listener){
      $listener($error, $buffer);
    }
  }

  /**
   * Метод вызывается при появлении предпруждения.
   * @param integer $code Код ошибки.
   * @param string $message Сообщение ошибки.
   * @param string $file Файл, в котором произошла ошибка.
   * @param integer $line Номер строки, в которой произошла ошибка.
   * @return boolean true - если все функции-обработчики вернули true, иначе - false.
   */
  public static function notifyWarning($code, $message, $file, $line){
    $result = count(self::$warningListeners); // Если не установлен ни один обработчик, будет возвращен false.
    foreach(self::$warningListeners as $listener){
      $result *= $listener($code, $message, $file, $line);
    }

    return (boolean) $result;
  }

  /**
   * Метод вызывается при появлении уведомления.
   * @param integer $code Код ошибки.
   * @param string $message Сообщение ошибки.
   * @param string $file Файл, в котором произошла ошибка.
   * @param integer $line Номер строки, в которой произошла ошибка.
   * @return boolean true - если все функции-обработчики вернули true, иначе - false.
   */
  public static function notifyNotice($code, $message, $file, $line){
    $result = count(self::$noticeListeners); // Если не установлен ни один обработчик, будет возвращен false.
    foreach(self::$noticeListeners as $listener){
      $result *= $listener($code, $message, $file, $line);
    }

    return (boolean) $result;
  }
}

ob_start();
// Реакция на Error
register_shutdown_function(function (){
  $error = error_get_last();
  if($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_CORE_WARNING | E_COMPILE_WARNING | E_PARSE)){
    $buffer = ob_get_contents();
    ob_end_clean();
    header('HTTP/1.0 500 Internal server error');
    Handler::notifyError($error, $buffer);
  }
  else{
    ob_end_flush();
  }
});
// Реакция на Warning и Notice
set_error_handler(function ($code, $message, $file, $line){
  // Warning
  if($code & (E_WARNING | E_USER_WARNING)){
    return Handler::notifyWarning($code, $message, $file, $line);
  }
  // Notice
  elseif($code & (E_NOTICE | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED)){
    return Handler::notifyNotice($code, $message, $file, $line);
  }

  return false;
});
