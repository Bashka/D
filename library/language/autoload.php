<?php
namespace D\library\language;

/**
 * Данный скрипт реализует механизм автоматической загрузки классов при первом обращении к ним.
 * Скрипт учитывает работу системы через командную строку.
 */

// Определение корня системы.
if(empty($_SERVER['DOCUMENT_ROOT'])){
  $_SERVER['DOCUMENT_ROOT'] = substr(__DIR__, 0, strpos(__DIR__, '/D'));
}

spl_autoload_register(function ($className){
  require_once $_SERVER['DOCUMENT_ROOT'] .'/'. str_replace('\\', '/', $className) . '.php';
});