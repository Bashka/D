<?php
namespace D\library\patterns\entity\exceptions\semanticExceptions\test;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

require_once substr(__DIR__, 0, strpos(__DIR__, '/D')) . '/D/starter.php';
class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase{
  /**
   * Допустимыми типами являются только n|s|i|f|b|a|o|S|I|F|B|A|O.
   * @covers D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException::verify
   */
  public function testShouldThrowExceptionIfTypeArgNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    InvalidArgumentException::verify(1, 'q');
  }

  /**
   * Должен ничего не делать если верификация успешна.
   * @covers D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException::verify
   */
  public function testShouldSilentIfGoodData(){
    InvalidArgumentException::verify(null, 'n');
    InvalidArgumentException::verify(1, 'i');
    InvalidArgumentException::verify(1.0, 'f');
    InvalidArgumentException::verify('', 's');
    InvalidArgumentException::verify(true, 'b');
    InvalidArgumentException::verify([], 'a');
    InvalidArgumentException::verify(new \stdClass(), 'o');
    InvalidArgumentException::verify([1], 'I');
    InvalidArgumentException::verify([1.0], 'F');
    InvalidArgumentException::verify([''], 'S');
    InvalidArgumentException::verify([true], 'B');
    InvalidArgumentException::verify([[]], 'A');
    InvalidArgumentException::verify([new \stdClass()], 'O');
    $r = fopen('.', 'r');
    InvalidArgumentException::verify([$r], 'R');
    fclose($r);
    InvalidArgumentException::verify(null, 'ni');
    InvalidArgumentException::verify(1, 'ni');
    InvalidArgumentException::verify(null, 'nia');
    InvalidArgumentException::verify(1, 'nia');
    InvalidArgumentException::verify([], 'nia');
    $r = fopen('.', 'r');
    InvalidArgumentException::verify($r, 'nr');
    fclose($r);
  }

  /**
   * Должен выбрасывать исключение если верификация не пройдена.
   * @covers D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException::verify
   */
  public function testShouldThrowExceptionIfBadData(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    InvalidArgumentException::verify(1, 'n');
  }

  /**
   * Должен ничего не делать если верификация успешна с учетом длины.
   * @covers D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException::verify
   */
  public function testShouldSilentIfGoodLength(){
    InvalidArgumentException::verify(1, 'i', [0, 1]);
    InvalidArgumentException::verify(1.0, 'f', [0, 1]);
    InvalidArgumentException::verify('a', 's', [1]);
    InvalidArgumentException::verify([], 'a', [0, 1]);
    InvalidArgumentException::verify([1], 'I', [1]);
    InvalidArgumentException::verify([1.0], 'F', [1]);
    InvalidArgumentException::verify([''], 'S', [1]);
    InvalidArgumentException::verify([true], 'B', [1]);
    InvalidArgumentException::verify([[]], 'A', [1]);
    InvalidArgumentException::verify([new \stdClass()], 'O', [1]);
    InvalidArgumentException::verify(null, 'ni', [1, 10]);
    InvalidArgumentException::verify(1, 'ni', [1, 10]);
    InvalidArgumentException::verify('a', 'nis', [1]);
  }

  /**
   * Должен выбрасывать исключение если верификация не пройдена с учетом длины.
   * @covers D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException::verify
   */
  public function testShouldThrowExceptionIfBadLength(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException');
    InvalidArgumentException::verify('', 'ns', [1]);
  }
}
 