<?php
namespace D\model\modules\SystemPackages\ModulesManager;

use D\library\patterns\entity\dataType\Boolean;
use D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress;
use D\library\patterns\entity\dataType\special\system\Alias;
use D\library\patterns\entity\dataType\special\system\GUID4;
use D\library\patterns\entity\dataType\special\system\Name;
use D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\resources\fileSystem\components\Directory;
use D\library\resources\fileSystem\components\special\ini\INI;
use D\library\resources\storage\database\ORM\UncertaintyException;
use D\model\classes\ModuleController;
use D\model\modules\SystemPackages\ModulePackage;
use D\model\modules\SystemPackages\ReflectionModule;
use D\services\config\SystemConf;
use D\services\module\Router;

/**
 * Данный модуль отвечает за установку и удаление модулей. Модуль позволяет выполнять пошаговую установку и удаление, а так же автоматизирует этот процесс с помощью фассадных методов install и uninstall.
 * @author Artur Sh. Mamedbekov
 */
class Controller extends ModuleController{
  /**
   * Метод возвращает абсолютный адрес пакета от корня системы.
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес пакета.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или его файла конфигурации.
   * @return string Абсолютный адрес пакета от корня системы.
   */
  private function getAbsoluteAddress(FileSystemAddress $packageAddress){
    if(!$packageAddress->isRoot()){
      $packageAddress = $_SERVER['DOCUMENT_ROOT'] . '/' . $packageAddress->getVal();
    }
    else{
      $packageAddress = $_SERVER['DOCUMENT_ROOT'] . $packageAddress->getVal();
    }

    return $packageAddress;
  }

  /**
   * Метод определяет, установлен ли указанный модуль в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Имя целевого модуля.
   * @return boolean true - если модуль установлен, иначе - false.
   */
  public function hasModule(Name $module){
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    return $router->hasModule($module->getVal());
  }

  /**
   * Метод возвращает информацию о неудовлетворенных зависимостях пакета относительно платформы.
   * Ответ представлен в виде массива следующей структуры: [assert => [нижнийДиапазон, верхнийДиапазон], actual => текущаяВерсия].
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\StructureDataException Выбрасывается в случае нарушения структуры пакета.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или его файла конфигурации.
   * @return string[] Информация о неудовлетворенной зависимости или пустой массив, если зависимость удовлетворена.
   */
  public function platformDepControl(FileSystemAddress $packageAddress){
    $result = [];
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    /**
     * @var SystemConf $sysConf
     */
    $sysConf = SystemConf::getInstance();
    $allowable = $package->getAllowablePlatformVersion();
    $actual = $sysConf->get('System', 'version');
    // Позиции выборки получены исходя из стандарта представления версии платформы: первый разделитель (точка) всегда располагается на 4 позиции с конца, а второй на 2 позиции с конца.
    $allowableStart = (int) substr($allowable[0], 0, -4) . substr($allowable[0], -3, -2) . substr($allowable[0], -1);
    $allowableEnd = (int) substr($allowable[1], 0, -4) . substr($allowable[1], -3, -2) . substr($allowable[1], -1);
    $actualInt = (int) substr($actual, 0, -4) . substr($actual, -3, -2) . substr($actual, -1);
    if($actualInt < $allowableStart || $actualInt > $allowableEnd){
      $result = ['assert' => $allowable, 'actual' => $actual];
    }

    return $result;
  }

