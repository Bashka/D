<?php
namespace D\library\patterns\entity\io;

/**
 * Интерфейс определяет входной поток.
 * Данные из входного потока считываются побайтно.
 * @author  Artur Sh. Mamedbekov
 */
interface Reader{
  /**
   * Метод считывает один байт из потока.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае возникновения ошибки при чтении из потока.
   * @return string Возвращает текущий байт из потока или пустую строку если поток закончет.
   */
  public function read();

  /**
   * Метод считывает указанное количество байт из потока.
   * @param integer $length Количество считываемых байт.
   * @throws \D\library\patterns\entity\io\IOException  Выбрасывается в случае возникновения ошибки при чтении из потока.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   * @return string Если размер входного потока больше или равен размеру считываемой строки, то считывается строка указанного размера, если меньше, то оставшаяся в потоке строка, если в потоке нет данных возвращается пустая строка.
   */
  public function readString($length);

  /**
   * Метод считывает строку от текущей позиции до символа конца строки.
   * @param string $EOLSymbol Символ, принимаемый за EOL.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае возникновения ошибки при чтении из потока.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или пустой строки.
   * @return string Прочитанная строка или пустая строка, если достигнут конец потока или если текущим символом является EOL.
   */
  public function readLine($EOLSymbol = "\r\n");

  /**
   * Метод считывает все содержимое потока.
   * @throws \D\library\patterns\entity\io\IOException Выбрасывается в случае возникновения ошибки при чтении из потока.
   * @return string Символы, содержащиеся в потоке или пустая строка, если достигнут конец потока.
   */
  public function readAll();
}
