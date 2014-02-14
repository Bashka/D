<?php
namespace D\model\classes;

use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;

/**
 * Класс является родительским по отношению ко всем контроллерам модулей.
 * @author Artur Sh. Mamedbekov
 */
abstract class ModuleController implements Singleton{
  use TSingleton;

  /**
   * Данный метод вызывается перед вызовом метода контроллера центральным контроллером.
   * Метод может быть переопределен в дочерних классах для его конкретизации.
   */
  public function afterRun(){
  }

  /**
   * Данный метод вызывается после вызова метода контроллера центральным контроллером.
   * Метод может быть переопределен в дочерних классах для его конкретизации.
   */
  public function beforeRun(){
  }
}
