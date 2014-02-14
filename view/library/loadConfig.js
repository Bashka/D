var YUI_config = {
  groups: {
    DJS_widgets: {
      base: '/D/view',
      async: false,
      modules: {
        'DJS.widgets.Frame': {
          path: '/library/DJS/widgets/frame/Frame.js',
          requires: ['widget', 'node', 'io', 'event']
        },
        'DJS.widgets.Screen': {
          path: '/library/DJS/widgets/screen/Screen.js',
          requires: ['widget', 'node', 'io', 'event', 'DJS.widgets.Frame']
        },
        'DJS.widgets.LoadingIndicator': {
          path: '/library/DJS/widgets/loadingIndicator/LoadingIndicator.js',
          requires: ['widget', 'node', 'event']
        },
        'DJS.widgets.HotKey': {
          path: '/library/DJS/widgets/hotKey/HotKey.js',
          requires: ['widget', 'node', 'event']
        },
        'DJS.widgets.Menu': {
          path: '/library/DJS/widgets/menu/Menu.js',
          requires: ['widget', 'node', 'event', 'node-menunav']
        },
        'DJS.widgets.TabView': {
          path: '/library/DJS/widgets/tabView/TabView.js',
          requires: ['widget', 'tabview']
        },
        'DJS.widgets.Editor': {
          path: '/library/DJS/widgets/editor/Editor.js',
          requires: ['widget', 'node', 'event', 'editor-base', 'editor-bidi', 'editor-selection']
        }
      }
    },
    DJS_services: {
      base: '/D/view/library/DJS/services/',
      async: false,
      modules: {
        'DJS.services.Query': {
          path: 'Query.js',
          requires: ['io-base', 'json-parse', 'json-stringify', 'event']
        },
        'DJS.services.User': {
          path: 'User.js',
          requires: ['DJS.services.Query', 'event', 'oop']
        }
      }
    },
    DJS_classes: {
      base: '/D/view/library/DJS/classes',
      async: false,
      modules: {
        'DJS.classes.Controller': {
          path: '/controller/Controller.js',
          requires: ['base', 'DJS.widgets.Screen', 'DJS.services.Query']
        }
      }
    },
    DJS_controllers: {
      base: '/D/view/screens',
      async: false,
      modules: {}
    }
  }
};