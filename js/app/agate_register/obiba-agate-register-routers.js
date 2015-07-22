/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
'use strict';

(function ($) {
  Drupal.behaviors.obiba_agate_register_routes = {
    attach: function (context, settings) {


      mica.agateRegister.config(['$routeProvider', '$locationProvider',
        function ($routeProvider, $locationProvider) {
          $routeProvider
            .when('/join', {
              templateUrl: Drupal.settings.basePath + 'obiba_main_app_angular/obiba_gate/obiba_agate-user-profile-register-form',
              controller: 'RegisterFormController'
            })
          ;
        }]);


    }
  }
}(jQuery));


