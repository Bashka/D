/**
 * @namespace DJS\widgets\TabView
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.TabView', function (Y){
  /**
   * Данный виджет представляет визуальные панели с вкладками.
   * Виджет может быть сформирован из HTML узлов документа следующим образом:
   * - ul - узел, определяющий вкладки панелей;
   * -- li - вкладка;
   * --- a - имя вкладки;
   * - div - узел определяющий панели;
   * -- div - панель.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {DJS.widgets.TabView}
   */
  function TabView(cnf){
    TabView.superclass.constructor.apply(this, arguments);
  }

  TabView.NAME = 'tabView';

  Y.extend(TabView, Y.TabView);

  Y.namespace('DJS.widgets').TabView = TabView;
}, '1.0', {requires: ['widget', 'tabview']});