  /**
   * Метод возвращает информацию о неудовлетворенных зависимостях пакета относительно родительского модуля.
   * Ответ представлен в виде массива следующей структуры: [assert => [нижнийДиапазон, верхнийДиапазон], actual => текущаяВерсия|пустаяСтрокаЕслиМодульНеУстановлен].
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\StructureDataException Выбрасывается в случае нарушения структуры пакета.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или его файла конфигурации.
   * @return string[] Информация о неудовлетворенной зависимости или пустой массив, если зависимость удовлетворена.
   */
  public function parentDepControl(FileSystemAddress $packageAddress){
    $result = [];
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    $parent = $package->getParent();
    if($parent !== ''){
      $allowable = $package->getAllowableVersionParentModule();
      if($router->hasModule($parent)){
        $parent = new ReflectionModule($parent);
        $actual = $parent->getVersion();
        // Позиции выборки получены исходя из стандарта представления версии модуля: разделитель (точка) всегда располагается на 2 позиции с конца.
        $allowableStart = (int) substr($allowable[0], 0, -2) . substr($allowable[0], -1);
        $allowableEnd = (int) substr($allowable[1], 0, -2) . substr($allowable[1], -1);
        $actualInt = (int) substr($actual, 0, -2) . substr($actual, -1);
        if($actualInt < $allowableStart || $actualInt > $allowableEnd){
          $result = ['assert' => $allowable, 'actual' => $actual];
        }
      }
      else{
        $result = ['assert' => $allowable, 'actual' => ''];
      }
    }

    return $result;
  }

  /**
   * Метод возвращает информацию о неудовлетворенных зависимостях пакета относительно используемых модулей.
   * Ответ представлен в виде массива следующей структуры: [имяМодуля => [assert => [нижнийДиапазон, верхнийДиапазон], actual => текущаяВерсия|пустаяСтрокаЕслиМодульНеУстановлен]].
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\StructureDataException Выбрасывается в случае нарушения структуры пакета.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или его файла конфигурации.
   * @return string[] Информация о неудовлетворенных зависимостях или пустой массив, если зависимости удовлетворены.
   */
  public function usedDepControl(FileSystemAddress $packageAddress){
    $result = [];
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $used = $package->getAllowableVersionUsedModules();
    foreach($used as $useModule => $allowable){
      if($router->hasModule($useModule)){
        $reflectionUsed = new ReflectionModule($useModule);
        $actual = $reflectionUsed->getVersion();
        // Позиции выборки получены исходя из стандарта представления версии модуля: разделитель (точка) всегда располагается на 2 позиции с конца.
        $allowableStart = (int) substr($allowable[0], 0, -2) . substr($allowable[0], -1);
        $allowableEnd = (int) substr($allowable[1], 0, -2) . substr($allowable[1], -1);
        $actualInt = (int) substr($actual, 0, -2) . substr($actual, -1);
        if($actualInt < $allowableStart || $actualInt > $allowableEnd){
          $result[$useModule] = ['assert' => $allowable, 'actual' => $actual];
        }
      }
      else{
        $result[$useModule] = ['assert' => $allowable, 'actual' => ''];
      }
    }

    return $result;
  }

  /**
   * Метод регистрирует модуль в роутере.
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если целевой модуль уже зарегистрирован.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или информации о родительском модуле в роутере.
   */
  public function register(FileSystemAddress $packageAddress){
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    $parent = $package->getParent();
    $parent = ($parent == '')? null : $parent;
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $router->add($package->getName(), $parent);
  }

  /**
   * Метод размещает модуль в хранилище модулей.
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если модуль уже имеется в хранилище.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или информации о родительском модуле в роутере.
   */
  public function expand(FileSystemAddress $packageAddress){
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    $name = $package->getName();
    // Определение корневого каталога модуля.
    $location = $_SERVER['DOCUMENT_ROOT'] . '/' . Router::MODULES_DIR;
    $parent = $package->getParent();
    if($parent != ''){
      /**
       * @var Router $router
       */
      $router = Router::getInstance();
      $location .= '/' . $router->get($parent);
    }
    $location .= '/' . $name;
    // Формирование каталога модуля.
    $location = new Directory($location);
    if($location->isExists()){
      throw new DuplicationException('Каталог целевого модуля [' . $name . '] уже имеется в хранилище.');
    }
    else{
      $location->create();
    }
    $package->getArchive()->extractTo($location->getAddress());
    // Формирование файла состояния модуля.
    $stateFile = $location->getFile('conf.ini');
    $stateFile->rename('state.ini');
    $stateFile = new INI($stateFile->getAddress());
    $stateFile->set('Depending', 'destitute', '');
    $stateFile->set('Depending', 'children', '');
    $stateFile->rewrite();
  }

