/**
 * @file
 * Obiba Agate Module AngularJs App Service.
 */
(function ($) {
'use strict';

      mica.agateRegister.factory('UserResourceJoin', ['$http',
        function ($http) {
          var drupalPathResource = Drupal.settings.basePath + 'agate/agate_user_join/ws';
          return {
            post: function (data) {
              return $http.post(drupalPathResource, $.param(data), {
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
              });
            }
          };
        }])
        .factory('AgateJoinFormResource', ['$resource',
          function ($resource) {
            return $resource(Drupal.settings.basePath + 'agate/agate-form/ws', {}, {
              'get': {
                method: 'GET', errorHandler: true
              }
            });
          }]);

}(jQuery));