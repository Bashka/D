<?php
namespace D\library\patterns\entity\io;

/**
 * Интерфейс определяет поток, содержание которого упорядочено.
 * Такой поток может смещать указатель текущего считываемого или записываемого байта, что позволяет получать данные от ресурса не последовательно.
 * Отсчет байтов в таком потоке начинается с нуля.
 * @author  Artur Sh. Mamedbekov
 */
interface SeekIO{
  /**
   * Метод устанавливает указатель текущего байта.
   * @param integer $position Позиция указателя текущего байта.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае ошибки при работе с потоком.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или пустой строки.
   */
  public function setPosition($position);

  /**
   * Метод возвращает позицию указателя текущего байта.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае ошибки при работе с потоком.
   * @return integer Текущая позиция указателя.
   */
  public function getPosition();
}
