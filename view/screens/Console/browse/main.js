YUI().use('node', 'DJS.widgets.Screen', function(Y){
  (new Y.DJS.widgets.Screen({
    srcNode: Y.one('#DJS-rootScreen')
  })).render();
});