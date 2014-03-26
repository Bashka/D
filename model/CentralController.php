<?php
namespace D\model;

use D\library\patterns\entity\dataType\special\system\Name;
use D\library\patterns\entity\exceptions\dataExceptions\StructureDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\LockException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\handler\Handler;
use D\library\patterns\structure\conversion\Restorable;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\model\classes\ModuleController;
use D\services\log\Log;
use D\services\module\Router;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';

/**
 * Служба является единой точной входа системы и отвечает за вызов и передачу модулю сообщений от слоя view, а так же за возврат ему ответа модуля.
 * @author Artur Sh. Mamedbekov
 */
class CentralController implements Singleton{
use TSingleton;

  /**
   * Метод возвращает контроллер модуля.
   * @param string $module Имя целевого модуля.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого модуля.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return \D\model\classes\ModuleController Контроллер модуля.
   */
  public function getController($module){
    // Проверка параметра выполняется в методе Router::get.
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $controllerFile = Router::MODULES_DIR.'/'.$router->get($module).'/Controller';
    $controller = str_replace('/', '\\', $controllerFile);
    $controllerFile = $_SERVER['DOCUMENT_ROOT'].'/'.$controllerFile.'.php';
    if(!file_exists($controllerFile)){
      throw new NotExistsException('Контроллер модуля ['.$module.'] не найден.');
    }
    /**
     * @var ModuleController $controller
     */
    return $controller::getInstance();
  }

  /**
   * Метод выполняет верификацию параметров метода модуля. Метод пропускает пустые параметры.
   * @param string $module Целевой модуль.
   * @param string $active Метод-основание.
   * @param mixed[] $params Массив проверяемых данных.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при нахождении хотя бы одного параметра, не прошедшего верификацию.
   * @return mixed[] Верифицированные данные, завернутые в соответствующие обертки.
   */
  public function verifyParams($module, $active, array $params){
    $reflectionParameters = (new \ReflectionMethod($this->getController($module), $active))->getParameters();
    foreach($reflectionParameters as $paramIndex => $reflectionParam){
      // Исключение пустого параметра.
      if(isset($params[$paramIndex]) && $params[$paramIndex] === ''){
        unset($params[$paramIndex]);
      }
      // Проверка числа аргументов и их опциональности.
      if(!isset($params[$paramIndex])){
        if($reflectionParam->isDefaultValueAvailable()){
          continue;
        }
        else{
          throw new InvalidArgumentException('Не передан обязательный параметр ['.$reflectionParam->getName().'] метода ['.$module.'::'.$active.'].');
        }
      }
      $verifyClass = $reflectionParam->getClass();
      // Исключение не типизированных аргументов.
      if(is_null($verifyClass)){
        continue;
      }
      $verifyClass = $verifyClass->getName();
      try{
        /**
         * @var Restorable $verifyClass
         */
        $params[$paramIndex] = $verifyClass::reestablish((string) $params[$paramIndex]);
      }
      catch(StructureDataException $e){
        throw new InvalidArgumentException('Недопустимое значение параметра. Ожидается [' . $verifyClass . '] вместо [' . $params[$paramIndex] . '].', 1, $e);
      }
    }
    return $params;
  }

  /**
   * Метод вызывает указанный метод модуля передавая ему параметры.
   * @param string $module Целевой модуль.
   * @param string $active Вызываемый метод модуля.
   * @param mixed[] $params [optional] Параметры, передаваемые методу.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого модуля или вызываемого метода.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\LockException Выбрасывается в случае невозможности вызова метода модуля из за отсутствия прав.
   * @return mixed Данные, возвращаемые методом модуля, null - если метод ничего не возвращает.
   */
  public function run($module, $active, array $params = null){
    // Проверка параметра $module выполняется в методе getController.
    InvalidArgumentException::verify($active, 's', [1]);
    if(is_null($params)){
      $params = [];
    }
    $controller = $this->getController($module);
    if(!method_exists($controller, $active) || $active == 'afterRun' || $active == 'beforeRun'){
      throw new NotExistsException('Целевой метод ['.$active.'] модуля ['.$module.'] не найден.');
    }
    // Контроль доступа.
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    if($router->hasModule('Access')){
      if(!$this->getController('Access')->isResolved(new Name($module), new Name($active))){
        throw new LockException('Недостаточно прав для вызова метода ['.$module.'::'.$active.'].');
      }
    }
    // Вызов.
    $controller->afterRun();
    $result = call_user_func_array([$controller, $active], $params);
    $controller->beforeRun();
    return $result;
  }

  /**
   * Метод обрабатывает запрос экрана слоя представления.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при нахождении хотя бы одного параметра, не прошедшего верификацию.
   */
  public function main(){
    if(isset($_REQUEST['module']) && isset($_REQUEST['action'])){
      $module = $_REQUEST['module'];
      $action = $_REQUEST['action'];
      if(isset($_REQUEST['params'])){
        $params = json_decode($_REQUEST['params']);
      }
      else{
        $params = [];
      }
      echo json_encode($this->run($module, $action, $this->verifyParams($module, $action, $params)));
    }
  }
}

// Журналирование ошибок.
Handler::registerNoticeListener(function($code, $message, $file, $line){
  /**
   * @var Log $log
   */
  $log = Log::getInstance();
  $log->addNotice($message . ' - ' . $file . ':' . $line);
});
Handler::registerWarningListener(function($code, $message, $file, $line){
  /**
   * @var Log $log
   */
  $log = Log::getInstance();
  $log->addWarning($message . ' - ' . $file . ':' . $line);
});
Handler::registerErrorListener(function($error, $buffer){
  /**
   * @var Log $log
   */
  $log = Log::getInstance();
  $log->addError($error['message'] . ' - ' . $error['file'] . ':' . $error['line']);
  // Передача ошибки на уровень представления.
  $error['buffer'] = $buffer;
  echo json_encode($error);
});

// Обработка запроса экрана. Метод не реагирует, если в параметрах вызова $_REQUEST нет данных о вызываемом экране.
CentralController::getInstance()->main();