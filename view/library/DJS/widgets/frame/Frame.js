/**
 * @namespace DJS\widgets\Frame
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.Frame', function (Y){
  /**
   * Данный виджет загружает контент из стороннего HTML файла, адрес которого указан в свойстве src, и преобразовывает вложенные в него виджеты.
   * Виджет использует синхронную загрузку контента по умолчанию.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.Frame}
   */
  function Frame(cnf){
    Frame.superclass.constructor.apply(this, arguments);
  }

  Frame.NAME = 'frame';
  Frame.ATTRS = {
    /**
     * Адрес загружаемого HTML файла относительно корня системы.
     * @public
     * @type {string}
     */
    src: {},
    /**
     * Флаг готовности контента. Если флаг установлен в true - виджет не будет загружать контент во время рендеринга.
     * @public
     * @type {boolean}
     */
    filled: {
      value: false
    },
    /**
     * Флаг рендеринга контента. Если флаг установлен в true - виджет не будет рендерить встроенные виджеты.
     * @public
     * @type {boolean}
     */
    transform: {
      value: false
    },
    /**
     * Адрес CSS стиля фрейма относительно корня системы. false - если фрейм не имеет файла стилизации.
     * @public
     * @type {string|boolean}
     */
    srcStyle: {
      value: false
    },
    /**
     * Механизм загрузки: true - синхронный, false - асинхронный.
     * @public
     * @type {boolean}
     */
    sync: {
      value: true
    },
    /**
     * Массив внедренных узлов. Для того, чтобы добавить узел фрейма в массив, ему необходимо задать аттрибут dInject, в качестве значения которого следует указать имя ключа, под которым он будет внедрен в данным массив.
     * @public
     * @type {Node[]}
     */
    inject: {
      value: {}
    }
  };
  Frame.HTML_PARSER = {
    src: function (srcNode){
      if (srcNode.hasAttribute('dSrc')){
        return srcNode.getAttribute('dSrc');
      }
    },
    srcStyle: function(srcNode){
      if(srcNode.hasAttribute('dSrcStyle')){
        var src = srcNode.getAttribute('dSrcStyle');
        if(src == 'false'){
          return false;
        }
        else{
          return src;
        }
      }
      else{
        return false;
      }
    },
    filled: function (srcNode){
      return srcNode.getHTML() !== '';
    },
    transform: function(srcNode){
      if(srcNode.hasAttribute('dTransform')){
        return (srcNode.getAttribute('dTransform') == 'true');
      }
      else{
        return false;
      }
    },
    sync: function (srcNode){
      if (srcNode.hasAttribute('dSync')){
        return (srcNode.getAttribute('dSync') == 'true');
      }
      else{
        return true;
      }
    },
    inject: function(srcNode){
      var inject = {};
      srcNode.all('[dInject]').each(function(node){
        inject[node.getAttribute('dInject')] = node;
      });
      return inject;
    }
  };

  Y.extend(Frame, Y.Widget, {
    /**
     * Рендеринг виджета включает процесс загрузки контента по установленному пути, если до вызова метода компонент не был заполнен.
     * Метод вызывает следующие события на объекте:
     * - loadContentStart - начата загрузка контента фрейма;
     * - loadContentComplete - загрузка контента фрейма завершена;
     * - renderWidgetsStart - начат рендеринг встроенных виджетов контента;
     * - renderWidgetsComplete - рендеринг встроенных виджетов контента завершен.
     */
    renderUI: function (){
      var cb = this.get('contentBox'),
        context = this;
      // Загрузка контента фрейма.
      if (!this.get('filled')){
        var src = this.get('src');
        if (!src){
          throw new Error('Не определено обязательное свойство [src] виджета [DJS.widgets.Frame].');
        }
        // Синхронная загрузка контента.
        this.fire('loadContentStart');
        Y.io(src, {
          sync: this.get('sync'),
          context: cb,
          on: {
            complete: function (code, response){
              if (response && response.responseText){
                cb.setContent(response.responseText);
              }
            },
            failure: function (){
              throw new Error('Невозможно загрузить контент фрейма [DJS.widgets.Frame], файл по указанному адресу [' + src + '] не найден.');
            }
          }
        });
        this.set('filled', true);
        this.fire('loadContentComplete');
      }
      // Внедрение узлов.
      var inject = {};
      cb.all('[dInject]').each(function(node){
        inject[node.getAttribute('dInject')] = node;
      });
      this.set('inject', inject);
      // Рендеринг контента фрейма.
      if(!this.get('transform')){
        this.fire('renderWidgetsStart');
        cb.all('[dWidget]').each(function (widgetNode){
          var widgetPath = widgetNode.getAttribute('dWidget'),
            widgetName = 'DJS.widgets.' + widgetPath;
          // Автоматическое подключение виджетов.
          if (Y.config.groups.DJS_widgets.modules[widgetName] === undefined){
            var cwn = widgetPath.split('.');
            // Подключение виджета по пути, указанном в dWidget относительно каталога /D/view
            Y.config.groups.DJS_widgets.modules[widgetName] = {
              path: '/' + cwn[0] + '/' + cwn[1] + '/' + cwn[2] + '/' + cwn[3] + '.js'
            };
          }
          // Рендеринг виджета.
          Y.use(widgetName, function (){
            widgetNode.widget = new (eval('Y.' + widgetName))({
              srcNode: widgetNode,
              sync: context.get('sync')
            });
            widgetNode.widget.render();
          });
        });
        this.set('transform', true);
        this.fire('renderWidgetsComplete');
      }
      // Подключение файла стиля.
      var style = this.get('srcStyle');
      if(style){
        Y.Node.one('head').append('<link rel="stylesheet" href="'+style+'" />');
      }
    },

    syncUI: function(){
      var cb = this.get('contentBox');
      // Синхронизация атрибутов узла.
      cb.setAttribute('dSrc', this.get('src'));
      cb.setAttribute('dFilled', this.get('filled'));
      cb.setAttribute('dTransform', this.get('transform'));
      cb.setAttribute('dSrcStyle', this.get('srcStyle'));
      cb.setAttribute('dSync', this.get('sync'));
    }
  });

  Y.namespace('DJS.widgets').Frame = Frame;
}, '1.0', {requires: ['widget', 'node', 'io', 'event']});