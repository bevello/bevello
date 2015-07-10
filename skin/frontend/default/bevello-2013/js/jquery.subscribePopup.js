(function(window){
  /*\
  |*|
  |*|  :: cookies.js ::
  |*|
  |*|  A complete cookies reader/writer framework with full unicode support.
  |*|
  |*|  https://developer.mozilla.org/en-US/docs/DOM/document.cookie
  |*|
  |*|  This framework is released under the GNU Public License, version 3 or later.
  |*|  http://www.gnu.org/licenses/gpl-3.0-standalone.html
  |*|
  |*|  Syntaxes:
  |*|
  |*|  * docCookies.setItem(name, value[, end[, path[, domain[, secure]]]])
  |*|  * docCookies.getItem(name)
  |*|  * docCookies.removeItem(name[, path], domain)
  |*|  * docCookies.hasItem(name)
  |*|  * docCookies.keys()
  |*|
  \*/

  window.docCookies = {
    getItem: function (sKey) {
      return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
    },
    setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
      if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
      var sExpires = "";
      if (vEnd) {
        switch (vEnd.constructor) {
          case Number:
            sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
            break;
          case String:
            sExpires = "; expires=" + vEnd;
            break;
          case Date:
            sExpires = "; expires=" + vEnd.toUTCString();
            break;
        }
      }
      document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
      return true;
    },
    removeItem: function (sKey, sPath, sDomain) {
      if (!sKey || !this.hasItem(sKey)) { return false; }
      document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + ( sDomain ? "; domain=" + sDomain : "") + ( sPath ? "; path=" + sPath : "");
      return true;
    },
    hasItem: function (sKey) {
      return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
    },
    keys: /* optional method: you can safely remove it! */ function () {
      var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
      for (var nIdx = 0; nIdx < aKeys.length; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
      return aKeys;
    }
  };

  window.getParameterByName = function(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }

})(window);

(function ($, cookies) {
  'use strict';
  if (typeof window.console !== 'object') {
    window.console = {};
  }
  if (typeof window.console.log !== 'function') {
    window.console.log = function () {
    }
  }
  if (typeof window.console.warn !== 'function') {
    window.console.warn = function () {
    }
  }
  $.subscribePopup = function (popup, options) {
    this.$popup = $(popup);
    this.popup = popup;
    this.$popup.data('subscribePopup', this);
    this.$form = this.$popup.find('form').first();

    var log = function () {
      var args = Array.prototype.slice.call(arguments, 0);
      args.unshift('subscribePopup:');
      window.console.log.apply(console, args);
    };

    var me = this;

    var genErrorSpan = function(err){
      return $('<span class="error"></span>').text(err);
    };

    me.init = function () {
      me.options = $.extend({}, $.subscribePopup.defaults, options);
      var cookieID = 'subscribePopup_' + me.options.id;
      me.$popup.easyModal({
        onClose: function(){
          if(!cookies.hasItem(cookieID)){
            cookies.setItem(cookieID, '1', 2592000);
          }
        }
      });

      if(!!me.options.id){

        if(getParameterByName('subscribeSuccess') === me.options.id){
          me.$popup.html('');
          me.$popup.addClass('success');
          cookies.setItem(cookieID, '1', Infinity);
          me.$popup.trigger('openModal');
        }else{
          me.$form.on('submit', function(event){
            var $form = $(event.target),
              $email = $form.find('#mce-EMAIL'),
              $confirm = $form.find('#mce-CONFIRM'),
              $birthday = $form.find('#mce-BIRTHDAY'),
              $birthMonth = $form.find('#mce-BIRTHMONTH'),
              $zip = $form.find('#mce-POSTAL'),
              $state = $form.find('#mce-STATE'),
              $firstName = $form.find('#mce-FNAME'),
              $lastName = $form.find('#mce-LNAME'),
              $realBirthday = $form.find('#mce-REAL-BIRTHDAY'),
              $disclaimer = $form.find('.subscribe-dialog-disclaimer-checkbox');
            $form.find('span.error').remove();
            $form.find('.error').removeClass('error');
            var issue = false;
            if($email.val() !== $confirm.val()){
              $confirm.addClass('error');
              $confirm.parent().prepend(genErrorSpan('Doesn\'t match'));
              $email.addClass('error');
              issue = true;
            }else{
              if($email.val() === ''){
                $email.addClass('error');
                $email.parent().prepend(genErrorSpan('Required'));
                issue = true;
              }
              if($confirm.val() === ''){
                $confirm.addClass('error');
                $confirm.parent().prepend(genErrorSpan('Required'));
                issue = true;
              }
            }
            if($birthday.val() === ''){
              $birthday.addClass('error');
              $birthday.parent().prepend(genErrorSpan('Required'));
              issue = true;
            }
            if($birthMonth.val() === ''){
              $birthMonth.addClass('error');
              $birthMonth.parent().prepend(genErrorSpan('Required'));
              issue = true;
            }
            if($zip.val() === ''){
              $zip.addClass('error');
              $zip.parent().prepend(genErrorSpan('Required'));
              issue = true;
            }
            if($state.val() === ''){
              $state.addClass('error');
              $state.parent().prepend(genErrorSpan('Required'));
              issue = true;
            }
            if($firstName.val() === ''){
              $firstName.addClass('error');
              $firstName.parent().prepend(genErrorSpan('Required'));
              issue = true;
            }
            if($lastName.val() === ''){
              $lastName.addClass('error');
              $lastName.parent().prepend(genErrorSpan('Required'));
              issue = true;
            }
            if(!$disclaimer.prop('checked')) {
              $disclaimer.addClass('error');
              $disclaimer.closest('.subscribe-dialog-control-group').prepend(genErrorSpan('Required'));
              issue = true;
            }

            if(!issue){
              $realBirthday.val($birthMonth.val() + '/' + $birthday.val());
            }else{
              event.preventDefault();
            }
          });

          me.$popup.on('click', '.subscribe-dialog-closer a', function(event) {
            me.$popup.trigger('closeModal');
            event.preventDefault();
          });

          if(!cookies.hasItem(cookieID)){
            me.$popup.trigger('openModal');
          }
        }

      }else{
        console.warn('An ID must be passed to subscribePopup for cookie purposes.');
      }
    };

    me.init();
  };

  $.subscribePopup.defaults = {
    id: false
  };

  $.fn.subscribePopup = function (options) {
    return this.each(function () {
      (new $.subscribePopup(this, options));
    });
  };

})(jQuery, docCookies);