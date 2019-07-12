/**
 * @file
 * Obiba Agate Module AngularJs App.
 */


      'use strict';
      /* App Module */
      mica.agateRegister = angular.module('mica.agateRegister', [
        'ui.bootstrap',
        'schemaForm',
        'vcRecaptcha'
      ]).service('DrupalSettings', [function(){
        return {baseUrl: Drupal.settings.basePath + Drupal.settings.pathPrefix}
      }]);


