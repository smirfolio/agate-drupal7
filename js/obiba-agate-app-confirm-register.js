/**
 * @file
 * JavaScript helper for password activation
 */

(function ($, Drupal) {
  Drupal.behaviors.obiba_agate_confirm_register = {
    attach: function (context, settings) {
      var query = location.href.split('=');

      //Extract the key (confirm/reset_password) between {agate/} and the {?key}  example : xxxxagate/confirm?keyxxxxxx
      var resourceAction = /agate\/(.*?)\?key/i.exec(query[0]);

      $('#key').val(query[1]);
      $('#action').val(resourceAction[1]);
      $('#verif-password').keyup(function () {
        // if re-typed password is same
        if ($('#type-password').val() != $('#verif-password').val()) {
          $('.form-item-repassword').addClass('has-error');
        }
        else {
          $('.form-item-repassword').removeClass('has-error');
          $('.form-item-repassword').addClass('has-success');
          $('#password').val($('#verif-password').val());
        }
      });

    }
  }
}(jQuery, Drupal));
