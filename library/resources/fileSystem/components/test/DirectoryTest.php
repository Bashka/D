<?php
namespace D\library\resources\fileSystem\components\test;

use D\library\resources\fileSystem\components\Directory;

require_once substr(__DIR__, 0, strpos(__DIR__, 'D') - 1) . '/D/starter.php';
class DirectoryTest extends \PHPUnit_Framework_TestCase {
  /**
   * Должен выбрасывать исключение, если переименование компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\Directory::rename
   */
  public function testShouldThrowExceptionIfDuplication(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $d = new Directory('dir');
    $d->rename('assistantDir');
  }

  /**
   * Должен выбрасывать исключение, если перемещение компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\Directory::move
   */
  public function testShouldThrowExceptionIfDuplication2(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $d = new Directory('dir');
    $d->move('assistantDir');
  }

  /**
   * Должен исключать перемещение каталога в себя.
   * @covers D\library\resources\fileSystem\components\Directory::move
   */
  public function testShouldExcludeRecursiveMove(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $d = new Directory('dir');
    $d->move('dir');
  }

  /**
   * Должен возвращать true - если вызываемый файл существует, иначе - false.
   * @covers D\library\resources\fileSystem\components\Directory::isExists
   */
  public function testShouldReturnTrueIfFileExists(){
    $d = new Directory('dir');
    $this->assertTrue($d->isExists());
    $d = new Directory('notExistsDir');
    $this->assertFalse($d->isExists());
  }

  /**
   * Должен создавать каталог в файловой системе.
   * @covers D\library\resources\fileSystem\components\Directory::create
   */
  public function testShouldCreate(){
    $d = new Directory('notExistsDir');
    $d->create();
    $this->assertTrue(file_exists('notExistsDir'));
    rmdir('notExistsDir');
  }

  /**
   * Должен выбрасывать исключение, если создание компонента приведет к дублированию.
   * @covers D\library\resources\fileSystem\components\Directory::create
   */
  public function testShouldThrowExceptionIfDuplication3(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $d = new Directory('dir');
    $d->create();
  }

  /**
   * Должен возвращать файловый итератор.
   * @covers D\library\resources\fileSystem\components\Directory::getDirectoryIterator
   */
  public function testShouldReturnFileIterator(){
    $d = new Directory('dir');
    $this->assertInstanceOf('DirectoryIterator', $d->getDirectoryIterator());
  }

  /**
   * Должен возвращать true если запрашиваемый файл существует в вызываемом каталоге, иначе - false.
   * @covers D\library\resources\fileSystem\components\Directory::hasFile
   */
  public function testShouldSeekFile(){
    $d = new Directory('assistantDir');
    $this->assertTrue($d->hasFile('file'));
    $this->assertFalse($d->hasFile('notExistsFile'));
  }

  /**
   * Должен выбрасывать исключение, если вызываемого каталога не существует.
   * @covers D\library\resources\fileSystem\components\Directory::hasFile
   */
  public function testShouldThrowExceptionIfDirNotExists(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $d = new Directory('notExistsDir');
    $d->hasFile('file');
  }

  /**
   * Должен возвращать true если запрашиваемый каталог существует в вызываемом каталоге, иначе - false.
   * @covers D\library\resources\fileSystem\components\Directory::hasDir
   */
  public function testShouldSeekDir(){
    $d = new Directory('assistantDir');
    $this->assertTrue($d->hasDir('dir'));
    $this->assertFalse($d->hasDir('notExistsDir'));
  }

  /**
   * Должен выбрасывать исключение, если вызываемого каталога не существует.
   * @covers D\library\resources\fileSystem\components\Directory::hasDir
   */
  public function testShouldThrowExceptionIfDirNotExists2(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $d = new Directory('notExistsDir');
    $d->hasDir('file');
  }

  /**
   * Должен возвращать представление файла в данном каталоге.
   * @covers D\library\resources\fileSystem\components\Directory::getFile
   */
  public function testShouldReturnFile(){
    $d = new Directory('assistantDir');
    $this->assertInstanceOf('D\library\resources\fileSystem\components\File', $d->getFile('file'));
    $this->assertInstanceOf('D\library\resources\fileSystem\components\File', $d->getFile('notExistsFile'));
  }

