<?php
namespace D\services\log;

use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\structure\singleton\Singleton;
use D\library\patterns\structure\singleton\TSingleton;
use D\library\resources\fileSystem\components\File;
use D\services\config\SystemConf;

/**
 * Служба, отвечающая за журналирование событий.
 * @author Artur Sh. Mamedbekov
 */
class Log implements Singleton{
use TSingleton;

  /**
   * Журналирование на уровне ошибок.
   */
  const ERROR = 1;

  /**
   * Журналирование на уровне предупреждений.
   */
  const WARNING = 2;

  /**
   * Журналирование на уровне сообщений.
   */
  const NOTICE = 3;

  /**
   * Метод добавляет сообщение в журнал.
   * @param integer $type Тип сообщения.
   * @param string $context Добавляемое сообщение.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   */
  private function add($type, $context){
    InvalidArgumentException::verify($context, 's', [1]);
    /**
     * @var SystemConf $conf
     */
    $conf = SystemConf::getInstance();
    if($type <= ((integer) $conf->get('Log', 'type'))){
      $now = new \DateTime('now', new \DateTimeZone($conf->get('System', 'timeZone')));
      $logFileName = $now->format('Y-M-d').'.log';
      $logFile = new File(__DIR__.'/'.$logFileName);
      if(!$logFile->isExists()){
        $logFile->create();
      }
      $logFile = $logFile->getWriter();
      if($type == 1){
        $type = 'ERROR';
      }
      elseif($type == 2){
        $type = 'WARNING';
      }
      elseif($type == 3){
        $type = 'NOTICE';
      }
      $logFile->write($type.' ['.$now->format('s:i:H').']: '.$context."\r\n");
      $logFile->close();
    }
  }

  /**
   * Метод добавляет ошибку в журнал.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @param string $context Добавляемое сообщение.
   */
  public function addError($context){
    $this->add(self::ERROR, $context);
  }

  /**
   * Метод добавляет предупреждение в журнал.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @param string $context Добавляемое сообщение.
   */
  public function addWarning($context){
    $this->add(self::WARNING, $context);
  }

  /**
   * Метод добавляет сообщение в журнал.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается в случае получения параметра недопустимого типа.
   * @param string $context Добавляемое сообщение.
   */
  public function addNotice($context){
    $this->add(self::NOTICE, $context);
  }
} 