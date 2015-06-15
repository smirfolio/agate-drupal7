/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_agate_confirm_register = {
    attach: function (context, settings) {

      var query = location.href.split('=');
      $('#key').val(query[1]);
      $('#verif-password').keyup(function(){

        if($('#type-password').val() != $('#verif-password').val()) {
          $('.form-item-repassword').addClass('has-error');
        }
        else{
          $('.form-item-repassword').removeClass('has-error');
          $('.form-item-repassword').addClass('has-success');
          $('#password').val($('#verif-password').val());
        }
      });
      $('#edit-submit').click(function(e){
        e.preventDefault();
        if($('#type-password').val() != $('#verif-password').val() || !$('#type-password').val()) {
          $('.form-item-repassword').addClass('has-error');
          return false;
        }
        else{
          $.ajax({
            method: "POST",
            url: Drupal.settings.basePath + "agate/send_confirmation",
            data: { key: query[1], password:$('#type-password').val() }
          })
            .done(function( msg ) {
              window.location = Drupal.settings.basePath + 'user/login';
              return false;
            })
            .fail(function(){
              console.log('error');
              window.location = Drupal.settings.basePath;
              return false;
            });
        }
      });




    }
  }
}(jQuery));


