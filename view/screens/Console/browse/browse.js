YUI.add('DJS.controllers.Console.browse.Controller', function(Y){
  function Controller(cnf){
    Controller.superclass.constructor.apply(this, arguments);
  }

  Y.extend(Controller, Y.DJS.classes.Controller, {
    /**
     * Метод отвечает за визуализацию индикатора загрузки.
     * @private
     */
    _listenQuery: function(){
      var li = this._getInject()['loadingIndicator'].widget;
      // Обработка обращения к серверу.
      Y.DJS.services.Query.on('QueryStart', function(){
        li.addApplication();
      });
      // Обработка завершения обращения к серверу.
      Y.DJS.services.Query.on('QueryComplete', function(){
        li.removeApplication();
      });
    },

    /**
     * Метод отвечает за взаимодействие консолей ввода и вывода.
     * @private
     */
    _listenConsoles: function(){
      var co = this._getInject()['consoleOut'].widget,
        ci = this._getInject()['consoleIn'].widget;
      // Установка Auto complete для ConsoleIn виджета.
      ci.on('focusModule', function(){
        this._request('getModulesNames', [], function(answer){
          ci.setModuleAutoComplete(answer);
        });
      }, this);
      ci.on('focusAction', function(){
        this._request('getModuleActions', [ci.getModule()], function(answer){
          ci.setActionAutoComplete(answer);
        },
        function(){
          ci.setActionAutoComplete([]);
        });
      }, this);
      // Визуализация запроса к серверу.
      ci.on('request', function(data){
        co.addRequest(data.module, data.action, data.params);
      }, this);
      // Визуализация ответа от сервера.
      ci.on('success', function(data){
        co.addResponse(Y.JSON.stringify(data.response));
      }, this);
      // Визуализация ошибки сервера.
      ci.on('failure', function(data){
        co.addResponse(Y.JSON.stringify(data.error));
      }, this);
      // Повтор команды.
      co.on('selectRequest', function(data){
        ci.setModule(data.module);
        ci.setAction(data.action);
        ci.addArguments(data.params);
      }, this);
    },

    initScreen: function(){
      var controller = this;
      // Визуализация индикатора загрузки.
      this._listenQuery();
      // Взаимодействие панелей ввода/вывода.
      this._listenConsoles();
      // Обработка горячих клавиш.
      this._getInject()['hotKey'].widget.addCommand(true, false, false, 'enter', function(){
        controller._getInject()['consoleIn'].widget.send();
        return true;
      });
    }
  });

  Y.namespace('DJS.controllers.Console.browse').Controller = Controller;
}, '1.0', {requires: ['node', 'DJS.classes.Controller', 'DJS.services.Query', 'json-stringify']});