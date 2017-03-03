/*global angular:false */
angular.module( 'warifu', ['lumx'] )
  .controller('warifuManager', ['$scope', '$http', 'LxDialogService', 'LxNotificationService', function($scope, $http, LxDialogService, LxNotificationService){

    "use strict";

    $scope.labels = window.warifuLabels;

    $scope.licenses = [];

    $scope.currentLicense = null;

    $scope.endpoint = '';

    $scope.nonce = '';

    $scope.loading = true;

    $scope.total = 0;

    $scope.next = true;

    $scope.offset = 0;

    $scope.validateResult = [];

    $scope.validateError = false;

    /**
     * Get endpoint path
     *
     * @param {String} path
     * @returns {String}
     */
    function getEndpoint(path){
      return $scope.endpoint + path;
    }

    /**
     * Error handler
     */
    function errorHandler(respones){
      showError(response.data.message, true);
    }

    /**
     * Show error message
     *
     * @param {String}  string
     * @param {Boolean} error
     */
    function showError(string, error){
      if (error) {
        LxNotificationService.error(string);
      } else{
        LxNotificationService.info(string);
      }
    }

    /**
     * Request endpoint
     *
     * @param {String} method
     * @param {String} endpoint
     * @param {Object} [data]
     * @returns {*}
     */
    function api(method, endpoint, data) {
      var request = {
        method : method,
        url    : getEndpoint(endpoint),
        headers: {
          'X-WP-Nonce': $scope.nonce
        }
      };
      if (data) {
        switch (method) {
          case 'POST':
          case 'PUT':
            request.data = data;
            break;
          default:
            request.params = data;
            break;
        }
      }
      return $http(request);
    }

    /**
     * Get translated string
     *
     * @param {String} key
     * @returns {String}
     */
    function translate(key){
      return $scope.labels[key] || key;
    }

    /**
     * Get Licenses
     * @returns {*}
     */
    $scope.getLicenses = function(){
      return api('GET', 'license', {
        nonce: $scope.nonce
      }).then(function(response){
        $scope.total = response.data.total;
        if ( ! response.data.data.length ) {
          $scope.next = false;
          return true;
        }
        for(var i = 0, l = response.data.data.length; i < l; i++){
          $scope.licenses.push(response.data.data[i]);
        }
      }, errorHandler).then(function(){
        $scope.loading = false;
      });
    };

    /**
     * Initialize
     *
     * @param {String} endpoint
     * @param {String} nonce
     */
    $scope.init = function(endpoint, nonce){
      $scope.endpoint = endpoint;
      $scope.nonce = nonce;
      $scope.getLicenses();
    };

    $scope.validate = function(license, postId){
      $scope.loading = true;
      api('POST', 'validation/' + postId, {
        license: license,
        nonce: $scope.nonce
      }).then(
        function( response ){
          $scope.validateResult = [];
          $scope.validateResult.push({
            title: translate('uses'),
            value: response.data.data.uses
          });
          $scope.validateResult.push({
            title: translate('email'),
            value: response.data.data.purchase.email
          });
          $scope.validateResult.push({
            title: translate('created_at'),
            value: response.data.data.purchase.created_at
          });
          LxDialogService.open('license-dialog');
        },
        function(response){
          LxNotificationService.confirm(response.data.message + ' ' + $scope.labels.may_i_remove, {
              cancel: $scope.labels.disagree,
              ok: $scope.labels.agree
          }, function (answer) {
            if (answer) {
              $scope.removeLicense( license );
            }
          });
        }
      ).then(function(){
        $scope.loading = false;
      });
    };

    $scope.removeLicense = function(license_string){
      var license = null;
      var index   = null;
      for(var i = 0, l = $scope.licenses.length; i < l; i++){
        if ( $scope.licenses[i].key == license_string ) {
          license = $scope.licenses[i];
          index = i;
          break;
        }
      }
      if( !license ){
        showError( translate('no_license'), true );
      }
      $scope.loading = true;
      api( 'DELETE', 'license/' + license.id, {
        nonce: $scope.nonce
      } ).then(
        function(response){
          $scope.licenses.splice( index, 1 );
          LxNotificationService.success(response.data.message);
        },
        function(response){
          showError( response.data.message, true );
        }
      ).then(function(){
        $scope.loading = false;
      });



    };

  }]);


