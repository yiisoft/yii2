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
		// the container CSS class representing the corresponding attribute has validation error
		errorCssClass: 'error',
		// the container CSS class representing the corresponding attribute passes validation
 		successCssClass: 'success',
		// the container CSS class representing the corresponding attribute is being validated
		validatingCssClass: 'validating',
		// whether it is waiting for ajax submission result
		submitting: false
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
		 * - status: integer, 0: empty, not entered before, 1: validated, 2: pending validation, 3: validating
		 * - validationDelay: 200,
		 * - validateOnChange: true,
		 * - validateOnType: false,
		 * - hideErrorMessage: false,
		 * - inputContainer: undefined,
		 * - enableAjaxValidation: true,
		 * - enableClientValidation: true,
		 * - clientValidation: undefined, // function (value, messages, attribute) | client-side validation
		 * - beforeValidateAttribute: undefined, // function (form, attribute) | boolean
		 * - afterValidateAttribute: undefined,  // function (form, attribute, data, hasError)
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
					this.value = getInputValue($form.find(this.inputSelector));
					attributes[i] = $.extend(settings, this);
				});
				$form.data('yiiActiveForm', {
					settings: settings,
					attributes: attributes
				});

				bindAttributes(attributes);

				/**
				 * Clean up error status when resetting the form.
				 * Note that neither $form.reset(...) nor $form.on('reset', ...) works.
				 */
				$form.bind('reset', resetForm);

				if (settings.validateOnSubmit) {
					$form.on('mouseup keyup', ':submit', function () {
						$form.data('submitObject', $(this));
					});
					var validated = false;
					$form.submit(submitForm);
				}
			});
		},

		destroy: function () {
			return this.each(function () {
				$(window).unbind('.yiiActiveForm');
				$(this).removeData('yiiActiveForm');
			})
		}
	};

	/**
	 * Returns the value of the specified input element.
	 * This method will perform additional checks to get proper values
	 * for checkbox, radio, checkbox list and radio list.
	 * @param $e jQuery the jQuery object of the input element
	 * @return string the input value
	 */
	var getInputValue = function ($input) {
		// TBD
		var type,
			c = [];
		if (!$e.length) {
			return undefined;
		}
		if ($e[0].tagName.toLowerCase() === 'div') {
			$e.find(':checked').each(function () {
				c.push(this.value);
			});
			return c.join(',');
		}
		type = $e.attr('type');
		if (type === 'checkbox' || type === 'radio') {
			return $e.filter(':checked').val();
		} else {
			return $e.val();
		}
	};

	var findInput = function ($form, attribute) {
		var $e = $form.find(attribute.inputSelector);
		if (!$e.length) {
			return undefined;
		}
		if ($e[0].tagName.toLowerCase() === 'div') {
			// checkbox list or radio list
			return $e.find('input');
		} else {
			return $e;
		}
	};

	var bindAttributes = function (attributes) {
		$.each(attributes, function (i, attribute) {
			if (this.validateOnChange) {
				$form.find(this.inputSelector).change(function () {
					validateAttribute(attribute, false);
				}).blur(function () {
					if (attribute.status !== 2 && attribute.status !== 3) {
						validateAttribute(attribute, !attribute.status);
					}
				});
			}
			if (this.validateOnType) {
				$form.find(this.inputSelector).keyup(function () {
					if (attribute.value !== getAFValue($(this))) {
						validateAttribute(attribute, false);
					}
				});
			}
		});
	};

	/**
	 * Performs the ajax validation request.
	 * This method is invoked internally to trigger the ajax validation.
	 * @param form jquery the jquery representation of the form
	 * @param successCallback function the function to be invoked if the ajax request succeeds
	 * @param errorCallback function the function to be invoked if the ajax request fails
	 */
	var validateForm = function (form, successCallback, errorCallback) {
		var $form = $(form),
			settings = $form.data('settings'),
			needAjaxValidation = false,
			messages = {};
		$.each(settings.attributes, function () {
			var value,
				msg = [];
			if (this.clientValidation !== undefined && (settings.submitting || this.status === 2 || this.status === 3)) {
				value = getInputValue($form.find('#' + this.inputID));
				this.clientValidation(value, msg, this);
				if (msg.length) {
					messages[this.id] = msg;
				}
			}
			if (this.enableAjaxValidation && !msg.length && (settings.submitting || this.status === 2 || this.status === 3)) {
				needAjaxValidation = true;
			}
		});

		if (!needAjaxValidation || settings.submitting && !$.isEmptyObject(messages)) {
			if (settings.submitting) {
				// delay callback so that the form can be submitted without problem
				setTimeout(function () {
					successCallback(messages);
				}, 200);
			} else {
				successCallback(messages);
			}
			return;
		}

		var $button = $form.data('submitObject'),
			extData = '&' + settings.ajaxVar + '=' + $form.attr('id');
		if ($button && $button.length) {
			extData += '&' + $button.attr('name') + '=' + $button.attr('value');
		}

		$.ajax({
			url: settings.validationUrl,
			type: $form.attr('method'),
			data: $form.serialize() + extData,
			dataType: 'json',
			success: function (data) {
				if (data !== null && typeof data === 'object') {
					$.each(settings.attributes, function () {
						if (!this.enableAjaxValidation) {
							delete data[this.id];
						}
					});
					successCallback($.extend({}, messages, data));
				} else {
					successCallback(messages);
				}
			},
			error: function () {
				if (errorCallback !== undefined) {
					errorCallback();
				}
			}
		});
	};

	var validateAttribute = function (attribute, forceValidate) {
		if (forceValidate) {
			attribute.status = 2;
		}
		$.each(attributes, function () {
			if (this.value !== getInputValue($form.find('#' + this.inputID))) {
				this.status = 2;
				forceValidate = true;
			}
		});
		if (!forceValidate) {
			return;
		}

		if (settings.timer !== undefined) {
			clearTimeout(settings.timer);
		}
		settings.timer = setTimeout(function () {
			if (settings.submitting || $form.is(':hidden')) {
				return;
			}
			if (attribute.beforeValidateAttribute === undefined || attribute.beforeValidateAttribute($form, attribute)) {
				$.each(settings.attributes, function () {
					if (this.status === 2) {
						this.status = 3;
						$.fn.yiiactiveform.getInputContainer(this, $form).addClass(this.validatingCssClass);
					}
				});
				$.fn.yiiactiveform.validate($form, function (data) {
					var hasError = false;
					$.each(settings.attributes, function () {
						if (this.status === 2 || this.status === 3) {
							hasError = $.fn.yiiactiveform.updateInput(this, data, $form) || hasError;
						}
					});
					if (attribute.afterValidateAttribute !== undefined) {
						attribute.afterValidateAttribute($form, attribute, data, hasError);
					}
				});
			}
		}, attribute.validationDelay);
	};

	var submitForm = function () {
		if (validated) {
			validated = false;
			return true;
		}
		if (settings.timer !== undefined) {
			clearTimeout(settings.timer);
		}
		settings.submitting = true;
		if (settings.beforeValidate === undefined || settings.beforeValidate($form)) {
			$.fn.yiiactiveform.validate($form, function (data) {
				var hasError = false;
				$.each(settings.attributes, function () {
					hasError = $.fn.yiiactiveform.updateInput(this, data, $form) || hasError;
				});
				$.fn.yiiactiveform.updateSummary($form, data);
				if (settings.afterValidate === undefined || settings.afterValidate($form, data, hasError)) {
					if (!hasError) {
						validated = true;
						var $button = $form.data('submitObject') || $form.find(':submit:first');
						// TODO: if the submission is caused by "change" event, it will not work
						if ($button.length) {
							$button.click();
						} else {  // no submit button in the form
							$form.submit();
						}
						return;
					}
				}
				settings.submitting = false;
			});
		} else {
			settings.submitting = false;
		}
		return false;
	};

	var resetForm = function () {
		var settings = $(this).data('yiiActiveForm').settings;
		var attributes = $(this).data('yiiActiveForm').attributes;
		/*
		 * because we bind directly to a form reset event, not to a reset button (that could or could not exist),
		 * when this function is executed form elements values have not been reset yet,
		 * because of that we use the setTimeout
		 */
		setTimeout(function () {
			$.each(attributes, function () {
				this.status = 0;
				var $error = $form.find('#' + this.errorID),
					$container = getInputContainer(this, $form);

				$container.removeClass(
					settings.validatingCssClass + ' ' +
					settings.errorCssClass + ' ' +
					settings.successCssClass
				);

				$error.html('').hide();

				/*
				 * without the setTimeout() we would get here the current entered value before the reset instead of the reseted value
				 */
				this.value = getInputValue($form.find('#' + this.inputID));
			});
			$('#' + settings.summaryID).hide().find('ul').html('');
		}, 10);
	};


	/**
	 * Returns the container element of the specified attribute.
	 * @param attribute object the configuration for a particular attribute.
	 * @param form the form jQuery object
	 * @return jQuery the jQuery representation of the container
	 */
	var getInputContainer = function (attribute, form) {
		if (attribute.inputContainer === undefined) {
			return form.find('#' + attribute.inputID).closest('div');
		} else {
			return form.find(attribute.inputContainer).filter(':has("#' + attribute.inputID + '")');
		}
	};

	/**
	 * updates the error message and the input container for a particular attribute.
	 * @param attribute object the configuration for a particular attribute.
	 * @param messages array the json data obtained from the ajax validation request
	 * @param form the form jQuery object
	 * @return boolean whether there is a validation error for the specified attribute
	 */
	var updateInput = function (attribute, messages, form) {
		attribute.status = 1;
		var $error, $container,
			hasError = false,
			$el = form.find('#' + attribute.inputID),
			errorCss = form.data('settings').errorCss;

		if ($el.length) {
			hasError = messages !== null && $.isArray(messages[attribute.id]) && messages[attribute.id].length > 0;
			$error = form.find('#' + attribute.errorID);
			$container = $.fn.yiiactiveform.getInputContainer(attribute, form);

			$container.removeClass(
				attribute.validatingCssClass + ' ' +
					attribute.errorCssClass + ' ' +
					attribute.successCssClass
			);
			$container.find('label, input').each(function () {
				$(this).removeClass(errorCss);
			});

			if (hasError) {
				$error.html(messages[attribute.id][0]);
				$container.addClass(attribute.errorCssClass);
			} else if (attribute.enableAjaxValidation || attribute.clientValidation) {
				$container.addClass(attribute.successCssClass);
			}
			if (!attribute.hideErrorMessage) {
				$error.toggle(hasError);
			}

			attribute.value = getAFValue($el);
		}
		return hasError;
	};

	/**
	 * updates the error summary, if any.
	 * @param form jquery the jquery representation of the form
	 * @param messages array the json data obtained from the ajax validation request
	 */
	var updateSummary = function (form, messages) {
		var settings = $(form).data('yiiActiveForm'),
			content = '';
		if (settings.summaryID === undefined) {
			return;
		}
		if (messages) {
			$.each(settings.attributes, function () {
				if ($.isArray(messages[this.id])) {
					$.each(messages[this.id], function (j, message) {
						content = content + '<li>' + message + '</li>';
					});
				}
			});
		}
		$('#' + settings.summaryID).toggle(content !== '').find('ul').html(content);
	};

	var getSettings = function (form) {
		return $(form).data('yiiActiveForm').settings;
	};

	var getAttributes = function (form) {
		return $(form).data('yiiActiveForm').attributes;
	};

})(window.jQuery);