<?php
namespace D\library\patterns\entity\SQL\operators\DML\components;

use D\library\patterns\entity\exceptions\semanticExceptions\InheritanceException;
use D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException;
use D\library\patterns\entity\SQL\operators\ComponentQuery;

/**
 * Класс-оболочка для добавления алиаса компоненту.
 * @author Artur Sh. Mamedbekov
 */
abstract class Alias extends ComponentQuery{
  /**
   * @var string Псевдоним компонента.
   */
  protected $alias;

  /**
   * @var \D\library\patterns\entity\SQL\operators\ComponentQuery Компонент, к которому устанавливается псевдоним.
   */
  protected $component;

  /**
   * Данный метод должен быть переопределен в дочерних классах и восстанавливать компонент.
   * @param string $string Исходная строка компонента.
   * @param mixed $driver [optional] Данные, позволяющие изменить логику интерпретации исходной строки.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InheritanceException Исключение свидетельствует о том, что дочерний класс не переопределил данный метод.
   * @return \D\library\patterns\entity\SQL\operators\ComponentQuery Восстановленный компонент.
   */
  protected static function reestablishComponent($string, $driver = null){
    throw new InheritanceException('Дочерний класс ['.get_called_class().'] не переопределил обязательный метод reestablishChild.');
  }

  /**
   * @prototype D\library\patterns\structure\conversion\TRestorable
   */
  public static function getPatterns($driver = null){
    return ['aliasValue' => '[A-Za-z_][A-Za-z0-9_]*'];
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Restorable
   */
  public static function reestablish($string, $driver = null){
    // Контроль типа и верификация выполняется в вызываемом родительском методе.
    parent::reestablish($string);
    /**
     * @var string[] $components
     */
    $components = explode(' AS ', $string);

    return new static(static::reestablishComponent($components[0]), $components[1]);
  }

  /**
   * @param \D\library\patterns\entity\SQL\operators\ComponentQuery $component Компонент, к которому устанавливается псевдоним. Выбор конкретного типа компонента зависит от реализации.
   * @param string $alias Псевдоним компонента.
   * @throws \D\library\patterns\entity\exceptions\semanticExceptions\InvalidArgumentException Выбрасывается при передаче параметра недопустимого типа или значения.
   */
  function __construct(ComponentQuery $component, $alias){
    InvalidArgumentException::verify($alias, 's', [1]);
    $this->alias = $alias;
    $this->component = $component;
  }

  /**
   * @prototype D\library\patterns\structure\conversion\Interpreter
   */
  public function interpretation($driver = null){
    InvalidArgumentException::verify($driver, 'ns', [1]);

    return $this->component->interpretation($driver) . ' AS ' . $this->alias;
  }

  /**
   * Метод возвращает псевдоним компонента.
   * @return string Псевдоним компонента.
   */
  public function getAlias(){
    return $this->alias;
  }

  /**
   * Метод возвращает компонент.
   * @return \D\library\patterns\entity\SQL\operators\ComponentQuery Компонент.
   */
  public function getComponent(){
    return $this->component;
  }
}
