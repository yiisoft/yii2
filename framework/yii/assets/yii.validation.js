/**
 * Yii validation module.
 *
 * This JavaScript module provides the validation methods for the built-in validators.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */

yii.validation = (function ($) {
	var isEmpty = function (value, trim) {
		return value === null || value === undefined || value == []
			|| value === '' || trim && $.trim(value) === '';
	};

	var addMessage = function (messages, message, value) {
		messages.push(message.replace(/\{value\}/g, value));
	};

	return {
		required: function (value, messages, options) {
			var valid = false;
			if (options.requiredValue === undefined) {
				if (options.strict && value !== undefined || !options.strict && !isEmpty(value, true)) {
					valid = true;
				}
			} else if (!options.strict && value == options.requiredValue || options.strict && value === options.requiredValue) {
				valid = true;
			}

			if (!valid) {
				addMessage(messages, options.message, value);
			}
		},

		boolean: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}
			var valid = !options.strict && (value == options.trueValue || value == options.falseValue)
				|| options.strict && (value === options.trueValue || value === options.falseValue);

			if (!valid) {
				addMessage(messages, options.message, value);
			}
		},

		string: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (typeof value !== 'string') {
				addMessage(messages, options.message, value);
				return;
			}

			if (options.min !== undefined && value.length < options.min) {
				addMessage(messages, options.tooShort, value);
			}
			if (options.max !== undefined && value.length > options.max) {
				addMessage(messages, options.tooLong, value);
			}
			if (options.is !== undefined && value.length != options.is) {
				addMessage(messages, options.is, value);
			}
		},

		number: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (typeof value === 'string' && !value.match(options.pattern)) {
				addMessage(messages, options.message, value);
				return;
			}

			if (options.min !== undefined && value < options.min) {
				addMessage(messages, options.tooSmall, value);
			}
			if (options.max !== undefined && value > options.max) {
				addMessage(messages, options.tooBig, value);
			}
		},

		range: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}
			var valid = !options.not && $.inArray(value, options.range) > -1
				|| options.not && $.inArray(value, options.range) == -1;

			if (!valid) {
				addMessage(messages, options.message, value);
			}
		},

		regularExpression: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (!options.not && !value.match(options.pattern) || options.not && value.match(options.pattern)) {
				addMessage(messages, options.message, value);
			}
		},

		email: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			var valid = true;

			if (options.enableIDN) {
				var regexp = /^(.*<?)(.*)@(.*)(>?)$/,
					matches = regexp.exec(value);
				if (matches === null) {
					valid = false;
				} else {
					value = matches[1] + punycode.toASCII(matches[2]) + '@' + punycode.toASCII(matches[3]) + matches[4];
				}
			}

			if (!valid || !(value.match(options.pattern) || (options.allowName && value.match(options.fullPattern)))) {
				addMessage(messages, options.message, value);
			}
		},

		url: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (options.defaultScheme && !value.match(/:\/\//)) {
				value = options.defaultScheme + '://' + value;
			}

			var valid = true;

			if (options.enableIDN) {
				var regexp = /^([^:]+):\/\/([^\/]+)(.*)$/,
					matches = regexp.exec(value);
				if (matches === null) {
					valid = false;
				} else {
					value = matches[1] + '://' + punycode.toASCII(matches[2]) + matches[3];
				}
			}

			if (!valid || !value.match(options.pattern)) {
				addMessage(messages, options.message, value);
			}
		},

		captcha: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			// CAPTCHA may be updated via AJAX and the updated hash is stored in body data
			var hash = $('body').data(options.hashKey);
			if (hash == null) {
				hash = options.hash;
			} else {
				hash = hash[options.caseSensitive ? 0 : 1];
			}
			var v = options.caseSensitive ? value : value.toLowerCase();
			for (var i = v.length - 1, h = 0; i >= 0; --i) {
				h += v.charCodeAt(i);
			}
			if (h != hash) {
				addMessage(messages, options.message, value);
			}
		},

		compare: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			var compareValue, valid = true;
			if (options.compareAttribute === undefined) {
				compareValue = options.compareValue;
			} else {
				compareValue = $('#' + options.compareAttribute).val();
			}
			switch (options.operator) {
				case '==':
					valid = value == compareValue;
					break;
				case '===':
					valid = value === compareValue;
					break;
				case '!=':
					valid = value != compareValue;
					break;
				case '!==':
					valid = value !== compareValue;
					break;
				case '>':
					valid = value > compareValue;
					break;
				case '>=':
					valid = value >= compareValue;
					break;
				case '<':
					valid = value < compareValue;
					break;
				case '<=':
					valid = value <= compareValue;
					break;
				default:
					valid = false;
					break;
			}

			if (!valid) {
				addMessage(messages, options.message, value);
			}
		}
	};
})(jQuery);
