YUI.add('DJS.widgets.screens.Console.browse.ConsoleIn', function(Y){
  /**
   * Данный виджет представляет панель ввода команд консоли.
   * Виджет может быть сформирован из HTML узлов документа следующим образом:
   * - input[type=dSend] - кнопка отправки команды;
   * - input[type=dModule] - поле ввода целевого модуля;
   * - input[type=dAction] - поле ввода целевого метода;
   * - span[class=dArgs] - контейнер параметров команды.
   * Аттрибут dTimeout узла виджета позволяет задать таймаут запроса.
   * Поля ввода целевых модуля и метода дополняются плагином auto complete для представления данных о доступных модулях и их методах.
   * Виджет генерирует следующие события:
   * - focusModule - пользователь установил фокус поля ввода целевого модуля;
   * - focusAction - пользователь установил фокус поля ввода целевого метода;
   * - request - команда отправлена;
   * - success - команда завершена успешно;
   * - failure - команда завершена с ошибкой.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.screens.Console.browse.ConsoleIn}
   */
  function ConsoleIn(cnf){
    ConsoleIn.superclass.constructor.apply(this, arguments);
  }

  ConsoleIn.NAME = 'consoleIn';
  ConsoleIn.ATTRS = {
    /**
     * Кнопка отправки запроса.
     * @public
     * @type {Node}
     */
    send: {},
    /**
     * Поле ввода имени целевого модуля.
     * @public
     * @type {Node}
     */
    module: {},
    /**
     * Поле ввода целевого метода.
     * @public
     * @type {Node}
     */
    action: {},
    /**
     * Узел, содержащий поля ввода параметров запроса.
     * @public
     * @type {Node}
     */
    argBox: {},
    /**
     * Индекс узлов-параметров запроса.
     * @public
     * @type {Node[]}
     */
    args: {
      value: []
    },
    /**
     * Кнопка, позволяющая добавить новое поле ввода параметра запроса.
     * @public
     * @type {Node}
     */
    argAddButton: {},
    /**
     * Таймаут запроса в миллисекундах.
     * @public
     * @type {number}
     */
    timeout: {
      value: 5000
    }
  };
  ConsoleIn.HTML_PARSER = {
    send: function(node){
      var button = node.one('input[type=dSend]');
      if(button){
        button.setAttribute('type', 'button');
        button.addClass('send');
      }
      return button;
    },
    module: function(node){
      var module = node.one('input[type=dModule]');
      if(module){
        module.setAttribute('type', 'text');
        module.getDOMNode().value = module.getAttribute('value');
        module.addClass('module');
      }
      return module;
    },
    action: function(node){
      var action = node.one('input[type=dAction]');
      if(action){
        action.setAttribute('type', 'text');
        action.getDOMNode().value = action.getAttribute('value');
        action.addClass('action');
      }
      return action;
    },
    argBox: function(node){
      var argBox = node.one('span[class=dArgs]');
      if(argBox){
        argBox.removeClass('dArgs');
        argBox.addClass('args');
      }
      return argBox;
    },
    timeout: function(node){
      var timeout = node.getAttribute('dTimeout');
      if(timeout){
        return timeout;
      }
    }
  };

  Y.extend(ConsoleIn, Y.Widget, {
    renderUI: function(){
      Y.use('autocomplete-filters');
      var cb = this.get('contentBox');
      // Кнопка передачи команды.
      var sendButton = this.get('send');
      if(!sendButton){
        sendButton = Y.Node.create('<input type="button" title="Send" class="send" value=">"/>');
        cb.append(sendButton);
        this.set('send', sendButton);
      }
      // Поле целевого модуля.
      var module = this.get('module');
      if(!module){
        module = Y.Node.create('<input type="text" class="module" title="Module name"/>');
        cb.append(module);
        this.set('module', module);
      }
      module.plug(Y.Plugin.AutoComplete, {
        resultFilters: 'startsWith'
      });
      // Поле целевого метода.
      var action = this.get('action');
      if(!action){
        cb.append('<span>::</span>');
        action = Y.Node.create('<input type="text" class="action" title="Action"/>');
        cb.append(action);
        this.set('action', action);
      }
      action.plug(Y.Plugin.AutoComplete, {
        resultFilters: 'startsWith'
      });
      // Поле аргументов.
      var argBox = this.get('argBox');
      if(!argBox){
        cb.append('(');
        argBox = Y.Node.create('<span class="args"></span>');
        cb.append(argBox);
        this.set('argBox', argBox);
        cb.append(')');
      }
      var argButton = Y.Node.create('<input type="button" title="Insert argument" value="+"/>');
      argBox.insert(argButton, 'after');
      this.set('argAddButton', argButton);
    },

    bindUI: function(){
      // Выполнение команды.
      this.get('send').on('click', function(){
        this.send(this.get('timeout'));
      }, this);
      // Добавление поля для ввода параметра.
      this.get('argAddButton').on('click', function(){
        this.addArgument();
      }, this);
      // Выбор поля ввода модуля.
      this.get('module').on('focus', function(){
        this.fire('focusModule');
      }, this);
      // Выбор поля ввода метода.
      this.get('action').on('focus', function(){
        this.fire('focusAction');
      }, this);
    },

    /**
     * Метод возвращает имя целевого модуля.
     * @returns {string} Имя целевого модуля.
     */
    getModule: function(){
      return this.get('module').getDOMNode().value;
    },

    /**
     * Метод устанавливает целевой модуль.
     * @param {string} value Имя целевого модуля.
     */
    setModule: function(value){
      this.get('module').getDOMNode().value = value;
    },

    /**
     * Метод устанавливает полю ввода целевого модуля данные auto complete.
     * @param {string[]} modules Имена установленных в системе конткретных модулей.
     */
    setModuleAutoComplete: function(modules){
      this.get('module').ac.set('source', modules);
    },

    /**
     * Метод возвращает имя целевого метода.
     * @returns {string} Имя целевого метода.
     */
    getAction: function(){
      return this.get('action').getDOMNode().value;
    },

    /**
     * Метод устанавливает целевой метод.
     * @param {string} value Имя целевого метода.
     */
    setAction: function(value){
      this.get('action').getDOMNode().value = value;
    },

    /**
     * Метод устанавливает полю ввода целевого метода данные auto complete.
     * @param {string[]} actions Имена доступных для вызова методов целевого модуля.
     */
    setActionAutoComplete: function(actions){
      this.get('action').ac.set('source', actions);
    },

    /**
     * Метод возвращает таймаут запроса.
     * @returns {number} Таймаут запроса.
     */
    getTimeout: function(){
      return this.get('timeout');
    },

    /**
     * Метод устанавливает таймаут запроса.
     * @param {string} value Таймаут запроса в миллисекундах.
     */
    setTimeout: function(value){
      this.set('timeout', value);
    },

    /**
     * Метод добавляет значение в качестве очередного параметра целевого метода.
     * @param {string} [value] Значение добавляемого параметра. Если не передано, добавляется поле для ввода значения аргумента.
     */
    addArgument: function(value){
      value = value || '';
      // Добавление узла.
      var argBox = this.get('argBox'),
        args = this.get('args'),
        arg = Y.Node.create('<input type="text" class="arg" />').setAttribute('value', value);
      if(args.length != 0){
        argBox.append(',');
      }
      argBox.append(arg);
      arg.focus();
      // Добавление узла в индекс.
      args.push(arg);
      this.set('args', args);
    },

    /**
     * Метод добавляет параметры в качестве очередных параметров целевого метода.
     * @param {string[]} values Значения добавляемых параметров.
     */
    addArguments: function(values){
      for(var i in values){
        this.addArgument(values[i]);
      }
    },

    /**
     * Метод удаляет все параметры метода.
     */
    emptyArguments: function(){
      this.get('argBox').empty();
      this.set('args', []);
    },

    /**
     * Метод возвращает значения всех установленных параметров целевого метода.
     * @returns {string[]} Массив значений параметров целевого метода.
     */
    getArgumentsValues: function(){
      var args = this.get('args'),
        result = [];
      for(var i in args){
        result.push(args[i].getDOMNode().value);
      }
      return result;
    },

    /**
     * Метод отчищает текущую команду.
     */
    empty: function(){
      this.setModule('');
      this.setAction('');
      this.emptyArguments();
    },

    /**
     * Метод выполняет запрос к методу модуля основываясь на текущей команде.
     * Метод генерирует следующие события виджета:
     * - request - запрос отправлен. Данное событие дополняется следующими параметрами: module - имя целевого модуля; action - имя вызываемого метода; params - массив передаваемых параметров;
     * - success - запрос завершен успешно. Ответ сервера передается в качестве параметра response;
     * - failure - запрос завершен с ошибкой. Ответ сервера передается в качестве параметра error.
     * @param {number} timeout Время ожидания ответа от сервера.
     */
    send: function(timeout){
      timeout = timeout || this.get('timeout');
      var module = this.getModule(),
        action = this.getAction(),
        args = this.getArgumentsValues();
      if(module != '' && action != ''){
        Y.DJS.services.Query.request(module, action, {
          params: args,
          timeout: timeout,
          context: this,
          success: function(response){
            this.fire('success', {response: response});
          },
          failure: function(error){
            this.fire('failure', {error: error});
          }
        });
        this.empty();
        this.fire('request', {module: module, action: action, params: args});
      }
    }
  });

  Y.namespace('DJS.widgets.screens.Console.browse').ConsoleIn = ConsoleIn;
}, '1.0', {requires: ['widget', 'node', 'autocomplete', 'autocomplete-filters', 'DJS.services.Query']});