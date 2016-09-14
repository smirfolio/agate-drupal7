/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
'use strict';

(function ($) {
  Drupal.behaviors.obiba_agate_profile_routes = {
    attach: function (context, settings) {

      mica.agateProfile.config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {
          var locatedPathUrl = Drupal.settings.basePath + Drupal.settings.pathPrefix;
          $routeProvider
           .when('/view', {
              templateUrl: locatedPathUrl + 'obiba_mica_app_angular_view_template/obiba_agate-user-profile-view',
              controller: 'UserViewProfileController'
            }).
            when('/edit', {
              templateUrl: locatedPathUrl + 'obiba_mica_app_angular_view_template/obiba_agate-user-profile-form',
              controller: 'UserEditProfileController'
            })
            .otherwise({
              templateUrl: locatedPathUrl + 'obiba_mica_app_angular_view_template/obiba_agate-user-profile-view',
              controller: 'UserViewProfileController'
            });
        }]);

    }
  }
}(jQuery));
