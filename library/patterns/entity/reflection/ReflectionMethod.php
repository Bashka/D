<?php
namespace D\library\patterns\entity\reflection;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\exceptions\semanticExceptions\LackException;
use D\library\patterns\structure\metadata\Described;

/**
 * Отражение метода класса, расширенное возможностью аннотирования.
 * Класс так же дополнен возможностью получения отражений своих аргументов.
 * @author  Artur Sh. Mamedbekov
 */
class ReflectionMethod extends \ReflectionMethod implements Described{
  use TDocMetadata;

  /**
   * Метод возвращает отражение своего аргумента.
   * @param string $param Имя запрашиваемого аргумента.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра неверного типа или недопустимого значения.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается при запросе отражения не определенного члена.
   * @return \D\library\patterns\entity\reflection\ReflectionParameter Отражение аргумента.
   */
  public function getParameter($param){
    InvalidArgumentException::verify($param, 's', [1]);
    $className = $this->getDeclaringClass()->getName();
    $methodName = $this->getName();
    try{
      return new ReflectionParameter([$className, $methodName], $param);
    }
    catch(\ReflectionException $e){
      throw new LackException('Запрашиваемый аргумент ['.$param.'] отсутствует в целевом методе ['.$className.'::'.$methodName.'].');
    }
  }
}