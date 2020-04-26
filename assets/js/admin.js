/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1);


/***/ }),
/* 1 */
/***/ (function(module, exports) {

jQuery(document).ready(function ($) {
    var pdfDownloader = {
        initDownloadFonts: function initDownloadFonts() {
            $('#ff_download_fonts').text('Downloading...').attr('disabled', true);
            $('.ff_download_loading').html('Please do not close this window when downloading the fonts');
            this.ajaxLoadFonts();
        },
        ajaxLoadFonts: function ajaxLoadFonts() {
            var _this = this;

            jQuery.post(window.fluentform_pdf_admin.ajaxUrl, {
                action: 'fluentform_pdf_admin_ajax_actions',
                route: 'downloadFonts'
            }).then(function (response) {
                if (response.data.downloaded_files && response.data.downloaded_files.length) {
                    $('.ff_download_logs').prepend(response.data.downloaded_files.join('<br />')).show();
                    _this.ajaxLoadFonts();
                } else {
                    // All Done
                    window.location.reload();
                }
            }).fail(function (error) {
                window.location.reload();
            });
        },
        init: function init() {
            var _this2 = this;

            $('#ff_download_fonts').on('click', function (e) {
                e.preventDefault();
                _this2.initDownloadFonts();
            });
        }
    };

    pdfDownloader.init();
});

/***/ })
/******/ ]);