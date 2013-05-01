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
		// whether it is waiting for ajax submission result
		submitting: false
	};

	var methods = {
		/**
		 * Initializes the plugin.
		 * @param attributes array attribute configurations. Each attribute may contain the following options:
		 *
		 * - id: 'ModelClass_attribute', // the unique attribute ID
		 * - model: 'ModelClass', // the model class name
		 * - name: 'name', // attribute name
		 * - inputID: 'input-tag-id',
		 * - errorID: 'error-tag-id',
		 * - value: undefined,
		 * - status: 0,  // 0: empty, not entered before,  1: validated, 2: pending validation, 3: validating
		 * - validationDelay: 200,
		 * - validateOnChange: true,
		 * - validateOnType: false,
		 * - hideErrorMessage: false,
		 * - inputContainer: undefined,
		 * - errorCssClass: 'error',
		 * - successCssClass: 'success',
		 * - validatingCssClass: 'validating',
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
					this.value = getInputValue($form.find('#' + this.inputID));
					attributes[i] = $.extend(settings, this);
				});
				$form.data('yiiActiveForm', {
					settings: settings,
					attributes: attributes
				});

				var validate = function (attribute, forceValidate) {
					if (forceValidate) {
						attribute.status = 2;
					}
					$.each(settings.attributes, function () {
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

				$.each(settings.attributes, function (i, attribute) {
					if (this.validateOnChange) {
						$form.find('#' + this.inputID).change(function () {
							validate(attribute, false);
						}).blur(function () {
								if (attribute.status !== 2 && attribute.status !== 3) {
									validate(attribute, !attribute.status);
								}
							});
					}
					if (this.validateOnType) {
						$form.find('#' + this.inputID).keyup(function () {
							if (attribute.value !== getAFValue($(this))) {
								validate(attribute, false);
							}
						});
					}
				});

				if (settings.validateOnSubmit) {
					$form.on('mouseup keyup', ':submit', function () {
						$form.data('submitObject', $(this));
					});
					var validated = false;
					$form.submit(function () {
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
					});
				}

				/*
				 * In case of reseting the form we need to reset error messages
				 * NOTE1: $form.reset - does not exist
				 * NOTE2: $form.on('reset', ...) does not work
				 */
				$form.bind('reset', function () {
					/*
					 * because we bind directly to a form reset event, not to a reset button (that could or could not exist),
					 * when this function is executed form elements values have not been reset yet,
					 * because of that we use the setTimeout
					 */
					setTimeout(function () {
						$.each(settings.attributes, function () {
							this.status = 0;
							var $error = $form.find('#' + this.errorID),
								$container = $.fn.yiiactiveform.getInputContainer(this, $form);

							$container.removeClass(
								this.validatingCssClass + ' ' +
									this.errorCssClass + ' ' +
									this.successCssClass
							);

							$error.html('').hide();

							/*
							 * without the setTimeout() we would get here the current entered value before the reset instead of the reseted value
							 */
							this.value = getAFValue($form.find('#' + this.inputID));
						});
						/*
						 * If the form is submited (non ajax) with errors, labels and input gets the class 'error'
						 */
						$form.find('label, input').each(function () {
							$(this).removeClass(settings.errorCss);
						});
						$('#' + settings.summaryID).hide().find('ul').html('');
						//.. set to initial focus on reset
						if (settings.focus !== undefined && !window.location.hash) {
							$form.find(settings.focus).focus();
						}
					}, 1);
				});

				/*
				 * set to initial focus
				 */
				if (settings.focus !== undefined && !window.location.hash) {
					$form.find(settings.focus).focus();
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
	 */
	var getInputValue = function ($e) {
		var type,
			c = [];
		if (!$e.length) {
			return undefined;
		}
		if ($e[0].tagName.toLowerCase() === 'span') {
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

	/**
	 * Performs the ajax validation request.
	 * This method is invoked internally to trigger the ajax validation.
	 * @param form jquery the jquery representation of the form
	 * @param successCallback function the function to be invoked if the ajax request succeeds
	 * @param errorCallback function the function to be invoked if the ajax request fails
	 */
	var validate = function (form, successCallback, errorCallback) {
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

})(window.jQuery);