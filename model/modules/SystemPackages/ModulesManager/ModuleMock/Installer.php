<?php
namespace D\model\modules\SystemPackages\ModulesManager\ModuleMock;

use D\model\classes\ModuleInstaller;

class Installer extends ModuleInstaller{
  public function install(){
    return 'install';
  }

  public function uninstall(){
    return 'uninstall';
  }
}