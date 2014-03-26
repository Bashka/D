<?php
namespace D\model\classes;

use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;

/**
 * Родительский класс, для всех внутренних инсталляторов.
 * @author Artur Sh. Mamedbekov
 */
abstract class ModuleInstaller implements Singleton{
  use TSingleton;

  /**
   * Метод настраивает систему для устанавливаемого модуля.
   * @return string Информация об установке.
   */
  public abstract function install();

  /**
   * Метод обновляет систему при обновлении модуля.
   * @return string Информация об обновлении.
   */
  public function upgrade(){
  }

  /**
   * Метод отменяет изменения в системе при удалении модуля.
   * @return string Информация об удалении.
   */
  public abstract function uninstall();
}


