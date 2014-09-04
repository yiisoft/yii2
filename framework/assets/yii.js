/**
 * Yii JavaScript module.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */

/**
 * yii is the root module for all Yii JavaScript modules.
 * It implements a mechanism of organizing JavaScript code in modules through the function "yii.initModule()".
 *
 * Each module should be named as "x.y.z", where "x" stands for the root module (for the Yii core code, this is "yii").
 *
 * A module may be structured as follows:
 *
 * ~~~
 * yii.sample = (function($) {
 *     var pub = {
 *         // whether this module is currently active. If false, init() will not be called for this module
 *         // it will also not be called for all its child modules. If this property is undefined, it means true.
 *         isActive: true,
 *         init: function() {
 *             // ... module initialization code go here ...
 *         },
 *
 *         // ... other public functions and properties go here ...
 *     };
 *
 *     // ... private functions and properties go here ...
 *
 *     return pub;
 * })(jQuery);
 * ~~~
 *
 * Using this structure, you can define public and private functions/properties for a module.
 * Private functions/properties are only visible within the module, while public functions/properties
 * may be accessed outside of the module. For example, you can access "yii.sample.isActive".
 *
 * You must call "yii.initModule()" once for the root module of all your modules.
 */
yii = (function ($) {
    var pub = {
        /**
         * List of scripts that can be loaded multiple times via AJAX requests. Each script can be represented
         * as either an absolute URL or a relative one.
         */
        reloadableScripts: [],
        /**
         * The selector for clickable elements that need to support confirmation and form submission.
         */
        clickableSelector: 'a, button, input[type="submit"], input[type="button"], input[type="reset"], input[type="image"]',
        /**
         * The selector for changeable elements that need to support confirmation and form submission.
         */
        changeableSelector: 'select, input, textarea',

        /**
         * @return string|undefined the CSRF parameter name. Undefined is returned if CSRF validation is not enabled.
         */
        getCsrfParam: function () {
            return $('meta[name=csrf-param]').prop('content');
        },

        /**
         * @return string|undefined the CSRF token. Undefined is returned if CSRF validation is not enabled.
         */
        getCsrfToken: function () {
            return $('meta[name=csrf-token]').prop('content');
        },

        /**
         * Sets the CSRF token in the meta elements.
         * This method is provided so that you can update the CSRF token with the latest one you obtain from the server.
         * @param name the CSRF token name
         * @param value the CSRF token value
         */
        setCsrfToken: function (name, value) {
            $('meta[name=csrf-param]').prop('content', name);
            $('meta[name=csrf-token]').prop('content', value)
        },

        /**
         * Updates all form CSRF input fields with the latest CSRF token.
         * This method is provided to avoid cached forms containing outdated CSRF tokens.
         */
        refreshCsrfToken: function () {
            var token = pub.getCsrfToken();
            if (token) {
                $('form input[name="' + pub.getCsrfParam() + '"]').val(token);
            }
        },

        /**
         * Displays a confirmation dialog.
         * The default implementation simply displays a js confirmation dialog.
         * You may override this by setting `yii.confirm`.
         * @param message the confirmation message.
         * @return boolean whether the user confirms with the message in the dialog
         */
        confirm: function (message) {
            return confirm(message);
        },

        /**
         * Returns a value indicating whether to allow executing the action defined for the specified element.
         * This method recognizes the `data-confirm` attribute of the element and uses it
         * as the message in a confirmation dialog. The method will return true if this special attribute
         * is not defined or if the user confirms the message.
         * @param $e the jQuery representation of the element
         * @return boolean whether to allow executing the action defined for the specified element.
         */
        allowAction: function ($e) {
            var message = $e.data('confirm');
            return message === undefined || pub.confirm(message);
        },

        /**
         * Handles the action triggered by user.
         * This method recognizes the `data-method` attribute of the element. If the attribute exists,
         * the method will submit the form containing this element. If there is no containing form, a form
         * will be created and submitted using the method given by this attribute value (e.g. "post", "put").
         * For hyperlinks, the form action will take the value of the "href" attribute of the link.
         * For other elements, either the containing form action or the current page URL will be used
         * as the form action URL.
         *
         * If the `data-method` attribute is not defined, the default element action will be performed.
         *
         * @param $e the jQuery representation of the element
         * @return boolean whether to execute the default action for the element.
         */
        handleAction: function ($e) {
            var method = $e.data('method');
            if (method === undefined) {
                return true;
            }

            var $form = $e.closest('form');
            var action = $e.attr('href');
            var newForm = !$form.length || action && action != '#';
            if (newForm) {
                if (!action || !action.match(/(^\/|:\/\/)/)) {
                    action = window.location.href;
                }
                $form = $('<form method="' + method + '" action="' + action + '"></form>');
                var target = $e.prop('target');
                if (target) {
                    $form.attr('target', target);
                }
                if (!method.match(/(get|post)/i)) {
                    $form.append('<input name="_method" value="' + method + '" type="hidden">');
                    method = 'POST';
                }
                if (!method.match(/(get|head|options)/i)) {
                    var csrfParam = pub.getCsrfParam();
                    if (csrfParam) {
                        $form.append('<input name="' + csrfParam + '" value="' + pub.getCsrfToken() + '" type="hidden">');
                    }
                }
                $form.hide().appendTo('body');
            }

            var activeFormData = $form.data('yiiActiveForm');
            if (activeFormData) {
                // remember who triggers the form submission. This is used by yii.activeForm.js
                activeFormData.submitObject = $e;
            }

            var oldMethod = $form.prop('method');
            $form.prop('method', method);

            $form.trigger('submit');

            $form.prop('method', oldMethod);

            if (newForm) {
                $form.remove();
            }

            return false;
        },

        getQueryParams: function (url) {
            var pos = url.indexOf('?');
            if (pos < 0) {
                return {};
            }
            var qs = url.substring(pos + 1).split('&');
            for (var i = 0, result = {}; i < qs.length; i++) {
                qs[i] = qs[i].split('=');
                result[decodeURIComponent(qs[i][0])] = decodeURIComponent(qs[i][1]);
            }
            return result;
        },

        initModule: function (module) {
            if (module.isActive === undefined || module.isActive) {
                if ($.isFunction(module.init)) {
                    module.init();
                }
                $.each(module, function () {
                    if ($.isPlainObject(this)) {
                        pub.initModule(this);
                    }
                });
            }
        },

        init: function () {
            initCsrfHandler();
            initRedirectHandler();
            initScriptFilter();
            initDataMethods();
        }
    };

    function initRedirectHandler() {
        // handle AJAX redirection
        $(document).ajaxComplete(function (event, xhr, settings) {
            var url = xhr.getResponseHeader('X-Redirect');
            if (url) {
                window.location = url;
            }
        });
    }

    function initCsrfHandler() {
        // automatically send CSRF token for all AJAX requests
        $.ajaxPrefilter(function (options, originalOptions, xhr) {
            if (!options.crossDomain && pub.getCsrfParam()) {
                xhr.setRequestHeader('X-CSRF-Token', pub.getCsrfToken());
            }
        });
        pub.refreshCsrfToken();
    }

    function initDataMethods() {
        var $document = $(document);
        // handle data-confirm and data-method for clickable elements
        $document.on('click.yii', pub.clickableSelector, function (event) {
            var $this = $(this);
            if (pub.allowAction($this)) {
                return pub.handleAction($this);
            } else {
                event.stopImmediatePropagation();
                return false;
            }
        });

        // handle data-confirm and data-method for changeable elements
        $document.on('change.yii', pub.changeableSelector, function (event) {
            var $this = $(this);
            if (pub.allowAction($this)) {
                return pub.handleAction($this);
            } else {
                event.stopImmediatePropagation();
                return false;
            }
        });
    }

    function initScriptFilter() {
        var hostInfo = location.protocol + '//' + location.host;
        var loadedScripts = $('script[src]').map(function () {
            return this.src.charAt(0) === '/' ? hostInfo + this.src : this.src;
        }).toArray();
        $.ajaxPrefilter('script', function (options, originalOptions, xhr) {
            if (options.dataType == 'jsonp') {
                return;
            }
            var url = options.url.charAt(0) === '/' ? hostInfo + options.url : options.url;
            if ($.inArray(url, loadedScripts) === -1) {
                loadedScripts.push(url);
            } else {
                var found = $.inArray(url, $.map(pub.reloadableScripts, function (script) {
                    return script.charAt(0) === '/' ? hostInfo + script : script;
                })) !== -1;
                if (!found) {
                    xhr.abort();
                }
            }
        });
    }

    return pub;
})(jQuery);

jQuery(document).ready(function () {
    yii.initModule(yii);
});
