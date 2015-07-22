/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
'use strict';

(function ($) {
  Drupal.behaviors.obiba_agate_profile_services = {
    attach: function (context, settings) {

      mica.agateProfile.factory('UserResourceJoin', ['$http',
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
        .factory('AgateFormResource', ['$resource',
          function ($resource) {
            return $resource(Drupal.settings.basePath + 'agate/agate-form/ws', {}, {
              'get': {
                method: 'GET', errorHandler: true
              }
            });
          }])
        .factory('AgateUserProfile', ['$resource', function($resource){
          return $resource(Drupal.settings.basePath + 'agate/agate-user-profile/ws', {}, {
            'save': {method: 'PUT', errorHandler: true},
            'get': {method: 'GET', errorHandler: true}
          });
        }])
      ;
    }
  }
}(jQuery));


