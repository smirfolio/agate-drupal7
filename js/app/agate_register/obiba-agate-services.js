/**
 * @file
 * Obiba Agate Module AngularJs App Service.
 */
(function ($) {
'use strict';

      mica.agateRegister.factory('UserResourceJoin', ['$http',
        function ($http) {
          return {
            post: function (data) {
              return $http.post(Drupal.settings.basePath + Drupal.settings.pathPrefix + 'agate/agate_user_join/ws', $.param(data), {
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
              });
            }
          };
        }])
        .factory('AgateJoinFormResource', ['$resource',
          function ($resource) {
            return $resource(Drupal.settings.basePath + Drupal.settings.pathPrefix + 'agate/agate-form/ws?locale=' + Drupal.settings.angularjsApp.locale, {}, {
              'get': {
                method: 'GET', errorHandler: true, params: {locale: Drupal.settings.angularjsApp.locale}
              }
            });
          }]);

}(jQuery));