/**
 * @namespace DJS\widgets\Editor
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.Editor', function (Y){
  /**
   * Данный виджет представляет функциональный текстовый редактор.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.Editor}
   */
  function Editor(cnf){
    Editor.superclass.constructor.apply(this, arguments);
  }

  Editor.NAME = 'editor';
  Editor.ATTRS = {
    /**
     * Узел панели управления.
     * @public
     * @type {Node}
     */
    buttons: {},
    /**
     * Узел панели состояния.
     * @public
     * @type {Node}
     */
    status: {},
    /**
     * Объект, предоставляющий информацию о выделенном фрагменте редактируемого текста.
     * @public
     * @type {EditorSelection}
     */
    select: {},
    /**
     * Объект, предоставляющий панель ввода.
     * @public
     * @type {EditorBase}
     */
    editor: {}
  };
  Editor.HTML_PARSER = {

  };

  // Наследование выполняется от Widget в связи с особенностью реализации класса EditorBase, который не восприимчив к свойству srcNode его конфигуратора, используемому классом Frame для рендеринга встроенных виджетов.
  Y.extend(Editor, Y.Widget, {
    renderUI: function(){
      var cb = this.get('contentBox'),
        content = cb.getHTML();
      cb.empty();

      // Формирование панели управления.
      var buttons = Y.Node.create('<div class="buttons"></div>');
      cb.append(buttons);
      this.set('buttons', buttons);
      var b = Y.Node.create('<input type="button" value="b" />');
      buttons.append(b);
      b.on('click', function(){
        var selected = this.get('select').getSelected();

      }, this);
      var g = Y.Node.create('<input type="button" value="get" />');
      buttons.append(g);
      g.on('click', function(){
        alert(this.get('editor').getInstance().one('body').getHTML());
      }, this);

      // Формирование панели ввода.
      var editor = new Y.EditorBase({
        content: content
      });
      this.set('editor', editor);
      editor.on('ready', function(){
        var instance = editor.getInstance();
        this.set('select', new instance.EditorSelection());
      }, this);
      editor.render(cb);

      // Формирование панели состояния.
      var status = Y.Node.create('<div class="status" style="height: 1.5em">Status panel</div>');
      this.set('status', status);
      cb.append(status);
    },

    // Панель состояния
    /**
     * Метод устанавливает значение панели состояния.
     * @public
     * @param {string} content Устанавливаемое значение.
     */
    status: function(content){
      this.get('status').setHTML(content);
    }
  });

  Y.namespace('DJS.widgets').Editor = Editor;
}, '1.0', {requires: ['widget', 'node', 'event', 'editor-base', 'editor-bidi', 'editor-selection']});
