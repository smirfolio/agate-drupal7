/**
 * @file
 * Obiba Agate Module AngularJs App Service.
 */
(function ($) {
'use strict';

      mica.agateRegister
        .factory('OidcProvidersResource', ['$resource',
          function ($resource) {
            return $resource(Drupal.settings.basePath + Drupal.settings.pathPrefix + 'agate/auth/providers/ws', {locale: '@locale'}, {
              'get': { method: 'GET', errorHandler: true, isArray: true }
            });
          }])
        .factory('JoinConfigResource', ['$resource',
        function ($resource) {
          return $resource(Drupal.settings.basePath + Drupal.settings.pathPrefix + 'agate/agate-form/ws' + (Drupal.settings.confCleanPath?'?':'&') + 'locale=' + Drupal.settings.angularjsApp.locale, { locale: Drupal.settings.angularjsApp.locale });
        }])
        .factory('JoinResource', ['$http',
        function ($http) {
          return {
            post: function (data) {
              return $http.post(Drupal.settings.basePath + Drupal.settings.pathPrefix + 'agate/agate_user_join/ws', $.param(data), {
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
              });
            }
          };
        }])
        .factory('ClientConfig', ['$resource',
        function ($resource) {
          return $resource(Drupal.settings.basePath + Drupal.settings.pathPrefix + 'agate/config/client/ws').get();
        }]);

}(jQuery));