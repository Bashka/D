/**
 * @namespace DJS\classes\Controller
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.classes.Controller', function(Y){
  /**
   * Данный класс представляет контроллер экрана и позволяет инициализировать экран и его компоненты.
   * Класс должен быть расширен конкретными контроллерами экранов с добавлением метода initScreen, который должен инициализировать экран, и метода syncScreen, который должен синхронзировать отображение экрана с его состоянием.
   * @param {Object} cnf Параметры класса.
   * @constructor
   * @this {DJS.classes.Controller}
   */
  function Controller(cnf){
    Controller.superclass.constructor.apply(this, arguments);

    if(cnf === undefined || cnf.screen === undefined){
      throw new Error('Невозможно инициализировать объект [DJS.classes.Controller]. Отсутствует обязательный параметр конфигурации [screen].');
    }
  }

  Controller.NAME = 'controller';
  Controller.ATTRS = {
    /**
     * Ссылка на Screen.
     * @public
     * @type {DJS.widgets.Screen}
     */
    screen: {
      writeOnce: 'initOnly',
      validator: function(val){
        return val instanceof Y.DJS.widgets.Screen;
      }
    }
  };

  Y.extend(Controller, Y.Base, {
    /**
     * Метод возвращает массив внедренных узлов экрана.
     * @private
     * @returns {Node[]} Массив внедреных узлов экрана, имеющий следующую структуру: [ключУзла => узел, ...]
     */
    _getInject: function(){
      return this.get('screen').get('frame').widget.get('inject');
    },

    /**
     * Метод отправляет запрос контроллеру родительского модуля экрана.
     * @private
     * @param {string} active Целевой метод.
     * @param {string[]} params Массив параметров.
     * @param {function} success Функция обратного вызова, отвечающая за обработку ответа сервера.
     * @param {function} [failure] Функция обратного вызова, отвечающая за обработку ошибки уровня сервера.
     * @param {function} [options] Конфигурация запроса.
     */
    _request: function(active, params, success, failure, options){
      options = options || {};
      options.params = params;
      options.success = success;
      options.failure = failure;
      options.context = this;

      Y.DJS.services.Query.request(this.get('screen').get('module'), active, options);
    },

    initScreen: function(){
    },

    syncScreen: function(){
    }
  });

  Y.namespace('DJS.classes').Controller = Controller;
}, '1.0', {requires: ['base', 'DJS.widgets.Screen', 'DJS.services.Query']});