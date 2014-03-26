<?php
namespace D\library\patterns\structure\conversion\test;

use D\library\patterns\structure\conversion\RestorableAdapter;

class TRestorableMock extends RestorableAdapter{
  protected $vars = [];

  public static function getMasks(){
    return ['(' . self::getPatterns()['varName'] . '):(' . self::getPatterns()['varVal'] . ')', '(' . self::getPatterns()['varName'] . ') (' . self::getPatterns()['varVal'] . ')'];
  }

  public static function getPatterns(){
    return ['varName' => '[A-Za-z_][A-Za-z0-9_]*', 'varVal' => '[1-9][0-9]*'];
  }

  public static function updateString(&$string){
    $string = str_replace('*', ':', $string);
  }
}