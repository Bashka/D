<?php
namespace D\model\modules\SystemPackages;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\exceptions\semanticExceptions\LackException;
use D\services\module\Router;

/**
 * Объекты данного класса представляют отражения модулей, установленных в системе.
 * @author Artur Sh. Mamedbekov
 */
class ReflectionModule extends ReflectionUtility{
  /**
   * Тип модуля - конкретный.
   */
  const SPECIFIC = 'specific';

  /**
   * Тип модуля - виртуальный.
   */
  const VIRTUAL = 'virtual';

  /**
   * @prototype D\model\modules\SystemPackages\ReflectionUtility
   */
  public function getLocationAddress(){
    try{
      /**
       * @var \D\services\module\Router $mr
       */
      $mr = Router::getInstance();
      return '/' . Router::MODULES_DIR . '/' . $mr->get($this->name);
    }
    catch(NotExistsException $e){
      throw new NotExistsException('Информация о модуле [' . $this->name . '] не найдена в службе роутинга. Невозможно определить местонахождение модуля в файловой системе.', 1, $e);
    }
  }

  /**
   * Метод возвращает версию модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string Версия модуля.
   */
  public function getVersion(){
    try{
      return $this->state->get('Component', 'version');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::version] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
  }

  /**
   * Метод возвращает тип модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string Тип модуля.
   */
  public function getType(){
    try{
      return $this->state->get('Component', 'type');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::type] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
  }

  /**
   * Метод возвращает допустимый диапазон версий платформы для работоспособности модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string[] Допустимый диапазон версий платформы для работоспособности модуля в виде массива, первым элементом которого является первая допустимая версия, а вторым - последняя.
   */
  public function getAllowablePlatformVersion(){
    try{
      return explode('-', $this->state->get('Component', 'platformVersion'));
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::platformVersion] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
  }

  /**
   * Метод возвращает массив имен модулей, от которых зависит данный модуль.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string[] Массив имен используемых модулей или пустой массив, если модуль не имеет зависимостей.
   */
  public function getUsed(){
    try{
      $property = $this->state->get('Depending', 'used');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::platformVersion] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
    if($property == ''){
      return [];
    }
    $property = explode(',', $property);
    // Исключение информации о требуемых версиях используемых модулей.
    foreach($property as &$module){
      $p = strpos($module, ':');
      if($p !== false){
        $module = substr($module, 0, $p);
      }
    }

    return $property;
  }

  /**
   * Метод возвращает ассоциативный массив допустимых диапазонов версий используемых данным модулем модулей.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return array[] Ассоциативный массив допустимых диапазонов версий используемых модулей. Массив имеет следующую структуру: [имяМодуля => [нижняяГраница, верхняяГраница], ...].
   */
  public function getAllowableVersionUsedModules(){
    try{
      $property = $this->state->get('Depending', 'used');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::platformVersion] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
    $property = explode(',', $property);
    $result = [];
    foreach($property as $module){
      $p = strpos($module, ':');
      if($p !== false){
        $module = explode(':', $module);
        $result[$module[0]] = explode('-', $module[1]);
      }
      else{
        $result[$module] = ['0.0', '0.0'];
      }
    }

    return $result;
  }

  /**
   * Метод возвращает массив имен модулей, которые зависят от данного модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string[] Массив имен зависимых модулей или пустой массив, если модуль не имеет зависимостей.
   */
  public function getDestitute(){
    try{
      $property = $this->state->get('Depending', 'destitute');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::platformVersion] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
    if($property == ''){
      return [];
    }

    return explode(',', $property);
  }

  /**
   * Метод добавляет информацию о зависимом модуле.
   * @param string $name Имя зависимого модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается в случае вызова метода для виртуального модуля.
   */
  public function addDestitute($name){
    InvalidArgumentException::verify($name, 's', [1]);
    if($this->getType() == self::VIRTUAL){
      throw new LackException('Вируальный модуль ['.$this->getName().'] не может иметь зависимости.');
    }
    $destitute = $this->getDestitute();
    if(array_search($name, $destitute) === false){
      $destitute[] = $name;
      $this->state->set('Depending', 'destitute', implode(',', $destitute));
      $this->state->rewrite();
    }
  }

  /**
   * Метод удаляет информацию о зависимом модуле.
   * @param string $name Имя зависимого модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается в случае вызова метода для виртуального модуля.
   */
  public function removeDestitute($name){
    InvalidArgumentException::verify($name, 's', [1]);
    if($this->getType() == self::VIRTUAL){
      throw new LackException('Вируальный модуль ['.$this->getName().'] не может иметь зависимости.');
    }
    $destitute = $this->getDestitute();
    if(($key = array_search($name, $destitute)) !== false){
      unset($destitute[$key]);
      $this->state->set('Depending', 'destitute', implode(',', $destitute));
      $this->state->rewrite();
    }
  }

