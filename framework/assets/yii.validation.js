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
				messages.push(options.message);
			}
		},

		boolean: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}
			var valid = !options.strict && (value == options.trueValue || value == options.falseValue)
				|| options.strict && (value === options.trueValue || value === options.falseValue);

			if (!valid) {
				messages.push(options.message);
			}
		},

		string: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (typeof value !== 'string') {
				messages.push(options.message);
				return;
			}

			if (options.min !== undefined && value.length < options.min) {
				messages.push(options.tooShort);
			}
			if (options.max !== undefined && value.length > options.max) {
				messages.push(options.tooLong);
			}
			if (options.is !== undefined && value.length != options.is) {
				messages.push(options.is);
			}
		},

		number: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (typeof value === 'string' && !value.match(options.pattern)) {
				messages.push(options.message);
				return;
			}

			if (options.min !== undefined && value < options.min) {
				messages.push(options.tooSmall);
			}
			if (options.max !== undefined && value > options.max) {
				messages.push(options.tooBig);
			}
		},

		range: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}
			var valid = !options.not && $.inArray(value, options.range)
				|| options.not && !$.inArray(value, options.range);

			if (!valid) {
				messages.push(options.message);
			}
		},

		regularExpression: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (!options.not && !value.match(options.pattern) || options.not && value.match(options.pattern)) {
				messages.push(options.message)
			}
		},

		email: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			var valid = value.match(options.pattern) && (!options.allowName || value.match(options.fullPattern));

			if (!valid) {
				messages.push(options.message);
			}
		},

		url: function (value, messages, options) {
			if (options.skipOnEmpty && isEmpty(value)) {
				return;
			}

			if (options.defaultScheme && !value.match(/:\/\//)) {
				value = options.defaultScheme + '://' + value;
			}

			if (!value.match(options.pattern)) {
				messages.push(options.message);
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
				messages.push(options.message);
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
			}

			if (!valid) {
				messages.push(options.message);
			}
		}
	};
})(jQuery);