  /**
   * Метод добавляет информацию о модуле в свойство Depending::children файла состояния родителя.
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или информации о родительском модуле в роутере.
   */
  public function addDepParent(FileSystemAddress $packageAddress){
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    $parent = $package->getParent();
    if($parent != ''){
      $parent = new ReflectionModule($parent);
      $parent->addChild($package->getName());
    }
  }

  /**
   * Метод добавляет информацию о модуле в свойства Depending::destitute всех используемых модулей.
   * Если используемый модуль отсутствует в системе, он пропускается методом.
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или его файла конфигурации.
   */
  public function addDepUsed(FileSystemAddress $packageAddress){
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    $name = $package->getName();
    $used = $package->getUsed();
    foreach($used as $module){
      try{
        $module = new ReflectionModule($module);
      }
      catch(NotExistsException $e){
        continue;
      }
      $module->addDestitute($name);
    }
  }

  /**
   * Метод выполняет внутреннюю инсталляцию модуля с помощью Installer этого модуля.
   * Целевой модуль должен быть установлен в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не найден.
   * @return string Информация, возвращаемая внутренним инсталлятором.
   */
  public function runInstaller(Name $module){
    $module = new ReflectionModule($module->getVal());
    $installer = $module->getInstaller();
    if($installer !== null){
      return $installer->install();
    }
    else{
      return '';
    }
  }

  /**
   * Метод устанавливает права доступа, определенные в файле состояния модуля, если модуль Access установлен.
   * Целевой модуль должен быть установлен в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не найден.
   */
  public function addAccessRules(Name $module){
    try{
      $access = new ReflectionModule('Access');
    }
    catch(NotExistsException $e){
      return;
    }
    $access = $access->getController();
    /**
     * @var \D\model\modules\System\Users\Access\Controller $access
     */
    $module = new ReflectionModule($module->getVal());
    $name = new Name($module->getName());
    $methods = $module->getAccess();
    foreach($methods as $method => $roles){
      $method = new Name($method);
      foreach($roles as $role){
        $role = new Alias($role);
        try{
          $rule = $access->addRule($name, $method);
        }
        catch(DuplicationException $e){
          $rule = $access->getOIDRule($name, $method);
        }
        try{
          $roleOID = $access->getOIDRole($role);
        }
        catch(UncertaintyException $e){
          $roleOID = $access->addRole($role);
        }
        try{
          $access->expandRole(new GUID4($roleOID), new GUID4($rule));
        }
        catch(DuplicationException $e){
          continue;
        }
      }
    }
  }

