/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_agate = {
    attach: function (context, settings) {
      'use strict';
      /* App Module */
      var obibaAth = angular.module('ObibaAgate', [
        'ngResource',
        'ngSanitize',
        'ui.bootstrap',
        'schemaForm',
        'vcRecaptcha'
      ]);


      obibaAth.controller('RegisterFormController', ['$scope', '$log', 'UserResource', 'vcRecaptchaService', function ($scope, $log, UserResource, vcRecaptchaService) {

        $scope.form = angular.fromJson(settings.form);
        $scope.schema = angular.fromJson(settings.schema);
        $scope.config = {
          key: settings.recaptchaKey
        };
        $scope.response = null;
        $scope.widgetId = null;
        $scope.model = {};

        $scope.setWidgetId = function (widgetId) {
          $scope.widgetId = widgetId;
        };
        $scope.setResponse = function (response) {
          $scope.response = response;
        };

        $scope.setWidgetId = function (widgetId) {
          $scope.widgetId = widgetId;
        };
        $scope.onSubmit = function (form) {
          // First we broadcast an event so all fields validate themselves
          $scope.$broadcast('schemaFormValidate');
          // Then we check if the form is valid
          if (form.$valid) {
            UserResource.post(angular.extend({}, $scope.model, {reCaptchaResponse: $scope.response})).
              success(function (data, status, headers, config) {
                $scope.alert = {
                  message: Drupal.t('You will receive an email to confirm your registration with the instructions to set your password.'),
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
                //re-load ReCaptcha field
                vcRecaptchaService.reload($scope.widgetId);

              });
          }
          $scope.closeAlert = function () {
            $scope.alert = [];
          };
        };
        $scope.onCancel = function (form) {
          window.location = Drupal.settings.basePath;
        }


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


