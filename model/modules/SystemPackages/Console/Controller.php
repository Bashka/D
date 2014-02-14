<?php
namespace D\model\modules\SystemPackages\Console;

use D\library\patterns\entity\dataType\Integer;
use D\library\patterns\entity\dataType\special\system\Name;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\LackException;
use D\model\classes\ModuleController;
use D\model\modules\SystemPackages\ReflectionModule;
use D\services\module\Router;
use ReflectionClass;

class Controller extends ModuleController{
  // Методы для тестирования центрального контроллера.
  protected function emptyArgs(){}

  protected function dynamicArgs(){}

  protected function strongArgs(Integer $a){}

  protected function optionalArgs($a = 0){}

  // Интерфейс.
  /**
   * Метод используется для проверки функционирования слоя домена и центрального контроллера. Если данный метод возвращает строку ответа, значит система работает исправно.
   * @return string Ответ вида: The argument is transferred: параметр, параметр, ...
   */
  public function test(){
    return 'The argument is transferred: ' . implode(', ', func_get_args()) . '.';
  }

  /**
   * Метод возвращает имена всех зарегистрированных в системе, конкретных модулей, упорядоченных в порядке возрастания.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если один из зарегистрированных модулей не найден в файловой системе или не имеет требуемого свойства.
   * @return string[] Массив имен зарегистрированных в системе, конкретных модулей.
   */
  public function getModulesNames(){
    /**
     * @var Router $modulesRouter
     */
    $modulesRouter = Router::getInstance();
    $modules = $modulesRouter->getAllNames();
    foreach($modules as $k => $moduleName){
      $reflectionModule = new ReflectionModule($moduleName);
      if($reflectionModule->getType() != ReflectionModule::SPECIFIC){
        unset($modules[$k]);
      }
    }
    sort($modules);

    return $modules;
  }

  /**
   * Метод определяет доступные для данного модуля методы контроллера.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Имя целевого модуля.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случает отсутствия доступа к модулю.
   * @return string[] Массив имен доступных методов контроллера данного модуля.
   */
  public function getModuleActions(Name $module){
    $module = $module->getVal();
    try{
      $reflectionModule = new ReflectionModule($module);
      $reflectionController = new ReflectionClass($reflectionModule->getController());
    }
    catch(\Exception $e){
      throw new NotExistsException('Невозможно определить методы модуля ['.$module.'].', 1, $e);
    }
    $actions = [];
    /**
     * @var \ReflectionMethod $action
     */
    foreach($reflectionController->getMethods() as $action){
      if($action->isPublic() && $action->getDeclaringClass() == $reflectionController){
        $actions[] = $action->getName();
      }
    }
    // Исключение технических методов
    if(($p = array_search('afterRun', $actions)) !== false){
      unset($actions[$p]);
    }
    if(($p = array_search('beforeRun', $actions)) !== false){
      unset($actions[$p]);
    }
    sort($actions);

    return $actions;
  }

  /**
   * Метод возвращает имена аргументов метода контроллера и их тип.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @param \D\library\patterns\entity\dataType\special\system\Name $method Целевой метод.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается в случает отсутствия целевого метода или невозможности определения его аргументов.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случает отсутствия доступа к модулю.
   * @return string[] Массив имен аргументов метода вида: имяАргумента:тип.
   */
  public function getMethodArgs(Name $module, Name $method){
    $module = $module->getVal();
    $method = $method->getVal();
    try{
      $reflectionModule = new ReflectionModule($module);
      $reflectionController = new ReflectionClass($reflectionModule->getController());
    }
    catch(\Exception $e){
      throw new NotExistsException('Невозможно определить методы модуля ['.$module.'].', 1, $e);
    }
    if(!$reflectionController->hasMethod($method)){
      throw new LackException('Запрашиваемого метода ['.$method.'] контроллера модуля ['.$module.'] не существует.');
    }
    $action = $reflectionController->getMethod($method);
    if(!$action->isPublic() || $action->getDeclaringClass() != $reflectionController){
      throw new LackException('Запрашиваемого метода ['.$method.'] контроллера модуля ['.$module.'] не существует.');
    }
    $params = $action->getParameters();
    $result = [];
    foreach($params as $param){
      // Определение допустимого типа аргумента
      $classParam = $param->getClass();
      if($classParam){
        $classParam = $classParam->getName();
        $classParam = ':' . substr($classParam, strrpos($classParam, '\\') + 1);
      }
      else{
        $classParam = '';
      }
      $result[] = $param->getName() . $classParam;
    }

    return $result;
  }
} 