  /**
   * Метод возвращает имя родительского модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string|null Имя родительского модуля или пустая строка - если модуль не имеет родителя.
   */
  public function getParent(){
    try{
      $property = $this->state->get('Depending', 'parent');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::version] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
    // Исключение информации о требуемых версиях родительского модуля.
    $p = strpos($property, ':');
    if($p !== false){
      $property = substr($property, 0, $p);
    }

    return $property;
  }

  /**
   * Метод возвращает диапазон допустимых версий родительского модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string[] Диапазон допустимых версий родительского модуля. Массив имеет следующую структуру: [нижняяГраница, верхняяГраница].
   */
  public function getAllowableVersionParentModule(){
    try{
      $property = $this->state->get('Depending', 'parent');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::version] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
    $p = strpos($property, ':');
    if($p !== false){
      return explode('-', substr($property, $p + 1));
    }
    else{
      return ['0.0', '0.0'];
    }
  }

  /**
   * Метод возвращает массив имен дочерних модулей.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   * @return string[] Массив имен дочерних модулей.
   */
  public function getChild(){
    try{
      $property = $this->state->get('Depending', 'children');
    }
    catch(NotFoundDataException $e){
      throw new NotFoundDataException('Запрашиваемое свойство [Component::version] отсутствует в файле состояния модуля ['.$this->getName().'].', 1, $e);
    }
    if($property == ''){
      return [];
    }

    return explode(',', $property);
  }

  /**
   * Метод добавляет информацию о дочернем модуле.
   * @param string $name Имя дочернего модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   */
  public function addChild($name){
    InvalidArgumentException::verify($name, 's', [1]);
    $child = $this->getChild();
    if(array_search($name, $child) === false){
      $child[] = $name;
      $this->state->set('Depending', 'children', implode(',', $child));
      $this->state->rewrite();
    }
  }

  /**
   * Метод удаляет информацию о дочернем модуле.
   * @param string $name Имя дочернего модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случает отсутствия запрашиваемого свойства модуля в файле состояния.
   */
  public function removeChild($name){
    InvalidArgumentException::verify($name, 's', [1]);
    $child = $this->getChild();
    if(($key = array_search($name, $child)) !== false){
      unset($child[$key]);
      $this->state->set('Depending', 'children', implode(',', $child));
      $this->state->rewrite();
    }
  }

  /**
   * Метод возвращает ассоциативный массив, ключами которого служат имена методов контроллера модуля, а значениями списки ролей, для которых эти методы запрещены.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается в случае вызова метода для виртуального модуля.
   * @return array[] Ассоциативный массив имен методов контроллера модуля запрещенных ролями. Массив имеет следующую структуру: [имяМетода => [роль, ...], ...].
   */
  public function getAccess(){
    if($this->getVersion() === self::VIRTUAL){
      throw new LackException('Вируальный модуль ['.$this->getName().'] не может иметь правил доступа');
    }
    if($this->state->hasSection('Access')){
      $accesses = $this->state->getSection('Access');
      foreach($accesses as &$access){
        $access = explode(',', $access);
      }
    }
    else{
      $accesses = [];
    }

    return $accesses;
  }

  /**
   * Метод возвращает контроллер данного модуля, если он является конкретным.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается в случае вызова метода для виртуального модуля.
   * @return \D\model\classes\ModuleController Контроллер модуля.
   */
  public function getController(){
    if($this->getType() == self::VIRTUAL){
      throw new LackException('Виртуальный модуль не имеет контроллер.');
    }
    /**
     * @var \D\model\classes\ModuleController $controller
     */
    $controller = str_replace('/', '\\', $this->getLocationAddress()) . '\Controller';

    return $controller::getInstance();
  }

  /**
   * Метод возвращает объект класса инсталлятора модуля.
   * @return null|\D\model\classes\ModuleInstaller Инсталлятор модуля или null если модуль не имеет его.
   */
  public function getInstaller(){
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . $this->getLocationAddress() . '/Installer.php')){
      /**
       * @var \D\model\classes\ModuleInstaller $installer
       */
      $installer = str_replace('/', '\\', $this->getLocationAddress()) . '\Installer';

      return $installer::getInstance();
    }
    else{
      return null;
    }
  }
}