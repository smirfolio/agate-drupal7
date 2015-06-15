/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving
 */
(function ($) {
  Drupal.behaviors.obiba_agate_confirm_register = {
    attach: function (context, settings) {

      var resource = location.href.split('#');
      var query = location.href.split('=');
      var resourceAction = /\/(.*?)\?key/i.exec(resource[1]);
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
            ajaxrequest = $.ajax({
              method: "POST",
              url: Drupal.settings.basePath + "agate/send_password/"+resourceAction[1],
              data: { key: query[1], password:$('#type-password').val() }
            });

          ajaxrequest.done(function( msg ) {
              window.location = Drupal.settings.basePath + 'user/login';
              return false;
            });
          ajaxrequest.fail(function(){
              console.log('error');
              window.location = Drupal.settings.basePath;
              return false;
            });
        }
      });




    }
  }
}(jQuery));


