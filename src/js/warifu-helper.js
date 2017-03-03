/**
 * Description
 */

/*global WarifuHelper: false*/

(function ($) {

  'use strict';

  var msgTimer = null;

  /**
   * Put message
   *
   * @param {HTMLElement} target
   * @param {String}      message
   */
  var message = function( target, message ) {
    var $msg;
    var exists = $(target).next('.warifu-license-message');
    if ( exists.length ) {
      $msg = exists;
    } else {
      $msg = $('<span class="warifu-license-message"></span>');
      $(target).after($msg);
    }
    $msg.text(message);
    if ( msgTimer ) {
      clearTimeout( msgTimer );
    }
    msgTimer = setTimeout(function () {
      $msg.remove();
    }, WarifuHelper.msgTimeout);
  };

  $(document).on('click', 'button[data-warifu="submit"]', function(e){
    e.preventDefault();
    var $button    = $(this);
    var $container = $button.parents('[data-href]');
    var key = $container.find('input[type=text]').val();
    if ( ! key.length ) {
      message( $button, WarifuHelper.noLicense );
    }
    $.ajax({
      url       : $container.attr('data-href'),
      method    : 'POST',
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', WarifuHelper.nonce);
      },
      data: {
        post_id: $container.attr('data-post-id'),
        license: key,
        nonce: WarifuHelper.nonce
      }
    }).done(function (response) {
      message($button, response.message);
    } ).fail( function(response){
      message($button, response.responseJSON.message);
    } );

  } );

})(jQuery);
