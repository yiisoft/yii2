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
		// whether to enable client-side (JavaScript) validation
		enableClientValidation: true,
		// whether to enable AJAX-based validation
		enableAjaxValidation: false,
		// the URL for performing AJAX-based validation. If not set, it will use the the form's action
		validationUrl: undefined,
		// number of milliseconds of validation delay. This is used when validateOnType is true.
		validationDelay: 200,
		// whether to perform validation when a change is detected on the input.
		validateOnChange: true,
		// whether to perform validation when the user is typing.
		validateOnType: false,
		// whether to perform validation before submitting the form.
		validateOnSubmit: true,
		// the container CSS class representing the corresponding attribute has validation error
		errorCssClass: 'error',
		// the container CSS class representing the corresponding attribute passes validation
		successCssClass: 'success',
		// the container CSS class representing the corresponding attribute is being validated
		validatingCssClass: 'validating',
		// a callback that is called before validating any attribute
		beforeValidate: undefined,
		// a callback that is called after validating any attribute
		afterValidate: undefined,
		// the GET parameter name indicating an AJAX-based validation
		ajaxVar: 'ajax'
	};

	var methods = {
		/**
		 * Initializes the plugin.
		 * @param attributes array attribute configurations. Each attribute may contain the following options:
		 *
		 * - name: string, attribute name or expression (e.g. "[0]content" for tabular input)
		 * - container: string, the jQuery selector of the container of the input field
		 * - input: string, the jQuery selector of the input field
		 * - error: string, the jQuery selector of the error tag
		 * - value: string|array, the value of the input
		 * - validateOnChange: boolean, whether to perform validation when a change is detected on the input.
		 *   If not set, it will take the value of the corresponding global setting.
		 * - validateOnType: boolean, defaults to false, whether to perform validation when the user is typing.
		 *   If not set, it will take the value of the corresponding global setting.
		 * - enableAjaxValidation: boolean, whether to enable AJAX-based validation.
		 *   If not set, it will take the value of the corresponding global setting.
		 * - enableClientValidation: boolean, whether to enable client-side validation.
		 *   If not set, it will take the value of the corresponding global setting.
		 * - validate: function (attribute, value, messages), the client-side validation function.
		 * - beforeValidate: function ($form, attribute), callback called before validating an attribute. If it
		 *   returns false, the validation will be cancelled.
		 * - afterValidate: function ($form, attribute, data, hasError), callback called after validating an attribute.
		 * - status: integer, 0: empty, not entered before, 1: validated, 2: pending validation, 3: validating
		 *
		 * @param options object the configuration for the plugin. The following options can be set:
		 */
		init: function (attributes, options) {
			return this.each(function () {
				var $form = $(this);
				if ($form.data('yiiActiveForm')) {
					return;
				}

				var settings = $.extend(defaults, options || {});
				if (settings.validationUrl === undefined) {
					settings.validationUrl = $form.attr('action');
				}
				$.each(attributes, function (i) {
					attributes[i] = $.extend({
						validateOnChange: settings.validateOnChange,
						validateOnType: settings.validateOnType,
						enableAjaxValidation: settings.enableAjaxValidation,
						enableClientValidation: settings.enableClientValidation,
						value: getValue($form, this)
					}, this);
				});
				$form.data('yiiActiveForm', {
					settings: settings,
					attributes: attributes,
					submitting: false
				});

				bindAttributes($form, attributes);

				/**
				 * Clean up error status when the form is reset.
				 * Note that $form.on('reset', ...) does work because the "reset" event does not bubble on IE.
				 */
				$form.bind('reset.yiiActiveForm', resetForm);

				if (settings.validateOnSubmit) {
					$form.on('mouseup.yiiActiveForm keyup.yiiActiveForm', ':submit', function () {
						$form.data('yiiActiveForm').submitObject = $(this);
					});
					$form.on('submit', submitForm);
				}
			});
		},

		destroy: function () {
			return this.each(function () {
				$(window).unbind('.yiiActiveForm');
				$(this).removeData('yiiActiveForm');
			});
		}
	};

	var getValue = function ($form, attribute) {
		var $input = findInput($form, attribute);
		var type = $input.attr('type');
		if (type === 'checkbox' || type === 'radio') {
			return $input.filter(':checked').val();
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

	var bindAttributes = function ($form, attributes) {
		$.each(attributes, function (i, attribute) {
			var $input = findInput($form, attribute);
			if (attribute.validateOnChange) {
				$input.on('change.yiiActiveForm', function () {
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
			if (!attribute.beforeValidate || attribute.beforeValidate($form, attribute)) {
				$.each(data.attributes, function () {
					if (this.status === 2) {
						this.status = 3;
						$form.find(this.container).addClass(data.settings.validatingCssClass);
					}
				});
				validateForm($form, function (messages) {
					var hasError = false;
					$.each(data.attributes, function () {
						if (this.status === 2 || this.status === 3) {
							hasError = updateInput($form, this, messages) || hasError;
						}
					});
					if (attribute.afterValidate) {
						attribute.afterValidate($form, attribute, messages, hasError);
					}
				});
			}
		}, data.settings.validationDelay);
	};
	
	/**
	 * Performs the ajax validation request.
	 * This method is invoked internally to trigger the ajax validation.
	 * @param $form jquery the jquery representation of the form
	 * @param successCallback function the function to be invoked if the ajax request succeeds
	 * @param errorCallback function the function to be invoked if the ajax request fails
	 */
	var validateForm = function ($form, successCallback, errorCallback) {
		var data = $form.data('yiiActiveForm'),
			needAjaxValidation = false,
			messages = {};

		$.each(data.attributes, function () {
			var msg = [];
			if (this.validate && (data.submitting || this.status === 2 || this.status === 3)) {
				this.validate(this, getValue($form, this), msg);
				if (msg.length) {
					messages[this.name] = msg;
				}
			}
			if (this.enableAjaxValidation && !msg.length && (data.submitting || this.status === 2 || this.status === 3)) {
				needAjaxValidation = true;
			}
		});

		if (!needAjaxValidation || data.submitting && !$.isEmptyObject(messages)) {
			if (data.submitting) {
				// delay callback so that the form can be submitted without problem
				setTimeout(function () {
					successCallback(messages);
				}, 200);
			} else {
				successCallback(messages);
			}
			return;
		}

		var $button = data.submitObject,
			extData = '&' + data.settings.ajaxVar + '=' + $form.attr('id');
		if ($button && $button.length && $button.attr('name')) {
			extData += '&' + $button.attr('name') + '=' + $button.attr('value');
		}

		$.ajax({
			url: data.settings.validationUrl,
			type: $form.attr('method'),
			data: $form.serialize() + extData,
			dataType: 'json',
			success: function (data) {
				if (data !== null && typeof data === 'object') {
					$.each(data.attributes, function () {
						if (!this.enableAjaxValidation) {
							delete data[this.name];
						}
					});
					successCallback($.extend({}, messages, data));
				} else {
					successCallback(messages);
				}
			},
			error: errorCallback
		});
	};

	var validated = false;
	var submitForm = function () {
		var $form = $(this),
			data = $form.data('yiiActiveForm');
		if (validated) {
			validated = false;
			return true;
		}
		if (data.settings.timer !== undefined) {
			clearTimeout(data.settings.timer);
		}
		data.submitting = true;
		if (!data.settings.beforeValidate || data.settings.beforeValidate($form)) {
			validateForm($form, function (messages) {
				var hasError = false;
				$.each(data.attributes, function () {
					hasError = updateInput($form, this, messages) || hasError;
				});
				updateSummary($form, messages);
				if (!data.settings.afterValidate || data.settings.afterValidate($form, data, hasError)) {
					if (!hasError) {
						validated = true;
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
				}
				data.submitting = false;
			});
		} else {
			data.submitting = false;
		}
		return false;
	};

	var resetForm = function () {
		var $form = $(this);
		var data = $form.data('yiiActiveForm');
		/**
		 * because we bind directly to a form reset event, not to a reset button (that could or could not exist),
		 * when this function is executed form elements values have not been reset yet,
		 * because of that we use the setTimeout
		 */
		setTimeout(function () {
			$.each(data.attributes, function () {
				this.status = 0;
				$form.find(this.container).removeClass(
					data.settings.validatingCssClass + ' ' +
					data.settings.errorCssClass + ' ' +
					data.settings.successCssClass
				);
				$form.find(this.error).html('');
				/*
				 * without the setTimeout() we would get here the current entered value before the reset instead of the reset value
				 */
				this.value = getValue($form, this);
			});
			$form.find(data.settings.summary).hide().find('ul').html('');
		}, 1);
	};

	/**
	 * updates the error message and the input container for a particular attribute.
	 * @param attribute object the configuration for a particular attribute.
	 * @param messages array the json data obtained from the ajax validation request
	 * @param $form the form jQuery object
	 * @return boolean whether there is a validation error for the specified attribute
	 */
	var updateInput = function ($form, attribute, messages) {
		var data = $form.data('yiiActiveForm'),
			$input = findInput($form, attribute),
			hasError = false;

		attribute.status = 1;
		if ($input.length) {
			hasError = messages && $.isArray(messages[attribute.name]) && messages[attribute.name].length;
			var $container = $form.find(attribute.container);
			$container.removeClass(
				data.settings.validatingCssClass + ' ' +
				data.settings.errorCssClass + ' ' +
				data.settings.successCssClass
			);

			if (hasError) {
				$container.find(attribute.error).html(messages[attribute.name][0]);
				$container.addClass(data.settings.errorCssClass);
			} else if (attribute.enableAjaxValidation || attribute.enableClientValidation && attribute.validate) {
				$container.addClass(data.settings.successCssClass);
			}
			attribute.value = getValue($form, attribute);
		}
		return hasError;
	};

	var updateSummary = function ($form, messages) {
		var data = $form.data('yiiActiveForm'),
			$summary = $form.find(data.settings.errorSummary),
			content = '';

		if ($summary.length && messages) {
			$.each(data.attributes, function () {
				if ($.isArray(messages[this.name])) {
					content += '<li>' + messages[this.name].join('</li><li>') + '</li>';
				}
			});
			$summary.toggle(content !== '').find('ul').html(content);
		}
	};

})(window.jQuery);