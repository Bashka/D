/**
 * @namespace D\services\Query
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.services.Query', function(Y){
  /**
   * Данная служба позволяет передавать сообщения серверу и принимать ответы от него.
   */
  var Query = (function(){
    /**
     * Физический адрес центрального контроллера.
     * @private
     * @static
     * @type {string}
     */
    var CENTRAL_CONTROLLER = '/D/model/CentralController.php',
      /**
       * Ожидание ответа в миллисекундах.
       * @private
       * @static
       * @type {int}
       */
        TIMEOUT = 5000;

    /**
     * Функция-обертка для обработчика события завершения запроса.
     * @private
     * @function
     * @param {String} code Код ответа сервера.
     * @param {XMLHttpRequest} xhr Объект запроса.
     * @param {Object} callbacks Пользовательские функции-обработчики событий ответа сервера.
     */
    function _completeListener(code, xhr, callbacks){
      Query.fire('QueryComplete', {
        xhr: xhr
      });
      if(callbacks.complete){
        callbacks.complete.apply(this, [xhr]);
      }
    }

    /**
     * Функция-обертка для обработчика события удачного завершения запроса.
     * @private
     * @function
     * @param {String} code Код ответа сервера.
     * @param {XMLHttpRequest} xhr Объект запроса.
     * @param {Object} callbacks Пользовательские функции-обработчики событий ответа сервера.
     */
    function _successListener(code, xhr, callbacks){
      if(xhr.responseText != ''){
        var response = Y.JSON.parse(xhr.responseText);
        Query.fire('QuerySuccess', {
          answer: response
        });
        if(callbacks.success){
          callbacks.success.apply(this, [response]);
        }
      }
    }

    /**
     * Функция-обертка для обработчика события неудачного завершения запроса.
     * @private
     * @function
     * @param {String} code Код ответа сервера.
     * @param {XMLHttpRequest} xhr Объект запроса.
     * @param {Object} callbacks Пользовательские функции-обработчики событий ответа сервера.
     */
    function _failureListener(code, xhr, callbacks){
      var error = Y.JSON.parse(xhr.responseText);
      Query.fire('QueryFailure', {
        exception: error
      });
      Y.log(error, "error");
      if(callbacks.failure){
        callbacks.failure.apply(this, [error]);
      }
    }

    return {
      /**
       * Метод передает запрос центральному контроллеру.
       * Метод вызывает следующие события на объекте:
       * - QueryStart - запрос отправлен.
       * - QueryComplete - запрос завершен. Параметр события xhr хранит объект XMLHttpRequest;
       * - QuerySuccess - запрос завершен успешно. Параметр события answer хранит ответ сервера;
       * - QueryFailure - запрос завершен неуспешно. Параметр события exception хранит объект класса Error, описывающий исключение.
       * @public
       * @function
       * @param {String} module Имя целевого модуля.
       * @param {String} action Имя целевого метода.
       * @param {Object} options Объект конфигурации запроса. Объект может включать следующие свойства:
       * - params - передаваемый серверу массив данных;
       * - sync - синхронность запроса;
       * - timeout - время ожидания ответа в миллисекундах;
       * - context - контекст исполнения функций-обработчиков;
       * - complete(xhr) - функция-обработчик заверешния запроса. Функция принимает единственный аргумент - объект XMLHttpRequest;
       * - success(answer) - функция-обработчик удачного заверешния запроса. Функция принимает единственный аргумент - ответ сервера;
       * - failure(error) - функция-обработчик неудачного заверешния запроса. Функция принимает единственный аргумент - ошибку.
       */
      request: function(module, action, options){
        var data = {
          module: module,
          action: action
        };
        if(options.params !== undefined){
          data.params = Y.JSON.stringify(options.params);
        }

        options.context = options.context || this;

        Query.fire('QueryStart');

        Y.io(CENTRAL_CONTROLLER,
          {
            method: (data.params === undefined)? 'GET' : 'POST',
            data: data,
            timeout: (options.timeout === undefined)? TIMEOUT : options.timeout,
            context: options.context,
            sync: options.sync,
            arguments: {
              complete: options.complete,
              success: options.success,
              failure: options.failure
            },
            on: {
              complete: _completeListener,
              success: _successListener,
              failure: _failureListener
            }
          });
      }
    }
  })();

  Y.augment(Query, Y.EventTarget);
  Y.namespace('DJS.services').Query = Query;
}, '1.0', {requires: ['io-base', 'json-parse', 'json-stringify', 'event']});