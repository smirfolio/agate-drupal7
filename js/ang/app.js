/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_auth = {
    attach: function (context, settings) {
      'use strict';
      /* App Module */
      var obibaAth = angular.module('ObibaAuth', [
//        'ngAnimate',
//        'ngCookies',
        'ngResource',
        'ngSanitize',
        'ui.bootstrap',
//        'ngRoute',
        'schemaForm'
      ]);

      obibaAth.controller('RegisterFormController', ['$scope', '$log', 'UserResource', function ($scope, $log, UserResource) {

        settings.form.push(
          {
            type: "submit",
            title: Drupal.t('Join')
          }
        );

        $scope.form = angular.fromJson(settings.form);
        $scope.schema = angular.fromJson(settings.schema);
        $scope.model = {};

        $scope.onSubmit = function (form) {
          // First we broadcast an event so all fields validate themselves
          $scope.$broadcast('schemaFormValidate');
          // Then we check if the form is valid
          if (form.$valid) {
            UserResource.post($scope.model).
              success(function (data, status, headers, config) {
                $scope.alert = {
                  message: Drupal.t('You will receive an email to confirm your registration.'),
                  type: 'success'
                };
                var elem = document.getElementById("obiba-user-register");
                angular.element(elem).remove();

              })
              .error(function (data, status, headers, config) {
                var errorParse = angular.fromJson(data);

                $scope.alert = {
                  message: Drupal.t(' Code :' + status + ' :' + errorParse.errorMessage),
                  type: 'danger'
                };
                //populate captcha field with new math challenge question
                $scope.form[5].placeholder = errorParse.updatedField.form[5].placeholder;

                $scope.$broadcast('schemaFormRedraw');
                $scope.model.captcha = ''

              });
          }
          $scope.closeAlert = function () {
            $scope.alert = [];
          };
        };

      }]);


      obibaAth.factory('UserResource', ['$http',
        function ($http) {
          return {
            post: function (data) {
              return $http.post('/drupal/agate_user_join', $.param(data), {
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
              });
            }
          };
        }]);


    }
  }
}(jQuery));


