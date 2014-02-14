/**
 * @namespace DJS\widgets\screens\Console\browse
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.screens.Console.browse.ConsoleOut', function(Y){
  /**
   * Виджет реализует панель вывода журнала взаимодействия с сервером.
   * Виджет генерирует следующие события:
   * - selectRequest - пользователь выбрал команду из журнала.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.screens.Console.browse.ConsoleOut}
   */
  function ConsoleOut(cnf){
    ConsoleOut.superclass.constructor.apply(this, arguments);
  }

  ConsoleOut.NAME = 'consoleOut';

  Y.extend(ConsoleOut, Y.Widget, {
    bindUI: function(){
      // Выбор команды.
      this.get('contentBox').delegate('click', function(e){
        var module = e.currentTarget.getAttribute('dModule'),
          active = e.currentTarget.getAttribute('dActive'),
          params = [];
        if(e.currentTarget.hasAttribute('dParams')){
          params = e.currentTarget.getAttribute('dParams');
          if(params != ''){
            params = params.split(',');
          }
        }
        this.fire('selectRequest', {module: module, action: active, params: params});
      }, '.DJS-widgets-screens-Console-browse-request', this);
    },

    /**
     * Метод добавляет информацию о запросе в панель.
     * @public
     * @param {string} module Имя целевого модуля.
     * @param {string} active Вызываемый метод.
     * @param {Array} params Массив параметров, передаваемых методу.
     */
    addRequest: function(module, active, params){
      var request = Y.Node.create('<div class="DJS-widgets-screens-Console-browse-request" dModule="'+module+'" dActive="'+active+'" dParams="'+params.join(',')+'">'+'> '+module+'::'+active+'('+params.join(', ')+')</div>');
      // Добавление запроса в начало панели для исключения необходимости горизонтальной прокрутки.
      var firstChild = this.get('contentBox').one('*');
      if(!firstChild){
        this.get('contentBox').append(request);
      }
      else{
        firstChild.insert(request, 'before');
      }
    },

    /**
     * Метод добавляет информацию об ответе в панель.
     * @param {string} responce Текст ответа.
     */
    addResponse: function(responce){
      var response = Y.Node.create('<div>'+'< '+responce+'</div>');
      // Добавление запроса в начало панели для исключения вертикальной прокрутки.
      var firstChild = this.get('contentBox').one('*');
      if(!firstChild){
        this.get('contentBox').append(response);
      }
      else{
        firstChild.insert(response, 'before');
      }
    }
  });

  Y.namespace('DJS.widgets.screens.Console.browse').ConsoleOut = ConsoleOut;
}, '2.0', {requires: ['widget', 'node', 'json-stringify']});