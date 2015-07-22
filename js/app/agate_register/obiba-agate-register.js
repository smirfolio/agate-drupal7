/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_agate_register = {
    attach: function (context, settings) {

      'use strict';
      /* App Module */
      mica.agateRegister= angular.module('mica.agateRegister', [
        'ui.bootstrap',
        'schemaForm',
        'vcRecaptcha'
      ]);

    }
  }
}(jQuery));


