/**
 * @namespace DJS\widgets\Screen
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.Screen', function (Y){
  /**
   *
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.Screen}
   */
  function Screen(cnf){
    Screen.superclass.constructor.apply(this, arguments);
  }

  Screen.NAME = 'screen';
  Screen.ATTRS = {
    /**
     * Имя родительского модуля.
     * @public
     * @type {string}
     */
    module: {},
    /**
     * Имя экрана.
     * @public
     * @type {string}
     */
    screen: {},
    /**
     * Контент экрана.
     * @public
     * @type {Node}
     */
    frame: {},
    /**
     * Контроллер экрана.
     * @public
     * @type {DJS.classes.Controller}
     */
    controller: {}
  };
  Screen.HTML_PARSER = {
    module: function(srcNode){
      if(srcNode.hasAttribute('dModule')){
        this.set('module', srcNode.getAttribute('dModule'));
      }
    },

    screen: function(srcNode){
      if(srcNode.hasAttribute('dScreen')){
        this.set('screen', srcNode.getAttribute('dScreen'));
      }
    },

    frame: function(srcNode){
      var frameNode = srcNode.get('children');
      // При наличии единственного дочернего узла, он определяется как Frame экрана.
      if(frameNode.size() == 1){
        this.set('frame', frameNode.item(0));
      }
      // Иначе Frame считается не определенным и экран отчищается.
      else{
        srcNode.empty();
      }
    }
  };

  Y.extend(Screen, Y.Widget, {
    /**
     * Рендеринг виджета включает механизм формирования контента экрана (Frame), а так же загрузки и инициализации контроллера.
     * В случае, если узел виджета содержит единственный дочерний элемент, он принимается за узел Frame, которому устанавливаются свойства src и widget в случае их отсутсвтия в узле.
     * Если узел виджета содержит 0 или более 1 узла, они удаляются, а за место них создается новый узел для Frame виджета, которому устанавливаются свойства src и widget.
     * Загрузка Frame всегда выполняется синхронно.
     * Узелу виджета Frame устанавливается класс yui3-frame-имяМодуля-имяЭкрана для применения стилизации.
     */
    renderUI: function (){
      var module = this.get('module'),
        screen = this.get('screen'),
        frame = this.get('frame');
      if (!module){
        throw new Error('Не определено обязательное свойство [module] виджета [DJS.widgets.Screen].');
      }
      if (!screen){
        throw new Error('Не определено обязательное свойство [screen] виджета [DJS.widgets.Screen].');
      }
      // Frame
      // При отсутствии узла.
      if(!frame){
        frame = Y.Node.create('<div dWidget="Frame" dSrc="/D/view/screens/'+module+'/'+screen+'/'+screen+'.html" dSrcStyle="/D/view/screens/'+module+'/'+screen+'/'+screen+'.css"></div>');
        this.get('contentBox').append(frame);
        this.set('frame', frame);
      }
      // При наличии узла.
      else{
        if(!frame.hasAttribute('dSrc')){
          frame.setAttribute('dSrc', '/D/view/screens/'+module+'/'+screen+'/'+screen+'.html');
        }
        if(!frame.hasAttribute('dWidget')){
          frame.setAttribute('dWidget', 'Frame');
        }
        if(!frame.hasAttribute('dSrcStyle')){
          frame.setAttribute('dSrcStyle', '/D/view/screens/'+module+'/'+screen+'/'+screen+'.css');
        }
      }
      frame.addClass('yui3-frame-'+module+'-'+screen);
      frame.widget = new Y.DJS.widgets.Frame({
        srcNode: frame,
        sync: true
      });
      this.fire('loadContentStart');
      frame.widget.render();
      this.fire('loadContentComplete');
      // Controller
      // Информирование песочницы о расположении контроллера
      this.fire('loadControllerStart');
      var controllerName = 'DJS.controllers.'+module+'.'+screen+'.Controller',
        screenWidget = this;
      Y.config.groups.DJS_controllers.modules[controllerName] = {
        path: '/'+module+'/'+screen+'/'+screen+'.js'
      };
      // Создание и инициализация контроллера
      Y.use(controllerName, function(){
        var controller = eval('Y.' + controllerName);
        controller = new controller({
          screen: screenWidget
        });
        screenWidget.set('controller', controller);
        controller.initScreen();
        screenWidget.fire('loadControllerComplete');
      });
    }
  });

  Y.namespace('DJS.widgets').Screen = Screen;
}, '1.0', {requires: ['widget', 'node', 'io', 'event', 'DJS.widgets.Frame']});