/**
 * @file
 * Obiba Agate Module AngularJs App Routers config.
 */

'use strict';



      mica.agateRegister.config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {
          var locatedBaseUrl = Drupal.settings.basePath + Drupal.settings.pathPrefix ;
          $routeProvider
            .when('/join', {
              templateUrl: locatedBaseUrl  + 'obiba_mica_app_angular_view_template/obiba_agate-user-profile-register-form',
              controller: 'RegisterFormController'
            });
        }]);


