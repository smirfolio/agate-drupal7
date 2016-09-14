/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
'use strict';

(function ($) {
  Drupal.behaviors.obiba_agate_profile_services = {
    attach: function (context, settings) {
      var locatedPathUrl = Drupal.settings.basePath + Drupal.settings.pathPrefix;
      mica.agateProfile
        .factory('AgateFormResource', ['$resource',
          function ($resource) {
            return $resource(locatedPathUrl + 'agate/agate-form/ws?locale=' + Drupal.settings.angularjsApp.locale, {}, {
              'get': {
                method: 'GET', errorHandler: true
              }
            });
          }])
        .factory('AgateUserProfile', ['$resource', function ($resource) {
          return $resource(locatedPathUrl + 'agate/agate-user-profile/ws', {}, {
            'save': {method: 'PUT', errorHandler: true},
            'get': {method: 'GET', errorHandler: true, params: {locale: Drupal.settings.angularjsApp.locale}}
          });
        }])
        .factory('AgateUserPassword', ['$resource', function ($resource) {
          return $resource(locatedPathUrl + 'agate/agate-user-update-password/ws', {}, {
            'save': {method: 'PUT', errorHandler: true}
          });
        }]);
    }
  }
}(jQuery));
