/**
 * @namespace DJS\widgets\Menu
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.Menu', function (Y){
  /**
   * Данный виджет представляет меню.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.Menu}
   */
  function Menu(cnf){
    Menu.superclass.constructor.apply(this, arguments);
  }

  Menu.NAME = 'menu';
  Menu.ATTRS = {
    /**
     * Узел ul, являющийся корнем меню.
     * @public
     * @type {Node}
     */
    root: {},
    /**
     * Ориентация ленты: horizontal - горизонтальная; vertical - вертикальная.
     * @public
     * @type {string}
     */
    orientation: {
      value: 'horizontal',
      validator: function(value){
        return (value == 'horizontal' || value == 'vertical');
      }
    },
    /**
     * Объект, используемый для инициализации структуры меню. При указании данного свойства, существующая структура меню не учитывается. Объект имеет следующую структуру:
     * {
     *    orientation: 'horizontal', // Ориентация ленты.
     *    items: [                   // Дочерние элементы
     *      {name: 'Узел A', id: 'ItemA'}, // Листовой узел.
     *      {name: 'Узел B', id: 'ItemB'},
     *      {
     *        name: 'Узел C', id: 'ItemC', // Ветвь.
     *        items: [
     *          {name: 'Узел D', id: 'ItemD', lynk: 'yuilibrary.com'}, // Листовой узел с внешней ссылкой.
     *          {name: 'Узел E', id: 'ItemE'}
     *        ]
     *      }
     *    ]
     *  }
     * @public
     * @type {Object}
     */
    content: {},
    /**
     * Индекс узлов меню по их идентификатору. Данное свойство формируется только при наличии свойства content.
     * @public
     * @type {Object[]}
     */
    items: {
      value: []
    }
  };
  Menu.HTML_PARSER = {
    root: function(srcNode){
      var children = srcNode.get('children');
      // При наличии единственного дочернего узла ul, он определяется как корень меню.
      if(children.size() == 1 && children.item(0).getDOMNode().nodeName == 'UL'){
        return children.item(0);
      }
      // Иначе корень считается не определенным и виджет отчищается.
      else{
        var root = Y.Node.create('<ul></ul>');
        srcNode.empty().append(root);
        return root;
      }
    },
    orientation: function(srcNode){
      if(srcNode.hasAttribute('dOrientation')){
        return srcNode.getAttribute('dOrientation');
      }
    }
  };

  Y.extend(Menu, Y.Widget, {
    /**
     * Метод рекурсивно формирует структуру меню используя объект структуры content.
     * @private
     * @param {Object} item Объекты, выступающие в качестве объектных представлений узлов меню свойства content.
     * @returns {Node} HTML узел, представляющий данный объект.
     */
    _renderItem: function(item){
      // Индексация узлов.
      var items = this.get('items');
      items[item.id] = item;
      this.set('items', items);

      // Формирование листьев.
      if(!item.items || item.items.length == 0){
        return Y.Node.create('<li><a href="'+((item.link)? 'http://'+item.link : '#'+item.id)+'" dId="'+item.id+'">'+item.name+'</a></li>');
      }
      // Формирование ветвей.
      else{
        var subBox = Y.Node.create('<li></li>');
        subBox.append('<span dId="'+item.id+'">'+item.name+'</span>');
        subBox.append('<div><div><ul></ul></div></div>');
        var subRoot = subBox.getElementsByTagName('ul').item(0);
        for(var i in item.items){
          subRoot.append(this._renderItem(item.items[i]));
        }
        return subBox;
      }
    },

    /**
     * Рендеринг виджета позволяет формировать меню основываясь как на объекте, представляющем структуру ленты (свойство content), так и на HTML узлах (свойство srcNode) документа. При этом первый случай является более приоритетным.
     * HTML структура ленты должна иметь следующий вид:
     * <div>
     *   <ul>
     *     <li>
     *       <a>Item A</a> Лист меню.
     *     </li>
     *     <li>
     *       <a>Item B</a>
     *     </li>
     *     <li>
     *       <span>Item C</span> Ветвь меню.
     *       <div>
     *         <div>
     *           <ul>
     *             <li>
     *               <a>Item D</a>
     *             </li>
     *             <li>
     *               <a>Item E</a>
     *             </li>
     *           </ul>
     *         </div>
     *       </div>
     *     </li>
     *   </ul>
     * </div>
     */
    renderUI: function(){
      var cb = this.get('contentBox'),
        root;
      // Определение корня.
      if(!this.get('root')){
        root = Y.Node.create('<ul></ul>');
        cb.append(root);
        this.set('root', root);
      }
      else{
        root = this.get('root');
      }

      // Формирование структуры ленты.
      var content = this.get('content');
      if(content){
        root.empty();
        this.set('orientation', content.orientation);
        for(var item in content.items){
          root.append(this._renderItem(content.items[item]));
        }
      }

      // Определение ориентации.
      this.get('boundingBox').addClass('yui3-menu-'+this.get('orientation'));

      // Рендеринг ленты.
      // Определение классов узлов.
      cb.all('li').each(function(node){
        var children = node.get('children');
        // Выделение листовых узлов.
        if(children.size() == 1){
          node.addClass('yui3-menuitem');
          children.addClass('yui3-menuitem-content');
        }
        // Выделение веток.
        else if(children.size() == 2){
          children.item(0).addClass('yui3-menu-label');
          // Верификация ветки меню.
          var submenuChildren = children.item(1).addClass('yui3-menu').get('children');
          // Определение класса menu-content.
          if(submenuChildren.size() == 1){
            submenuChildren.addClass('yui3-menu-content');
          }
          else{
            // Добавление menu-content в корень ветки меню, если он отсутствует.
            children.item(1).empty().append('<div class="yui3-menu-content"><ul></ul></div>');
          }
        }
        // Исключение нестандартного содержимого.
        else{
          node.empty();
        }
      });
      cb.plug(Y.Plugin.NodeMenuNav);
    },

    /**
     * Виджет генерирует следующие события:
     * - selectMenuItem - пользователем выбран листовой элемент меню. В даном событии передаются следующие параметры: node - целевой узел; item - объектное представление узла;
     * - selectSubMenu - пользователем выбрана ветвь меню. В даном событии передаются следующие параметры: node - целевой узел; item - объектное представление узла.
     */
    bindUI: function(){
      // Выбор листа.
      this.get('root').delegate('click', function(event){
        this.fire('selectMenuItem', {node: event.target, item: this.get('items')[event.target.getAttribute('dId')]});
      }, '.yui3-menuitem-content', this);
      // Выбор ветви.
      this.get('root').delegate('click', function(event){
        this.fire('selectSubMenu', {node: event.target, item: this.get('items')[event.target.getAttribute('dId')]});
      }, '.yui3-menu-label', this);
    }
  });

  Y.namespace('DJS.widgets').Menu = Menu;
}, '1.0', {requires: ['widget', 'node', 'event', 'node-menunav']});
