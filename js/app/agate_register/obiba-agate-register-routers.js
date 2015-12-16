/**
 * @file
 * Obiba Agate Module AngularJs App Routers config.
 */

'use strict';



      mica.agateRegister.config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {
          $routeProvider
            .when('/join', {
              templateUrl: Drupal.settings.basePath + 'obiba_mica_app_angular/obiba_gate/obiba_agate-user-profile-register-form',
              controller: 'RegisterFormController'
            });
        }]);


