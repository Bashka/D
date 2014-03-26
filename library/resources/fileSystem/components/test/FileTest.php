<?php
namespace D\library\resources\fileSystem\components\test;

use D\library\resources\fileSystem\components\File;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class FileTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен выбрасывать исключение, если переименование компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\File::rename
   */
  public function testShouldThrowExceptionIfDuplication(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $f = new File('file');
    $f->rename('assistantFile');
  }

  /**
   * Должен выбрасывать исключение, если перемещение компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\File::move
   */
  public function testShouldThrowExceptionIfDuplication2(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $f = new File('file');
    $f->move('assistantDir');
  }

  /**
   * Должен выполнять компирование.
   * @covers D\library\resources\fileSystem\components\File::copyPaste
   */
  public function testShouldCopyPaste(){
    $f = new File('file');
    $f->copyPaste('dir');
    $this->assertTrue(file_exists('dir/file') && file_exists('file'));
    unlink('dir/file');
  }

  /**
   * Должен выбрасывать исключение, если копирование компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\File::copyPaste
   */
  public function testShouldThrowExceptionIfDuplication3(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $f = new File('file');
    $f->copyPaste('assistantDir');
  }

  /**
   * Должен выбрасывать исключение, если компонента нет в файловой системы.
   * @covers D\library\resources\fileSystem\components\File::copyPaste
   */
  public function testShouldThrowExceptionIfComponentNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $f = new File('notExistsFile');
    $f->copyPaste('dir');
  }

  /**
   * Должен возвращать размер файла в байтах.
   * @covers D\library\resources\fileSystem\components\File::getSize
   */
  public function testShouldReturnFileSize(){
    $f = new File('file');
    $this->assertEquals(4, $f->getSize());
  }

  /**
   * Должен выбрасывать исключение, если компонента нет в файловой системы.
   * @covers D\library\resources\fileSystem\components\File::getSize
   */
  public function testShouldThrowExceptionIfComponentNotExists2(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $f = new File('notExistsFile');
    $f->getSize();
  }

  /**
   * Должен возвращать true - если вызываемый файл существует, иначе - false.
   * @covers D\library\resources\fileSystem\components\File::isExists
   */
  public function testShouldReturnTrueIfFileExists(){
    $f = new File('file');
    $this->assertTrue($f->isExists());
    $f = new File('notExistsFile');
    $this->assertFalse($f->isExists());
  }

  /**
   * Должен удалять вызываемый файл.
   * @covers D\library\resources\fileSystem\components\File::delete
   */
  public function testShouldDelete(){
    $f = new File('file');
    $f->delete();
    $this->assertTrue(!file_exists('file'));
    fclose(fopen('file', 'a+'));
    file_put_contents('file', 'Data');
  }

  /**
   * Должен выбрасывать исключение, если компонента нет в файловой системы.
   * @covers D\library\resources\fileSystem\components\File::delete
   */
  public function testShouldThrowExceptionIfComponentNotExists3(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $f = new File('notExistsFile');
    $f->delete();
  }

  /**
   * Должен создавать файл в файловой системе.
   * @covers D\library\resources\fileSystem\components\File::create
   */
  public function testShouldCreate(){
    $f = new File('notExistsFile');
    $f->create();
    $this->assertTrue(file_exists('notExistsFile'));
    unlink('notExistsFile');
  }

  /**
   * Должен выбрасывать исключение, если создание компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\File::create
   */
  public function testShouldThrowExceptionIfDuplication4(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $f = new File('file');
    $f->create();
  }

  /**
   * Должен возвращать файловый поток ввода.
   * @covers D\library\resources\fileSystem\components\File::getReader
   */
  public function testShouldReturnFileInStream(){
    $f = new File('file');
    $r = $f->getReader();
    $this->assertInstanceOf('D\library\resources\fileSystem\io\BlockingFileReader', $r);
    $r->close();
  }

  /**
   * Должен возвращать файловый поток вывода.
   * @covers D\library\resources\fileSystem\components\File::getWriter
   */
  public function testShouldReturnFileOutStream(){
    $f = new File('file');
    $r = $f->getWriter();
    $this->assertInstanceOf('D\library\resources\fileSystem\io\BlockingFileWriter', $r);
    $r->close();
  }
}
