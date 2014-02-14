<?php
namespace D\library\patterns\entity\reflection;

use D\library\patterns\entity\exceptions\dataExceptions\NotFoundDataException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;

/**
 * Данный trait реализует механизм разбора блока документации класса или его члена с целью выделения ассоциативного массива метаданных.
 * Trait определяет метаданные как компонент блока документации со следующей структурой: $<имяАннотации> <значениеАннотации>.
 * @author  Artur Sh. Mamedbekov
 */
trait TDocMetadata{
  /**
   * @var string[]|null Кэш метаданных.
   */
  private $metadata = null;

  /**
   * @prototype D\library\patterns\structure\metadata\Described
   */
  public function getAllMetadata(){
    if(is_null($this->metadata)){
      $docs = explode("\n", $this->getDocComment());
      $docs = array_splice($docs, 1, -1);
      $this->metadata = [];
      foreach($docs as $doc){
        $doc = substr(trim($doc), 2);
        if($doc[0] == '$'){
          $point = strpos($doc, ' ');
          if($point !== false){
            $this->metadata[substr($doc, 1, $point - 1)] =  substr($doc, $point + 1);
          }
          else{
            $this->metadata[substr($doc, 1)] =  '';
          }
        }
      }
    }

    return $this->metadata;
  }

  /**
   * @prototype D\library\patterns\structure\metadata\Described
   */
  public function getMetadata($name){
    InvalidArgumentException::verify($name, 's', [1]);
    $metadata = $this->getAllMetadata();
    if(!isset($metadata[$name])){
      throw new NotFoundDataException('Запрашиваемых метаданных ['.$name.'] не найдено.');
    }
    return $metadata[$name];
  }

  /**
   * @prototype D\library\patterns\structure\metadata\Described
   */
  public function hasMetadata($name){
    InvalidArgumentException::verify($name, 's', [1]);
    $metadata = $this->getAllMetadata();
    return isset($metadata[$name]);
  }
}