  /**
   * Метод устанавливает указанный модуль в систему.
   * В случае ошибки, модуль откатывает все произведенные изменения.
   * @param \D\library\patterns\entity\dataType\special\fileSystem\FileSystemAddress $packageAddress Адрес целевого пакета относительно корня системы.
   * @param \D\library\patterns\entity\dataType\Boolean $depControl [optional] Если в качестве данного параметра передается false, метод не будет проверять зависимости пакета перед установкой.
   * @param \D\library\patterns\entity\dataType\Boolean $runInstaller [optional] Если в качестве данного параметра передается false, метод не будет вызывать внутренний инсталлятор модуля.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае отсутствия целевого пакета или нарушения зависимостей модуля.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException Выбрасывается в случае, если целевой модуль уже присутствует в роутере или хранилище модулей.
   * @return string Информация, возвращаемая внутренним инсталлятором.
   */
  public function install(FileSystemAddress $packageAddress, Boolean $depControl = null, Boolean $runInstaller = null){
    $depControl = ($depControl === null || $depControl->getVal());
    $runInstaller = ($runInstaller === null || $runInstaller->getVal());
    // Контроль зависимостей.
    if($depControl){
      $dep = $this->platformDepControl($packageAddress);
      if(count($dep) != 0){
        throw new NotExistsException('Зависимости пакета [' . $packageAddress->getVal() . '] относительно платформы не удовлетворены [assert: ' . implode(' - ', $dep['assert']) . '; actual: ' . $dep['actual'] . ']. Установка невозможна.');
      }
      $dep = $this->parentDepControl($packageAddress);
      if(count($dep) != 0){
        throw new NotExistsException('Зависимости пакета [' . $packageAddress->getVal() . '] относительно родительского модуля не удовлетворены [assert: ' . implode(' - ', $dep['assert']) . '; actual: ' . $dep['actual'] . ']. Установка невозможна.');
      }
      $dep = $this->usedDepControl($packageAddress);
      if(count($dep) != 0){
        $message = '';
        foreach($dep as $module => $badDep){
          $message .= $module . ' assert: ' . implode(' - ', $badDep['assert']) . '; actual: ' . $badDep['actual'] . '; ';
        }
        throw new NotExistsException('Зависимости пакета [' . $packageAddress->getVal() . '] относительно используемых модулей не удовлетворены [' . $message . ']. Установка невозможна.');
      }
    }
    // Установка модуля.
    $package = new ModulePackage($this->getAbsoluteAddress($packageAddress));
    $name = new Name($package->getName());
    $this->register($packageAddress);
    try{
      $this->expand($packageAddress);
    }
    catch(DuplicationException $e){
      $this->unregister($name); // Откат изменений.
      throw $e;
    }
    try{
      $this->addDepParent($packageAddress);
    }
    catch(NotExistsException $e){
      if($depControl){
        // Откат изменений.
        $this->unregister($name);
        $this->delete($name);
        throw $e;
      }
    }
    $this->addDepUsed($packageAddress);
    $this->addAccessRules($name);
    if($runInstaller){
      return $this->runInstaller($name);
    }
    else{
      return '';
    }
  }

  /**
   * Метод исключает модуль из роутера.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не зарегистрирован.
   */
  public function unregister(Name $module){
    /**
     * @var Router $router
     */
    $router = Router::getInstance();
    $router->remove($module->getVal());
  }

  /**
   * Метод удаляет модуль из хранилища модулей.
   * Целевой модуль должен быть зарегистрирован в роутере.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не найден в хранилище модулей.
   */
  public function delete(Name $module){
    $module = new ReflectionModule($module->getVal());
    $dir = new Directory($_SERVER['DOCUMENT_ROOT'] . $module->getLocationAddress());
    $dir->delete();
  }

  /**
   * Метод удаляет информацию о модуле из свойства Depending::children файла состояния родителя.
   * Целевой модуль должен быть установлен в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой или родительский модуль не найдены.
   */
  public function removeDepParent(Name $module){
    $module = new ReflectionModule($module->getVal());
    $parent = $module->getParent();
    if($parent != ''){
      $parent = new ReflectionModule($parent);
      $parent->removeChild($module->getName());
    }
  }

  /**
   * Метод удаляет информацию о модуле из свойства Depending::destitute всех используемых модулей.
   * Целевой модуль должен быть установлен в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не найден.
   */
  public function removeDepUsed(Name $module){
    $module = new ReflectionModule($module->getVal());
    $name = $module->getName();
    $used = $module->getUsed();
    foreach($used as $usedModule){
      try{
        $usedModule = new ReflectionModule($usedModule);
      }
      catch(NotExistsException $e){
        continue; // Пропуск исключения, так как при отсутствии используемого модуля, информация о целевом модуле не может присутствовать в его файле состояния.
      }
      $usedModule->removeDestitute($name);
    }
  }

