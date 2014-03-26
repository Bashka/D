<?php
namespace D\library\patterns\entity\reflection\test;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class TReflectTest extends \PHPUnit_Framework_TestCase {
  /**
   * @var ChildReflectMock
   */
  static protected $child;

  /**
   * @var ParentReflectMock
   */
  static protected $parent;

  public static function setUpBeforeClass(){
    self::$child = new ChildReflectMock;
    self::$parent = new ParentReflectMock;
  }

  /**
   * Должен возвращать отражение вызываемого класса.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionClass
   */
  public function testShouldReturnReflectionClass(){
    $rc = self::$parent->getReflectionClass();
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionClass', $rc);
    $this->assertEquals('D\library\patterns\entity\reflection\test\ParentReflectMock', $rc->getName());
  }

  /**
   * Должен возвращать отражение родительского класса.
   * @covers D\library\patterns\entity\reflection\TReflect::getParentReflectionClass
   */
  public function testShouldReturnReflectionParentClass(){
    $rc = self::$child->getParentReflectionClass();
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionClass', $rc);
    $this->assertEquals('D\library\patterns\entity\reflection\test\ParentReflectMock', $rc->getName());
  }

  /**
   * Должен возвращать null, если вызываемый класс является вершиной наследования.
   * @covers D\library\patterns\entity\reflection\TReflect::getParentReflectionClass
   */
  public function testShouldReturnNullIfClassRoot(){
    $this->assertEquals(null, self::$parent->getParentReflectionClass());
  }
  
  /**
   * Должен возвращать отражение свойства класса.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionProperty
   */
  public function testShouldReturnReflectionProperty(){
    /**
     * @var \D\library\patterns\entity\reflection\ReflectionProperty $rp
     */
    $rp = self::$parent->getReflectionProperty('a');
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionProperty', $rp);
    $this->assertEquals('a', $rp->getName());
  }

  /**
   * Должен возвращать отражение свойства родительского класса, если целевое свойство относится к нему.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionProperty
   */
  public function testShouldReturnParentProperty(){
    /**
     * @var \D\library\patterns\entity\reflection\ReflectionProperty $rp
     */
    $rp = self::$child->getReflectionProperty('a');
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionProperty', $rp);
    $this->assertEquals('D\library\patterns\entity\reflection\test\ParentReflectMock', $rp->getDeclaringClass()->getName());
  }

  /**
   * Должен выбрасывать исключение, если запрашиваемое свойство отсутствует в классе и в его родителях.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionProperty
   */
  public function testShouldThrowExceptionIfPropertyNotExists(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\LackException');
    self::$child->getReflectionProperty('notProperty');
  }

  /**
   * Должен возвращать отражение метода класса.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionMethod
   */
  public function testShouldReturnReflectionMethod(){
    /**
     * @var \D\library\patterns\entity\reflection\ReflectionMethod $rm
     */
    $rm = self::$parent->getReflectionMethod('c');
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionMethod', $rm);
    $this->assertEquals('c', $rm->getName());
  }

  /**
   * Должен возвращать отражение метода родительского класса, если целевой метод относится к нему.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionMethod
   */
  public function testShouldReturnParentMethod(){
    /**
     * @var \D\library\patterns\entity\reflection\ReflectionMethod $rm
     */
    $rm = self::$child->getReflectionMethod('c');
    $this->assertInstanceOf('D\library\patterns\entity\reflection\ReflectionMethod', $rm);
    $this->assertEquals('D\library\patterns\entity\reflection\test\ParentReflectMock', $rm->getDeclaringClass()->getName());
  }

  /**
   * Должен выбрасывать исключение, если запрашиваемый метод отсутствует в классе и в его родителях.
   * @covers D\library\patterns\entity\reflection\TReflect::getReflectionMethod
   */
  public function testShouldThrowExceptionIfMethodNotExists(){
    $this->setExpectedException('\D\library\patterns\entity\exceptions\semanticExceptions\LackException');
    self::$child->getReflectionMethod('notMethod');
  }

  /**
   * Должен возвращать отражения всех свойств, в том числе родительского класса.
   * @covers D\library\patterns\entity\reflection\TReflect::getAllReflectionProperties
   */
  public function testShouldReturnAllReflectionProperties(){
    /**
     * @var \D\library\patterns\entity\reflection\ReflectionProperty[] $ps
     */
    $ps = self::$child->getAllReflectionProperties();
    $this->assertEquals('e', $ps['e']->getName());
    $this->assertInstanceOf('\D\library\patterns\entity\reflection\ReflectionProperty', $ps['e']);
    $this->assertInstanceOf('D\library\patterns\structure\metadata\Described', $ps['e']);
    $this->assertEquals('a', $ps['a']->getName());
  }

  /**
   * Должен возвращать отражения всех методов, в том числе родительского класса.
   * @covers D\library\patterns\entity\reflection\TReflect::getAllReflectionMethods
   */
  public function testShouldReturnAllReflectionMethods(){
    /**
     * @var \D\library\patterns\entity\reflection\ReflectionMethod[] $ms
     */
    $ms = self::$child->getAllReflectionMethods();
    $this->assertEquals('h', $ms['h']->getName());
    $this->assertInstanceOf('\D\library\patterns\entity\reflection\ReflectionMethod', $ms['h']);
    $this->assertInstanceOf('D\library\patterns\structure\metadata\Described', $ms['h']);
    $this->assertEquals('c', $ms['c']->getName());
  }
}
 
