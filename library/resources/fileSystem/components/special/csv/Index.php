<?php
namespace D\library\resources\fileSystem\components\special\csv;

use SplSubject;

/**
 * Индекс записей. Объекты данного класса должны быть подписаны на объект DSV перед выполнением метода load, что позволит индексировать все загружаемые записи.
 * Класс позволяет формировать индекс по группе полей.
 * @author  Artur Sh. Mamedbekov
 */
class Index implements \SplObserver{
  /**
   * @var integer[][] Ассоциативный двумерный индекс. В качестве ключей массива выступают значения индексируемого поля, а в качестве значений множество индексов записей, в которых присутствует это значение.
   */
  private $index;

  /**
   * @var integer[] Номера индексируемых полей.
   */
  private $indexedFields;

  /**
   * @param integer $indexedField Номер индексируемого поля.
   * @param integer ... Номера индексируемых полей.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function __construct($indexedField){
    $this->indexedFields = func_get_args();
  }

  /**
   * @param \D\library\resources\fileSystem\components\special\csv\DSV $subject Обрабатываемый CVS.
   * @prototype \SplObserver
   */
  public function update(SplSubject $subject){
    $row = $subject->getCurrentRow();
    $value = '';
    foreach($this->indexedFields as $field){
      $value .= $row[$field].',';
    }
    $value = substr($value, 0, -1);
    if(!isset($this->index[$value])){
      $this->index[$value] = [$subject->getIndex()];
    }
    else{
      $this->index[$value][] = $subject->getIndex();
    }
  }

  /**
   * Метод возвращает индексы записей, в индексируемом поле которых установлено целевое значение.
   * @param string $value Целевое значение индексируемого поля.
   * @param string ... Целевые значение индексируемых полей.
   * @return integer[] Индексы записей с данным значением.
   */
  public function getIndex($value){
    $value = func_get_args();
    $value = implode(',', $value);
    if(isset($this->index[$value])){
      return $this->index[$value];
    }
    return [];
  }
}