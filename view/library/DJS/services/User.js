/**
 * @namespace D\view\library\DJS\services\User
 * @author Artur Sh. Mamedbekov
 */
YUI.add('DJS.services.User', function(Y){
  /**
   * Данная служба хранит информацию о текущем пользователе.
   * Служба инициализируется синхронно.
   */
  var User = (function(){
    /**
     * Идентификатор текущего пользователя или null, если пользователь не аутентифицирован или отсутствует модуль Users.
     * @private
     * @static
     * @type {string|null}
     */
    var OID = null,
      /**
       * Массив ролей, делегированных текущему пользователю или пустой массив, если не установлен модуль Access.
       * Роли включают следующие свойства:
       * - OID - идентификатор роли;
       * - name - имя роли.
       * @private
       * @static
       * @type {Object[]}
       */
      roles = [];

    Y.DJS.services.Query.request('ModulesManager', 'hasModule', {
      params: ['Users'],
      sync: true,
      success: function(answer){
        if(answer){
          Y.DJS.services.Query.request('Users', 'identifyUser', {
            sync: true,
            success: function(answer){
              OID = answer.OID;
              Y.DJS.services.Query.request('ModulesManager', 'hasModule', {
                sync: true,
                params: ['Access'],
                success: function(answer){
                  if(answer){
                    Y.DJS.services.Query.request('Access', 'getRolesUser', {
                      params: [OID],
                      sync: true,
                      success: function(answer){
                        roles = answer;
                      }
                    });
                  }
                }
              })
            }
          });
        }
      }
    });

    return {
      /**
       * Метод возвращает идентификатор текущего пользователя.
       * @public
       * @function
       * @return {string|null} Идентификатор текущего пользователя или null если пользователь не идентифицирован или не установлен модуль Users.
       */
      getOID: function(){
        return OID;
      },

      /**
       * Метод возвращает массив ролей, делегированных текущему пользователю.
       * @public
       * @function
       * @return {Object[]} Массив ролей, делегированных текущему пользователю или пустой массив, если пользователю не делегировано ни одной роли или модуль Access не установлен.
       */
      getRoles: function(){
        return roles;
      },

      /**
       * Метод определяет, делегирована ли некоторая роль текущему пользователю.
       * @public
       * @function
       * @param {string} role Имя целевой роли.
       * @return {boolean} true - если роль делегирована, иначе - false.
       */
      hasRole: function(role){
        for(var i in roles){
          if(roles[i].name == role){
            return true;
          }
        }
        return false;
      }
    }
  })();

  Y.augment(User, Y.EventTarget);
  Y.namespace('DJS.services').User = User;
}, '1.0', {requires: ['DJS.services.Query', 'event', 'oop']});