  /**
   * Должен возвращать представление каталога в данном каталоге.
   * @covers D\library\resources\fileSystem\components\Directory::getDir
   */
  public function testShouldReturnDir(){
    $d = new Directory('assistantDir');
    $this->assertInstanceOf('D\library\resources\fileSystem\components\Directory', $d->getDir('dir'));
    $this->assertInstanceOf('D\library\resources\fileSystem\components\Directory', $d->getDir('notExistsDir'));
  }

  /**
   * Должен удалять каталог и все вложенные компоненты.
   * @covers D\library\resources\fileSystem\components\Directory::delete
   */
  public function testShouldRecursivelyRemoveDir(){
    $d = new Directory('assistantDir');
    $this->assertTrue($d->delete());
    $this->assertTrue((!file_exists('assistantDir')));

    mkdir('assistantDir');
    mkdir('assistantDir/dir');
    fclose(fopen('assistantDir/file', 'a+'));
  }

  /**
   * Должен выбрасывать исключение если вызываемого каталога нет в файловой системе.
   * @covers D\library\resources\fileSystem\components\Directory::delete
   */
  public function testShouldThrowExceptionIfDirNotExists3(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $dir = new Directory('notExistsDir');
    $dir->delete();
  }

  /**
   * Должен удалять все компоненты в вызываемом каталоге.
   * @covers D\library\resources\fileSystem\components\Directory::clear
   */
  public function testShouldRemoveAllChild(){
    $d = new Directory('assistantDir');
    $d->clear();
    $this->assertTrue((!file_exists('assistantDir/dir') && !file_exists('assistantDir/file')));

    mkdir('assistantDir/dir');
    fclose(fopen('assistantDir/file', 'a+'));
  }

  /**
   * Должен выбрасывать исключение если вызываемого каталога нет в файловой системе.
   * @covers D\library\resources\fileSystem\components\Directory::clear
   */
  public function testShouldThrowExceptionIfDirNotExists4(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $d = new Directory('notExistsDir');
    $d->clear();
  }

  /**
   * Должен возвращать суммарный размер всех файлов в данном каталоге.
   * @covers D\library\resources\fileSystem\components\Directory::getSize
   */
  public function testShouldReturnAllSize(){
    $f = fopen('assistantDir/file', 'a');
    fwrite($f, 'Data');
    fclose($f);
    $d = new Directory('assistantDir');
    $this->assertEquals(4, $d->getSize());
    fclose(fopen('assistantDir/file', 'w+'));
  }

  /**
   * Должен возвращать 0 если в каталоге нет ни одного файла.
   * @covers D\library\resources\fileSystem\components\Directory::getSize
   */
  public function testShouldReturnZeroIfDirEmpty(){
    $d = new Directory('assistantDir');
    $this->assertEquals(0, $d->getSize());
  }

  /**
   * Должен выполнять копирование каталога со всеми вложенными файлами.
   * @covers D\library\resources\fileSystem\components\Directory::copyPaste
   */
  public function testShouldCopyDirAndAllChildComponents(){
    $assDir = new Directory('assistantDir');
    $assDir->copyPaste('dir');
    $this->assertTrue((file_exists('dir/assistantDir') && file_exists('dir/assistantDir/dir') && file_exists('dir/assistantDir/file')));
    unlink('dir/assistantDir/file');
    rmdir('dir/assistantDir/dir');
    rmdir('dir/assistantDir');
  }

  /**
   * Должен возвращать представление созданной копии.
   * @covers D\library\resources\fileSystem\components\Directory::copyPaste
   */
  public function testShouldReturnCopy(){
    $assDir = new Directory('assistantDir');
    $copy = $assDir->copyPaste('dir');
    $this->assertInstanceOf('D\library\resources\fileSystem\components\Directory', $copy);
    unlink('dir/assistantDir/file');
    rmdir('dir/assistantDir/dir');
    rmdir('dir/assistantDir');
  }

  /**
   * Должен выбрасывать исключение если вызываемого каталога нет в файловой системе.
   * @covers D\library\resources\fileSystem\components\Directory::copyPaste
   */
  public function testShouldThrowExceptionIfDirNotExists5(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException');
    $dir = new Directory('notExistsDir');
    $dir->copyPaste('dir');
  }

  /**
   * Должен предотвращать дублирование.
   * @covers D\library\resources\fileSystem\components\Directory::copyPaste
   */
  public function testShouldPreventDuplicationOfCopy(){
    $this->setExpectedException('D\library\patterns\entity\exceptions\environmentExceptions\DuplicationException');
    $d = new Directory('dir');
    $d->copyPaste('assistantDir');
  }
}
