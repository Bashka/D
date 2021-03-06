<?php
namespace D\library\patterns\entity\io;

/**
 * Интерфейс определяет поток, который может быть закрыт.
 * Закрытым потоком невозможно воспользоваться вновь.
 * Как правило закрытие потока сопровождается освобождением ресурсов системы, потому важно закрывать потоки, которые уже не будут использоваться.
 * При завершении сценария некоторые потоки могут быть закрыты автоматически.
 * @author  Artur Sh. Mamedbekov
 */
interface Closed{
  /**
   * Метод закрывает поток.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае невозможности закрытия потока.
   */
  public function close();

  /**
   * Метод определяет, закрыт ли поток.
   * @return boolean true - если поток закрыт, иначе - false.
   */
  public function isClose();
}
