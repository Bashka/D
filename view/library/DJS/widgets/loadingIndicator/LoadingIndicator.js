/**
 * @namespace DJS\widgets\LoadingIndicator
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.widgets.LoadingIndicator', function(Y){
  /**
   * Данный виджет позволяет визуализировать процесс асинхронного взаимодействия с ресурсами (на пример с сервером).
   * Виджет скрывает себя, когда операции не выполняются, и показывает символ загрузки, когда ожидается исполнение асинхронной операции.
   * @param {Object} cnf Параметры виджета.
   * @constructor
   * @this {LoadingIndicator}
   */
  function LoadingIndicator(cnf){
    LoadingIndicator.superclass.constructor.apply(this, arguments);
  }

  LoadingIndicator.NAME = 'loadingIndicator';
  LoadingIndicator.ATTRS = {
    /**
     * Индикатор загрузки.
     * @public
     * @type {Node}
     */
    imgNode: {
      writeOnce: true
    },
    imgSrc: {
      value: '/D/view/library/DJS/widgets/loadingIndicator/loading.gif',
      writeOnce: true
    },
    /**
     * Число активных заявок.
     * @public
     * @type {int}
     */
    applications: {
      value: 0
    }
  };
  LoadingIndicator.HTML_PARSER = {
    imgNode: function(srcNode){
      var imgNode = srcNode.one('img');
      if(imgNode){
        this.set('imgNode', imgNode);
      }
    },

    imgSrc: function(){
      var imgNode = this.get('imgNode');
      if(imgNode){
        return imgNode.getAttribute('src');
      }
    }
  };

  Y.extend(LoadingIndicator, Y.Widget, {
    renderUI: function(){
      if(!this.get('imgNode')){
        var cb = this.get('contentBox'),
          img = Y.Node.create('<img src="" alt="Loading" />');
        img.setStyle('width', 24);
        img.setStyle('height', 24);
        this.set('imgNode', img);
        cb.append(img);
        var bb = this.get('boundingBox');
        bb.setStyle('width', 24);
        bb.setStyle('height', 24);
        bb.setStyle('position', 'absolute');
      }
    },

    bindUI: function(){
      this.get('boundingBox').on('mouseover', function(){
        this.setStyle('opacity', 0.2);
      });

      this.get('boundingBox').on('mouseout', function(){
        this.setStyle('opacity', 1);
      });
    },

    syncUI: function(){
      this.get('imgNode').setAttribute('src', this.get('imgSrc'));
      if(this.get('applications') == 0){
        this.get('boundingBox').hide();
      }
      else{
        this.get('boundingBox').show();
      }
    },

    /**
     * Метод добавляет заявку в очередь отображая индикацию загрузки.
     * @public
     */
    addApplication: function(){
      this.set('applications', this.get('applications') + 1);
      this.syncUI();
    },

    /**
     * Метод отзывает заявку из очереди. В случае опустошения очереди, индикация загрузки скрывается.
     * @public
     */
    removeApplication: function(){
      var applications = this.get('applications');
      if(applications > 0){
        this.set('applications', applications - 1);
        this.syncUI();
      }
    }
  });

  Y.namespace('DJS.widgets').LoadingIndicator = LoadingIndicator;
}, '2.0', {requires: ['widget', 'node', 'event']});