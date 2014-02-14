<?php
namespace D\model\modules\SystemPackages;

/**
 * Объекты данного класса представляют пакеты модулей, используемых для установки в систему.
 * @author Artur Sh. Mamedbekov
 */
class ModulePackage extends Package{
  /**
   * Метод возвращает версию модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string Версия модуля.
   */
  public function getVersion(){
    return $this->get('Component', 'version');
  }

  /**
   * Метод возвращает тип модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string Тип модуля.
   */
  public function getType(){
    return $this->get('Component', 'type');
  }

  /**
   * Метод возвращает допустимый диапазон версий платформы для работоспособности модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string[] Допустимый диапазон версий платформы для работоспособности модуля в виде массива, первым элементом которого является первая допустимая версия, а вторым - последняя.
   */
  public function getAllowablePlatformVersion(){
    return explode('-', $this->get('Component', 'platformVersion'));
  }

  /**
   * Метод возвращает имя родительского модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string Имя родительского модуля или пустая строка - если модуль не имеет родителя.
   */
  public function getParent(){
    $property = $this->get('Depending', 'parent');
    // Исключение информации о требуемых версиях родительского модуля.
    $p = strpos($property, ':');
    if($p !== false){
      $property = substr($property, 0, $p);
    }

    return $property;
  }

  /**
   * Метод возвращает диапазон допустимых версий родительского модуля.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string[] Диапазон допустимых версий родительского модуля. Массив имеет следующую структуру: [нижняяГраница, верхняяГраница]. Метод возвращает пустой массив если модуль не имеет родителя.
   */
  public function getAllowableVersionParentModule(){
    $property = $this->get('Depending', 'parent');
    if($property == ''){
      return [];
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
   * Метод возвращает массив имен модулей, от которых зависит данный модуль.
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return string[] Массив имен используемых модулей или пустой массив, если модуль не имеет зависимостей.
   */
  public function getUsed(){
    $property = $this->get('Depending', 'used');
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
   * @throws \D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException Выбрасывается в случае отсутствия обязательного свойства конфигурации.
   * @return array[] Ассоциативный массив допустимых диапазонов версий используемых модулей. Массив имеет следующую структуру: [имяМодуля => [нижняяГраница, верхняяГраница], ...]. Если модуль не использует другие модули, возвращается пустой массив.
   */
  public function getAllowableVersionUsedModules(){
    $property = $this->get('Depending', 'used');
    if($property == ''){
      return [];
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
} 