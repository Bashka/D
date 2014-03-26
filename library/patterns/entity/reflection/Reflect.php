<?php
namespace D\library\patterns\entity\reflection;

/**
 * Класс, реализующий данный интерфейс, способен возвращать отражение себя и своих членов.
 * @author  Artur Sh. Mamedbekov
 */
interface Reflect{
  /**
   * Метод возвращает отражение свойства вызываемого класса в том числе, если свойство относится к родительскому классу.
   * @param string $name Имя свойства.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается при запросе отражения не определенного члена.
   * @return \D\library\patterns\entity\reflection\ReflectionProperty Отражение свойства класса.
   */
  static public function getReflectionProperty($name);

  /**
   * Метод возвращает отражение метода вызываемого класса в том числе, если метод относится к родительскому классу.
   * @param string $name Имя метода.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\LackException Выбрасывается при запросе отражения не определенного члена.
   * @return \D\library\patterns\entity\reflection\ReflectionMethod Отражение метода класса.
   */
  static public function getReflectionMethod($name);

  /**
   * Метод возвращает отражение класса вызываемого объекта.
   * @return \D\library\patterns\entity\reflection\ReflectionClass Отражение класса.
   */
  static public function getReflectionClass();

  /**
   * Метод возвращает отражение родительского класса.
   * @return \D\library\patterns\entity\reflection\ReflectionClass|null Отражение родительского класса или null - если данный класс является вершиной иерархии наследования.
   */
  static public function getParentReflectionClass();

  /**
   * Метод возвращает отражения всех свойств вызываемого объекта и его родителей.
   * @return \D\library\patterns\entity\reflection\ReflectionProperty[] Отражение всех свойств класса в виде ассоциативного массива, ключами которого являются имена, а значениями отражения свойств.
   */
  static public function getAllReflectionProperties();

  /**
   * Метод возвращает отражения всех методов вызываемого объекта и его родителей.
   * @return \D\library\patterns\entity\reflection\ReflectionMethod[] Отражение всех методов класса в виде ассоциативного массива, ключами которого являются имена, а значениями отражения методов.
   */
  static public function getAllReflectionMethods();
}
