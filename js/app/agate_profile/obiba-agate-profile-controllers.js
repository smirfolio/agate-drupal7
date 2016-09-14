/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_agate_profile_controllers = {
    attach: function (context, settings) {

      'use strict';

      mica.agateProfile.controller('ModalPasswordUpdateController',
        ['$scope',
          '$uibModalInstance',
          'userId',
          'AlertService',
          'AgateUserPassword',
          function ($scope,
                    $uibModalInstance,
                    userId,
                    AlertService,
                    AgateUserPassword) {
            $scope.ok = function () {
              /*Validation*/
              if ($scope.profile.NewPassword !== $scope.profile.ConfirmPassword) {
                AlertService.alert({
                  id: 'ModalPasswordUpdateController',
                  type: 'danger',
                  msg: Drupal.t('The password and its confirmation do not match!'),
                  delay: 3000
                });
              } else {
                AgateUserPassword.save('', {
                    currentPassword: $scope.profile.password,
                    newPassword: $scope.profile.NewPassword
                  }, function (response) {
                    if (response.errorServer) {
                      AlertService.alert({
                        id: 'ModalPasswordUpdateController',
                        type: 'danger',
                        msg: Drupal.t('Server Error :') + response.errorServer,
                        delay: 3000
                      });
                    } else {
                      AlertService.alert({
                        id: 'UserProfile',
                        type: 'success',
                        msg: Drupal.t('The changes have been saved.'),
                        delay: 3000
                      });
                      $uibModalInstance.close();
                    }
                  }
                );
              }
            };

            $scope.cancel = function () {
              $uibModalInstance.dismiss('cancel');
            };
          }]);

      mica.agateProfile.controller('UserViewProfileController', [
        '$rootScope',
        '$scope',
        '$sce',
        'AgateFormResource',
        'AgateUserProfile',
        '$uibModal',
        function ($rootScope,
                  $scope,
                  $sce,
                  AgateFormResource,
                  AgateUserProfile,
                  $uibModal) {

          AgateFormResource.get(function onSuccess(FormResources) {
            $scope.model = {};

            $scope.form = FormResources.form;
            $scope.schema = FormResources.schema;
            $scope.schema.readonly = true;
            AgateUserProfile.get(function onSuccess(userProfile) {
              userProfile.userProfile.username = Drupal.settings.agateParam.userId;
              $scope.model = userProfile.userProfile;
              $scope.DrupalProfile = $sce.trustAsHtml(userProfile.drupalUserDisplay);

              /*********U P D A T E    P A S S W O R D   U S E R ********************/

              $scope.updatePasswordUser = function () {
                var locatedPathUrl = Drupal.settings.basePath + Drupal.settings.pathPrefix;
                $uibModal.open({
                  templateUrl: locatedPathUrl + 'obiba_mica_app_angular_view_template/obiba-agate-user-update-password-modal',
                  controller: 'ModalPasswordUpdateController',
                  resolve: {
                    userId: function () {
                      return $scope.model.username;
                    }
                  }
                }).result.then(function (data) {
                  $scope.profile = data;
                }, function () {
                  console.log('Modal dismissed at: ' + new Date());
                });

              };
            });
          });

        }]);

      mica.agateProfile.controller('UserEditProfileController', ['$scope',
        '$location',
        'AlertService',
        'AgateUserProfile',
        'AgateFormResource',
        '$sce',
        function ($scope,
                  $location,
                  AlertService,
                  AgateUserProfile,
                  AgateFormResource,
                  $sce) {
          AgateFormResource.get(
            function onSuccess(AgateProfileForm) {
              $scope.model = {};
              $scope.form = AgateProfileForm.form;
              $scope.schema = AgateProfileForm.schema;
              $scope.response = null;
              if ($scope.schema.properties.username) {
                $scope.schema.properties.username.readonly = true;
              }
              AgateUserProfile.get(function onSuccess(userProfile) {
                userProfile.userProfile.username = Drupal.settings.agateParam.userId;
                $scope.model = userProfile.userProfile;

              });
              $scope.ClientProfileEditForm = $sce.trustAsHtml(Drupal.settings.agateParam.ClientProfileEditForm);
              $scope.onSubmit = function (form) {
                $scope.$broadcast('schemaFormValidate');
                if (form.$valid) {
                  AgateUserProfile.save($scope.model, function (response) {
                    response.locationRedirection = response.locationRedirection ? response.locationRedirection : 'view';
                    if (response && !response.errorServer) {
                      AlertService.alert({
                        id: 'UserProfile',
                        type: 'success',
                        msg: Drupal.t('The changes have been saved.'),
                        delay: 3000
                      });

                    }
                    else {
                      AlertService.alert({
                        id: 'UserProfile',
                        type: 'warning',
                        msg: response.errorServer,
                        delay: 3000
                      });
                    }
                    $location.path(response.locationRedirection).replace();
                  });
                }
              }

            }
          );

        }]);

    }
  }
}(jQuery));
