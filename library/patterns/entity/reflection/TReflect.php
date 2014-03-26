<?php
namespace D\library\patterns\entity\reflection;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\exceptions\semanticExceptions\LackException;

/**
 * Классическая реализация интерфейса Reflect.
 * @author  Artur Sh. Mamedbekov
 */
trait TReflect{
  /**
   * @prototype D\library\patterns\entity\reflection\Reflect
   */
  static public function getReflectionProperty($name){
    InvalidArgumentException::verify($name, 's', [1]);
    /**
     * @var Reflect $class
     */
    $class = get_called_class();
    while(!property_exists($class, $name)){
      if(method_exists($class, 'getReflectionClass')){
        $parentClass = $class::getReflectionClass()->getParentClass();
        if($parentClass !== false){
          $class = $parentClass->getName();
          continue;
        }
      }
      throw new LackException('Указанное свойство [' . $name . '] отсутствует в вызываемом классе ['.get_called_class().'] и его надклассах.');
    }

    return new ReflectionProperty($class, $name);
  }

  /**
   * @prototype D\library\patterns\entity\reflection\Reflect
   */
  static public function getReflectionMethod($name){
    InvalidArgumentException::verify($name, 's', [1]);
    /**
     * @var Reflect $class
     */
    $class = get_called_class();
    while(!method_exists($class, $name)){
      $parentClass = $class::getReflectionClass()->getParentClass();
      if($parentClass === false){
        throw new LackException('Указанный метод [' . $name . '] отсутствует в вызываемом классе ['.get_called_class().'] и его надклассах.');
      }
      $class = $parentClass->getName();
    }

    return new ReflectionMethod($class, $name);
  }

  /**
   * @prototype D\library\patterns\entity\reflection\Reflect
   */
  static public function getReflectionClass(){
    return new ReflectionClass(get_called_class());
  }

  /**
   * @prototype D\library\patterns\entity\reflection\Reflect
   */
  static public function getParentReflectionClass(){
    $parentClass = static::getReflectionClass()->getParentClass();
    if(!$parentClass){
      return null;
    }
    return new ReflectionClass($parentClass->getName());
  }

  /**
   * @prototype D\library\patterns\entity\reflection\Reflect
   */
  static public function getAllReflectionProperties(){
    $reflectionProperties = [];
    /**
     * @var Reflect $className
     */
    $className = get_called_class();
    /**
     * @var ReflectionClass $reflectionClass
     */
    $reflectionClass = $className::getReflectionClass();
    do{
      /**
       * @var ReflectionProperty[] $properties
       */
      $properties = $reflectionClass->getProperties();
      foreach($properties as $property){
        if(array_key_exists($property->getName(), $reflectionProperties)){
          continue;
        }
        $className = $reflectionClass->getName();
        $reflectionProperties[$property->getName()] = $className::getReflectionProperty($property->getName());
      }
      $reflectionClass = $reflectionClass->getParentClass();
    } while($reflectionClass !== false);

    return $reflectionProperties;
  }

  /**
   * @prototype D\library\patterns\entity\reflection\Reflect
   */
  static public function getAllReflectionMethods(){
    $reflectionMethods = [];
    /**
     * @var Reflect $className
     */
    $className = get_called_class();
    /**
     * @var ReflectionClass $reflectionClass
     */
    $reflectionClass = $className::getReflectionClass();
    do{
      /**
       * @var ReflectionMethod[] $methods
       */
      $methods = $reflectionClass->getMethods();
      foreach($methods as $method){
        if(array_key_exists($method->getName(), $reflectionMethods)){
          continue;
        }
        $className = $reflectionClass->getName();
        $reflectionMethods[$method->getName()] = $className::getReflectionMethod($method->getName());;
      }
      $reflectionClass = $reflectionClass->getParentClass();
    } while($reflectionClass !== false);

    return $reflectionMethods;
  }
}
