/**
 * @file
 * Obiba Agate Module AngularJs App Controller.
 */


      'use strict';
mica.agateRegister.controller('RegisterFormController',

  ['$rootScope', '$scope', '$q', '$location', '$cookies', '$translate', '$uibModal', 'JoinConfigResource', 'JoinResource', 'ClientConfig',
    'NOTIFICATION_EVENTS', 'ServerErrorUtils', 'AlertService', 'vcRecaptchaService', 'OidcProvidersResource', 'DrupalSettings',
    function ($rootScope, $scope, $q, $location, $cookies, $translate, $uibModal, JoinConfigResource, JoinResource, ClientConfig,
              NOTIFICATION_EVENTS, ServerErrorUtils, AlertService, vcRecaptchaService, OidcProvidersResource, DrupalSettings) {
      var AGATE_USER_REALM = 'agate-user-realm';

      var userCookie = $cookies.get('u_auth');

      function isAnExternalProvider(selectedRealm) {
        var providerNames = ($scope.providers || []).map(function (provider) {
          return provider.name;
        });

        return providerNames.indexOf(selectedRealm) !== -1;
      }

      function joinHasTobeValidated(selectedRealm) {
        return selectedRealm !== AGATE_USER_REALM && !isAnExternalProvider(selectedRealm);
      }

      function openCredentialsTester(providerName, username) {
        $uibModal.open({
          backdrop: 'static',
          templateUrl: DrupalSettings.baseUrl + 'obiba_mica_app_angular_view_template/obiba_agate-user-profile-register-test-modal',
          controller: 'CredentialsTestModalController',
          resolve: {
            provider: function () {
              return providerName;
            },
            username: function () {
              return username;
            }
          }
        }).result.then(function (value) {
          $scope.model.username = value.username;
          $scope.model.realm = value.provider;

          $scope.outsideRealmValidated = true;
        }, function (reason) {
          $scope.outsideRealmValidated = false;
          $scope.model.realm = AGATE_USER_REALM;

          if (reason.error) {
            // $rootScope.$broadcast(NOTIFICATION_EVENTS.showNotificationDialog, {
            //   message: ServerErrorUtils.buildMessage(reason.error)
            // });

            /* Drupal notification */
            drupalNotificationMessage(ServerErrorUtils.buildMessage(reason.error), 'danger');
            /***********************/
          }
        });
      }

      $q.all([OidcProvidersResource.get({locale: $translate.use()}).$promise, JoinConfigResource.get().$promise]).then(function (values) {
        $scope.providers = values[0];
        $scope.joinConfig = values[1];

        if (userCookie) {
          $scope.model = JSON.parse(userCookie.replace(/\\"/g, "\""));

          $scope.joinConfig.schema.properties.username.readonly = true;
          $scope.joinConfig.schema.properties.realm.readonly = true;
        }
      });

      $scope.outsideRealmValidated = true;

      $scope.model = {};
      $scope.response = null;
      $scope.widgetId = null;
      $scope.hideRegistration = false;
      $scope.config = ClientConfig;

      $scope.hasCookie = !!userCookie;

      $scope.urlOrigin = new URL($location.absUrl()).origin;

      $scope.setResponse = function (response) {
        $scope.response = response;
      };

      $scope.setWidgetId = function (widgetId) {
        $scope.widgetId = widgetId;
      };

      $scope.onSubmit = function (form) {
        // First we broadcast an event so all fields validate themselves
        $scope.$broadcast('schemaFormValidate');

        if (!$scope.response) {
          AlertService.alert({ id: 'RegisterFormController', type: 'danger', msgKey: 'missing-reCaptcha-error' });
          return;
        }

        if (form.$valid && $scope.outsideRealmValidated) {
          var model = $scope.model;
          if (!model.locale) {
            model.locale = $translate.use();
          }
          JoinResource.post(angular.extend({}, model, { reCaptchaResponse: $scope.response }))
            .then(function () {
              // $location.url($location.path());
              // $location.path('/');

              /* Drupal specification */
              drupalNotificationMessage(null, 'success');
              $scope.hideRegistration = true;
              /**********************/

            }, function (data) {
              // $rootScope.$broadcast(NOTIFICATION_EVENTS.showNotificationDialog, {
              //   message: ServerErrorUtils.buildMessage(data)
              // });

              /* Drupal specification */
              drupalNotificationMessage(ServerErrorUtils.buildMessage(data), 'danger');
              /*************************/
              vcRecaptchaService.reload($scope.widgetId);
            });
        } else if (!$scope.outsideRealmValidated) {
          openCredentialsTester($scope.model.realm, $scope.model.username);
        }

      };

      $scope.$on('$destroy', function () {
        $cookies.remove('u_auth');
      });

      $scope.$watch('model.realm', function (newVal) {
        if (newVal && joinHasTobeValidated(newVal)) {
          $scope.outsideRealmValidated = false;
          openCredentialsTester(newVal, $scope.model.username);
        } else if (newVal && isAnExternalProvider(newVal)) {
          var found = angular.element(document).find('#' + newVal);
          if (found && found[0]) {
            found.get(0).click();
          }
          $scope.outsideRealmValidated = true;
        }

      });

      /* Drupal specification */
      function drupalNotificationMessage(message, type){
        function userProvider(realm){
          return $scope.providers.filter(function(provider){
            return provider.name === realm ? realm : null;
          });
        }
        if(!message){
          message = Drupal.t('You will receive an email to confirm your registration with the instructions to set your password.');
          var userProvider = userProvider($scope.model.realm);
          var signInUrl = userProvider.length > 0 ? userProvider[0].linkSingInPath : DrupalSettings.baseUrl + 'user/login';
          if($scope.model.realm !== "agate-user-realm"){
            message = Drupal.t('You can now <a href="@loginLink">Sign In</a>', {'@loginLink':signInUrl});
          }
        }
        AlertService.alert({
          id: 'RegisterFormController',
          type: type,
          msg: message
        });
      }
      /**********************************/
    }])
  .controller('CredentialsTestModalController', ['$scope', '$uibModalInstance', '$resource', 'provider', 'username', 'DrupalSettings',
  function ($scope, $uibModalInstance, $resource, provider, username, DrupalSettings) {
    $scope.provider = provider;
    $scope.username = username;

    $scope.cameWithUsername = username && username.length;

    $scope.cancel = function () {
      $uibModalInstance.dismiss({});
    };

    $scope.test = function () {
      $resource(DrupalSettings.baseUrl + 'agate/users/_test/ws', {}, {'test': {method: 'POST', errorHandler: true}})
        .test({provider: provider, username: $scope.username, password: $scope.password})
        .$promise.then(function (value) {
        $uibModalInstance.close({provider: provider, username: $scope.username});
      }, function (reason) {
        $uibModalInstance.dismiss({error: reason, provider: provider, username: $scope.username});
      });
    }
  }]);

