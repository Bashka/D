<?php
namespace D\library\patterns\entity\io;

/**
 * Интерфейс определяет выходной поток.
 * Данные в выходной поток записываются побайтово и пакетно.
 * @author  Artur Sh. Mamedbekov
 */
interface Writer{
  /**
   * Метод записывает байт или строку в поток.
   * @param string $data Записываемый байт (строка).
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае возникновения ошибки при записи в поток.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или пустой строки.
   * @return integer Число реально записанных байт.
   */
  public function write($data);
}
