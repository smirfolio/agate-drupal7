/**
 * @file
 * Obiba Agate Module AngularJs App Controller.
 */


      'use strict';
      mica.agateRegister.controller('RegisterFormController',
        ['$scope',
          '$log',
          'UserResourceJoin',
          'vcRecaptchaService',
          'AgateJoinFormResource',
          'AlertService',
          function ($scope,
                    $log,
                    UserResourceJoin,
                    vcRecaptchaService,
                    AgateJoinFormResource,
                    AlertService) {
            AgateJoinFormResource.get(
              function onSuccess(AgateProfileForm) {
                $scope.form = AgateProfileForm.form;
                $scope.schema = AgateProfileForm.schema;
                $scope.config = {
                  key: AgateProfileForm.captchaConfig
                };
                $scope.response = null;
                $scope.widgetId = null;
                $scope.model = {};
                var clientUSer = Drupal.settings.agateParam.userToExport;
                if(clientUSer){
                  $scope.model = {
                    email: clientUSer.mail,
                    username : clientUSer.name
                  };
                  $scope.schema.properties.email.readonly=true;
                  $scope.schema.properties.username.readonly=true;
                }

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
                    UserResourceJoin.post(angular.extend({}, $scope.model, {reCaptchaResponse: $scope.response}))
                        .success(function (data, status, headers, config) {
                        AlertService.alert({
                          id: 'RegisterFormController',
                          type: 'success',
                          msg: Drupal.t('You will receive an email to confirm your registration with the instructions to set your password.'),
                          delay: 3000
                        });
                        var elem = document.getElementById("obiba-user-register");
                        angular.element(elem).remove();

                      })
                      .error(function (data, status, headers, config) {
                        AlertService.alert({
                          id: 'RegisterFormController',
                          type: 'danger',
                          msg: data.message,
                          delay: 3000
                        });
                        vcRecaptchaService.reload($scope.widgetId);

                      });
                  }
                };
                $scope.onCancel = function (form) {
                  window.location = Drupal.settings.basePath;
                }

              }
            );
          }]);

