<?php
namespace D\model\modules\SystemPackages;

use D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\resources\fileSystem\components\special\ini\INI;

/**
 * Объекты данного класса представляют отражения компонентов, установленных в системе.
 * @author Artur Sh. Mamedbekov
 */
abstract class ReflectionUtility{
  /**
   * Имя файла состояния компонента.
   */
  const STATE_FILE_NAME = 'state.ini';

  /**
   * @var \D\library\resources\fileSystem\components\special\ini\INI Файл состояния компонента.
   */
  protected $state;

  /**
   * @var string Имя компонента.
   */
  protected $name;

  /**
   * Метод возвращает адрес каталога компонента относительно корня системы.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если требуемый компонент не найден.
   * @return string Адрес каталога компонента.
   */
  public abstract function getLocationAddress();

  /**
   * @param string $name Имя компонента.
   * @throws \D\library\patterns\entity\exceptions\environmentExceptions\NotExistsException Выбрасывается в случае, если требуемый компонент не найден.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  public function __construct($name){
    InvalidArgumentException::verify($name, 's', [1]);
    $this->name = $name;
    try{
      $this->state = new INI($_SERVER['DOCUMENT_ROOT'].$this->getLocationAddress() . '/' . self::STATE_FILE_NAME);
    }
    catch(NotExistsException $e){
      throw new NotExistsException('Требуемый компонент ['.$name.'] не найден.', 1, $e);
    }
  }

  /**
   * Метод возвращает имя компонента.
   * @return string Имя компонента.
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Метод возвращает файл состояния компонента.
   * @return \D\library\resources\fileSystem\components\special\ini\INI Файл состояния компонента.
   */
  public function getStateFile(){
    return $this->state;
  }
}