  /**
   * Метод выполняет внутреннюю деинсталляцию модуля с помощью Installer этого модуля.
   * Целевой модуль должен быть установлен в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не найден.
   * @return string Информация, возвращаемая внутренним инсталлятором.
   */
  public function runUninstaller(Name $module){
    $module = new ReflectionModule($module->getVal());
    $installer = $module->getInstaller();
    if($installer !== null){
      return $installer->uninstall();
    }
    else{
      return '';
    }
  }

  /**
   * Метод удаляет права доступа, определенные в файле состояния модуля, если модуль Access установлен.
   * Целевой модуль должен быть установлен в системе.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Целевой модуль.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если целевой модуль не найден.
   */
  public function removeAccessRules(Name $module){
    try{
      $access = new ReflectionModule('Access');
    }
    catch(NotExistsException $e){
      return;
    }
    $access = $access->getController();
    /**
     * @var \D\model\modules\System\Users\Access\Controller $access
     */
    $module = new ReflectionModule($module->getVal());
    $name = new Name($module->getName());
    $methods = $module->getAccess();
    foreach($methods as $method => $roles){
      $method = new Name($method);
      try{
        $rule = new GUID4($access->getOIDRule($name, $method));
      }
      catch(UncertaintyException $e){
        continue; // Пропуск прав, не зарегистрированных в системе.
      }
      foreach($roles as $role){
        $role = new Alias($role);
        try{
          $role = new GUID4($access->getOIDRole($role));
        }
        catch(UncertaintyException $e){
          $access->removeRule($rule); // В случае отсутствия роли, удаление права доступа.
        }
        // Сужение роли и удаление права доступа.
        $access->narrowRole($role, $rule);
        $access->removeRule($rule);
      }
    }
  }

  /**
   * Метод удаляет указанный модуль из системы.
   * Метод может почистить систему от остатков не полностью удаленного модуля, если он зарегистрирован в роутере и существует его файл состояния.
   * @param \D\library\patterns\entity\dataType\special\system\Name $module Имя целевого модуля.
   * @param \D\library\patterns\entity\dataType\Boolean $runUninstaller [optional] Если в качестве данного параметра передается false, метод не будет вызывать внутренний деинсталлятор модуля.
   * @param \D\library\patterns\entity\dataType\Boolean $recursive [optional] Если в качестве данного параметра передается true, метод не будет удалять дочерний или зависимые модули.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если на момент вызова метода целевой модуль не был установлен.
   * @return string Информация, возвращаемая внутренним деинсталлятором.
   */
  public function uninstall(Name $module, Boolean $runUninstaller = null, Boolean $recursive = null){
    $recursive = ($recursive === null || $recursive->getVal());
    $runUninstaller = ($runUninstaller === null || $runUninstaller->getVal());
    $name = $module;
    $module = new ReflectionModule($module->getVal());
    if($runUninstaller){
      $resultUnistall = $this->runUninstaller($name);
    }
    else{
      $resultUnistall = '';
    }
    try{
      $this->removeDepParent($name);
    }
    catch(NotExistsException $e){
      // Пропуск исключения, так как при отсутствии родительского модуля, информация о целевом модуле не может присутствовать в его файле состояния.
    }
    $this->removeDepUsed($name);
    $this->removeAccessRules($name);
    $this->delete($name); //
    $this->unregister($name); // Перехват исключений не выполняеться в связи с тем, что при отсутствии модуля в роутере или его файла состояния метод завершится при получении отражения модуля.
    if($recursive){
      $child = $module->getChild();
      foreach($child as $childName){
        try{
          $this->uninstall(new Name($childName));
        }
        catch(NotExistsException $e){
          // Пропуск исключения, если удаляемый модуль уже отсутствует в системе.
        }
      }
      $destitute = $module->getDestitute();
      foreach($destitute as $destituteName){
        try{
          $this->uninstall(new Name($destituteName));
        }
        catch(NotExistsException $e){
          // Пропуск исключения, если удаляемый модуль уже отсутствует в системе.
        }
      }
    }
    return $resultUnistall;
  }
}