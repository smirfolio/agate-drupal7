/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_agate_profile_controllers = {
    attach: function (context, settings) {

      'use strict';


      mica.agateProfile.controller('UserViewProfileController', [
        '$scope',
        '$sce',
        'AgateFormResource',
        'AgateUserProfile',
        function ($scope,
                  $sce,
                  AgateFormResource,
                  AgateUserProfile) {

          AgateFormResource.get(function onSuccess(FormResources){
            $scope.model = {};

            $scope.form = FormResources.form;
            $scope.schema = FormResources.schema;
            $scope.schema.readonly = true;
            AgateUserProfile.get(function onSuccess(userProfile) {
              userProfile.userProfile.username = Drupal.settings.agateParam.userId;
              $scope.model = userProfile.userProfile;
              $scope.DrupalProfile = $sce.trustAsHtml(userProfile.drupalUserDisplay);
            });
          });

        }]);

      mica.agateProfile.controller('UserEditProfileController', ['$scope',
        '$location',
        'AlertService',
        'AgateUserProfile',
        'AgateFormResource',
        function ($scope,
                  $location,
                  AlertService,
                  AgateUserProfile,
                  AgateFormResource) {
          AgateFormResource.get(
            function onSuccess(AgateProfileForm) {
              $scope.model = {};
              $scope.form = AgateProfileForm.form;
              $scope.schema = AgateProfileForm.schema;
              $scope.response = null;
              $scope.schema.properties.username.readonly = true;
              AgateUserProfile.get(function onSuccess(userProfile) {
                userProfile.userProfile.username = Drupal.settings.agateParam.userId;
                $scope.model = userProfile.userProfile;

              });

              $scope.onSubmit = function (form) {
                $scope.$broadcast('schemaFormValidate');
                if (form.$valid) {
                  AgateUserProfile.save($scope.model, function (response) {
                    response.locationRedirection = response.locationRedirection ? response.locationRedirection:'view';
                    if (response && !response.errorServer) {
                      AlertService.alert({
                        id: 'MainController',
                        type: 'success',
                        msg: Drupal.t('The changes have been saved.')
                      });

                    }
                    else {
                      AlertService.alert({
                        id: 'MainController',
                        type: 'warning',
                        msg: response.errorServer
                      });
                    }
                    $location.path(response.locationRedirection).replace();
                  });
                }
              }

            }
          );

        }])

    }
  }
}(jQuery));


