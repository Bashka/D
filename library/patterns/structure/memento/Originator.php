<?php
namespace D\library\patterns\structure\memento;

/**
 * Определяет классы, способные сохранять и востанавливать текущее состояние с использованием хранителей.
 * Именно экземпляры классов, реализующих данный интерфейс, называются родителями хранителей.
 * @author Artur Sh. Mamedbekov
 */
interface Originator{
  /**
   * Создает хранителя со своим текущим состоянием и возвращает его.
   * @return \D\library\patterns\structure\memento\Memento Хранитель текущего состояния вызываемого объекта.
   */
  public function createMemento();

  /**
   * Восстанавливает состояние вызываемого объекта из переданного хранителя.
   * @param \D\library\patterns\structure\memento\Memento $memento Хранитель, являющийся основой для восстановления.
   * @throws \D\library\patterns\structure\memento\AccessException Выбрасывается в случае, если вызываемый объект пытается получить доступ к чужому хранителю.
   */
  public function restoreFromMemento(Memento $memento);
}
