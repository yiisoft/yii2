/**
 * Yii form widget.
 *
 * This is the JavaScript widget used by the yii\widgets\ActiveForm widget.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
(function ($) {

    $.fn.yiiActiveForm = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.yiiActiveForm');
            return false;
        }
    };

    var defaults = {
        // the jQuery selector for the error summary
        errorSummary: undefined,
        // whether to perform validation before submitting the form.
        validateOnSubmit: true,
        // the container CSS class representing the corresponding attribute has validation error
        errorCssClass: 'error',
        // the container CSS class representing the corresponding attribute passes validation
        successCssClass: 'success',
        // the container CSS class representing the corresponding attribute is being validated
        validatingCssClass: 'validating',
        // the URL for performing AJAX-based validation. If not set, it will use the the form's action
        validationUrl: undefined,
        // a callback that is called before submitting the form. The signature of the callback should be:
        // function ($form) { ...return false to cancel submission...}
        beforeSubmit: undefined,
        // a callback that is called before validating each attribute. The signature of the callback should be:
        // function ($form, attribute, messages) { ...return false to cancel the validation...}
        beforeValidate: undefined,
        // a callback that is called after an attribute is validated. The signature of the callback should be:
        // function ($form, attribute, messages)
        afterValidate: undefined,
        // the GET parameter name indicating an AJAX-based validation
        ajaxParam: 'ajax',
        // the type of data that you're expecting back from the server
        ajaxDataType: 'json'
    };

    var attributeDefaults = {
        // attribute name or expression (e.g. "[0]content" for tabular input)
        name: undefined,
        // the jQuery selector of the container of the input field
        container: undefined,
        // the jQuery selector of the input field
        input: undefined,
        // the jQuery selector of the error tag
        error: undefined,
        // whether to perform validation when a change is detected on the input
        validateOnChange: false,
        // whether to perform validation when the user is typing.
        validateOnType: false,
        // number of milliseconds that the validation should be delayed when a user is typing in the input field.
        validationDelay: 200,
        // whether to enable AJAX-based validation.
        enableAjaxValidation: false,
        // function (attribute, value, messages), the client-side validation function.
        validate: undefined,
        // status of the input field, 0: empty, not entered before, 1: validated, 2: pending validation, 3: validating
        status: 0,
        // the value of the input
        value: undefined
    };

    var methods = {
        init: function (attributes, options) {
            return this.each(function () {
                var $form = $(this);
                if ($form.data('yiiActiveForm')) {
                    return;
                }

                var settings = $.extend({}, defaults, options || {});
                if (settings.validationUrl === undefined) {
                    settings.validationUrl = $form.prop('action');
                }
                $.each(attributes, function (i) {
                    attributes[i] = $.extend({value: getValue($form, this)}, attributeDefaults, this);
                });
                $form.data('yiiActiveForm', {
                    settings: settings,
                    attributes: attributes,
                    submitting: false,
                    validated: false
                });

                watchAttributes($form, attributes);

                /**
                 * Clean up error status when the form is reset.
                 * Note that $form.on('reset', ...) does work because the "reset" event does not bubble on IE.
                 */
                $form.bind('reset.yiiActiveForm', methods.resetForm);

                if (settings.validateOnSubmit) {
                    $form.on('mouseup.yiiActiveForm keyup.yiiActiveForm', ':submit', function () {
                        $form.data('yiiActiveForm').submitObject = $(this);
                    });
                    $form.on('submit', methods.submitForm);
                }
            });
        },

        destroy: function () {
            return this.each(function () {
                $(window).unbind('.yiiActiveForm');
                $(this).removeData('yiiActiveForm');
            });
        },

        data: function () {
            return this.data('yiiActiveForm');
        },

        submitForm: function () {
            var $form = $(this),
                data = $form.data('yiiActiveForm');
            if (data.validated) {
                if (data.settings.beforeSubmit !== undefined) {
                    if (data.settings.beforeSubmit($form) == false) {
                        data.validated = false;
                        data.submitting = false;
                        return false;
                    }
                }
                // continue submitting the form since validation passes
                return true;
            }

            if (data.settings.timer !== undefined) {
                clearTimeout(data.settings.timer);
            }
            data.submitting = true;
            validate($form, function (messages) {
                var errors = [];
                $.each(data.attributes, function () {
                    if (updateInput($form, this, messages)) {
                        errors.push(this.input);
                    }
                });
                updateSummary($form, messages);
                if (errors.length) {
                    var top = $form.find(errors.join(',')).first().offset().top;
                    var wtop = $(window).scrollTop();
                    if (top < wtop || top > wtop + $(window).height) {
                        $(window).scrollTop(top);
                    }
                } else {
                    data.validated = true;
                    var $button = data.submitObject || $form.find(':submit:first');
                    // TODO: if the submission is caused by "change" event, it will not work
                    if ($button.length) {
                        $button.click();
                    } else {
                        // no submit button in the form
                        $form.submit();
                    }
                    return;
                }
                data.submitting = false;
            }, function () {
                data.submitting = false;
            });
            return false;
        },

        resetForm: function () {
            var $form = $(this);
            var data = $form.data('yiiActiveForm');
            // Because we bind directly to a form reset event instead of a reset button (that may not exist),
            // when this function is executed form input values have not been reset yet.
            // Therefore we do the actual reset work through setTimeout.
            setTimeout(function () {
                $.each(data.attributes, function () {
                    // Without setTimeout() we would get the input values that are not reset yet.
                    this.value = getValue($form, this);
                    this.status = 0;
                    var $container = $form.find(this.container);
                    $container.removeClass(
                        data.settings.validatingCssClass + ' ' +
                            data.settings.errorCssClass + ' ' +
                            data.settings.successCssClass
                    );
                    $container.find(this.error).html('');
                });
                $form.find(data.settings.summary).hide().find('ul').html('');
            }, 1);
        }
    };

    var watchAttributes = function ($form, attributes) {
        $.each(attributes, function (i, attribute) {
            var $input = findInput($form, attribute);
            if (attribute.validateOnChange) {
                $input.on('change.yiiActiveForm',function () {
                    validateAttribute($form, attribute, false);
                }).on('blur.yiiActiveForm', function () {
                    if (attribute.status == 0 || attribute.status == 1) {
                        validateAttribute($form, attribute, !attribute.status);
                    }
                });
            }
            if (attribute.validateOnType) {
                $input.on('keyup.yiiActiveForm', function () {
                    if (attribute.value !== getValue($form, attribute)) {
                        validateAttribute($form, attribute, false);
                    }
                });
            }
        });
    };

    var validateAttribute = function ($form, attribute, forceValidate) {
        var data = $form.data('yiiActiveForm');

        if (forceValidate) {
            attribute.status = 2;
        }
        $.each(data.attributes, function () {
            if (this.value !== getValue($form, this)) {
                this.status = 2;
                forceValidate = true;
            }
        });
        if (!forceValidate) {
            return;
        }

        if (data.settings.timer !== undefined) {
            clearTimeout(data.settings.timer);
        }
        data.settings.timer = setTimeout(function () {
            if (data.submitting || $form.is(':hidden')) {
                return;
            }
            $.each(data.attributes, function () {
                if (this.status === 2) {
                    this.status = 3;
                    $form.find(this.container).addClass(data.settings.validatingCssClass);
                }
            });
            validate($form, function (messages) {
                var hasError = false;
                $.each(data.attributes, function () {
                    if (this.status === 2 || this.status === 3) {
                        hasError = updateInput($form, this, messages) || hasError;
                    }
                });
            });
        }, data.settings.validationDelay);
    };

    /**
     * Performs validation.
     * @param $form jQuery the jquery representation of the form
     * @param successCallback function the function to be invoked if the validation completes
     * @param errorCallback function the function to be invoked if the ajax validation request fails
     */
    var validate = function ($form, successCallback, errorCallback) {
        var data = $form.data('yiiActiveForm'),
            needAjaxValidation = false,
            messages = {};

        $.each(data.attributes, function () {
            if (data.submitting || this.status === 2 || this.status === 3) {
                var msg = [];
                if (!data.settings.beforeValidate || data.settings.beforeValidate($form, this, msg)) {
                    if (this.validate) {
                        this.validate(this, getValue($form, this), msg);
                    }
                    if (msg.length) {
                        messages[this.name] = msg;
                    } else if (this.enableAjaxValidation) {
                        needAjaxValidation = true;
                    }
                }
            }
        });

        if (needAjaxValidation && (!data.submitting || $.isEmptyObject(messages))) {
            // Perform ajax validation when at least one input needs it.
            // If the validation is triggered by form submission, ajax validation
            // should be done only when all inputs pass client validation
            var $button = data.submitObject,
                extData = '&' + data.settings.ajaxParam + '=' + $form.prop('id');
            if ($button && $button.length && $button.prop('name')) {
                extData += '&' + $button.prop('name') + '=' + $button.prop('value');
            }
            $.ajax({
                url: data.settings.validationUrl,
                type: $form.prop('method'),
                data: $form.serialize() + extData,
                dataType: data.settings.ajaxDataType,
                success: function (msgs) {
                    if (msgs !== null && typeof msgs === 'object') {
                        $.each(data.attributes, function () {
                            if (!this.enableAjaxValidation) {
                                delete msgs[this.name];
                            }
                        });
                        successCallback($.extend({}, messages, msgs));
                    } else {
                        successCallback(messages);
                    }
                },
                error: errorCallback
            });
        } else if (data.submitting) {
            // delay callback so that the form can be submitted without problem
            setTimeout(function () {
                successCallback(messages);
            }, 200);
        } else {
            successCallback(messages);
        }
    };

    /**
     * Updates the error message and the input container for a particular attribute.
     * @param $form the form jQuery object
     * @param attribute object the configuration for a particular attribute.
     * @param messages array the validation error messages
     * @return boolean whether there is a validation error for the specified attribute
     */
    var updateInput = function ($form, attribute, messages) {
        var data = $form.data('yiiActiveForm'),
            $input = findInput($form, attribute),
            hasError = false;

        if (data.settings.afterValidate) {
            data.settings.afterValidate($form, attribute, messages);
        }
        attribute.status = 1;
        if ($input.length) {
            hasError = messages && $.isArray(messages[attribute.name]) && messages[attribute.name].length;
            var $container = $form.find(attribute.container);
            var $error = $container.find(attribute.error);
            if (hasError) {
                $error.text(messages[attribute.name][0]);
                $container.removeClass(data.settings.validatingCssClass + ' ' + data.settings.successCssClass)
                    .addClass(data.settings.errorCssClass);
            } else {
                $error.text('');
                $container.removeClass(data.settings.validatingCssClass + ' ' + data.settings.errorCssClass + ' ')
                    .addClass(data.settings.successCssClass);
            }
            attribute.value = getValue($form, attribute);
        }
        return hasError;
    };

    /**
     * Updates the error summary.
     * @param $form the form jQuery object
     * @param messages array the validation error messages
     */
    var updateSummary = function ($form, messages) {
        var data = $form.data('yiiActiveForm'),
            $summary = $form.find(data.settings.errorSummary),
            $ul = $summary.find('ul').html('');

        if ($summary.length && messages) {
            $.each(data.attributes, function () {
                if ($.isArray(messages[this.name]) && messages[this.name].length) {
                    $ul.append($('<li/>').text(messages[this.name][0]));
                }
            });
            $summary.toggle($ul.find('li').length > 0);
        }
    };

    var getValue = function ($form, attribute) {
        var $input = findInput($form, attribute);
        var type = $input.prop('type');
        if (type === 'checkbox' || type === 'radio') {
            var $realInput = $input.filter(':checked');
            if (!$realInput.length) {
                $realInput = $form.find('input[type=hidden][name="' + $input.prop('name') + '"]');
            }
            return $realInput.val();
        } else {
            return $input.val();
        }
    };

    var findInput = function ($form, attribute) {
        var $input = $form.find(attribute.input);
        if ($input.length && $input[0].tagName.toLowerCase() === 'div') {
            // checkbox list or radio list
            return $input.find('input');
        } else {
            return $input;
        }
    };

})(window.jQuery);
