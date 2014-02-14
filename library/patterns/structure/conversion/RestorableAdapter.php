<?php
namespace D\library\patterns\structure\conversion;

/**
 * Данный класс может быть использован как родительский для классов, которые реализуют интерфейс Restorable через trait TRestorable без дополнительной логики.
 * @author Artur Sh. Mamedbekov
 */
abstract class RestorableAdapter implements Restorable{
  use TRestorable;
}