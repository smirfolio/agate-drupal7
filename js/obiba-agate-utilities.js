/**
 * @file
 * JavaScript ajax helper for Statistics variables retrieving.
 */

(function ($) {
  Drupal.behaviors.obiba_agate_utilities = {
    attach: function (context, settings) {
      var pathName = Drupal.settings.currentPath;
      var hashAttribute = location.href.split('#')[1];
      function destinationLinkDeal(){
        var currentLocation = location.href;
        var urlDestination = currentLocation.split('?destination=')[1];
        if(!urlDestination){
          urlDestination = pathName + '#' + currentLocation.split('#')[1];
        }
        if (hashAttribute) {
          $('form#user-login').each(function () {
            var action = this.getAttribute('action');
            var positionDestination = action.indexOf('?destination=');
            if (positionDestination > 0) {
              var  basePath = action.substring(0, positionDestination != -1 ? positionDestination : action.length);
              action = basePath + '?destination=' + urlDestination;
              $(this).attr('action', action);
            }
          });
          $('a.redirection-place-holder').each(function () {
            var href = this.getAttribute('href');
            var positionDestination = href.indexOf('?destination=');
            if (positionDestination > 0) {

             var  basePath = href.substring(0, positionDestination != -1 ? positionDestination : href.length);
              href = basePath + '?destination=' + urlDestination;
              $(this).attr('href', href);
            }
          });
        }
      }
      destinationLinkDeal();
      window.onhashchange = function(){
        destinationLinkDeal();
      }
    }
  }
}(jQuery));
