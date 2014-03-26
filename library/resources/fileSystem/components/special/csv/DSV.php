<?php
namespace D\library\resources\fileSystem\components\special\csv;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\conversion\Interpreter;
use D\library\patterns\structure\observer\TSubject;
use D\library\resources\fileSystem\components\File;

/**
 * Файл данных в формате DSV.
 * @author  Artur Sh. Mamedbekov
 */
class DSV extends File implements \SplSubject, Interpreter{
  use TSubject;

  /**
   * @var string[][] Массив записей файла.
   */
  private $data;

  /**
   * @var string[] Текущая обрабатываемая запись.
   */
  private $currentRow;

  /**
   * @var integer Текущий индекс записи.
   */
  private $index = 0;

  /**
   * Метод загружает данные из DSV файла.
   * В ходе работы метод информирует зарегистрированных слушателей при загружке каждой следующей записи.
   * @param string $separator [optional] Разделитель полей.
   */
  public function load($separator = ';'){
    $reader = $this->getReader();
    while(($row = $reader->readLine()) != ''){
      $this->currentRow = explode($separator, $row);
      $this->notify();
      $this->data[$this->index] = $this->currentRow;
      $this->index++;
    }
    $reader->close();
  }

  /**
   * Метод возвращает загруженное количесво записей.
   * @return integer Количество записей.
   */
  public function count(){
    return count($this->data);
  }

  /**
   * Метод возвращает текущую обрабатываемую запись.
   * @return string[] Текущая обрабатываемая запись.
   */
  public function getCurrentRow(){
    return $this->currentRow;
  }

  /**
   * Метод возвращает индекс текущей записи.
   * @return integer Индекс текущей записи.
   */
  public function getIndex(){
    return $this->index;
  }

  /**
   * Метод возвращает запись по ее индексу.
   * @param integer $index Индекс запрашиваемой записи.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @return null|string[] Запрашиваемая запись или null - если запись с таким индексом отсутствует.
   */
  public function getRow($index){
    InvalidArgumentException::verify($index, 'i', [0]);
    if(isset($this->data[$index])){
      return $this->data[$index];
    }
    return null;
  }

  /**
   * Метод добавляет запись.
   * @param integer $index Индекс, под которым будет добавляться запись.
   * @param string[] $row Добавляемая запись.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function setRow($index, $row){
    InvalidArgumentException::verify($index, 'i', [0]);
    $this->data[$index] = $row;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    $result = '';
    foreach($this->data as $row){
      $result .= implode(';', $row)."\r\n";
    }
    return substr($result, 0, -2);
  }

  /**
   * Метод записывает DSV данные в файл.
   */
  public function rewrite(){
    $writer = $this->getWriter();
    $writer->clean();
    $writer->write($this->interpretation());
    $writer->close();
  }
}