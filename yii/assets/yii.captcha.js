/**
 * Yii Captcha widget.
 *
 * This is the JavaScript widget used by the yii\captcha\Captcha widget.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
(function ($) {
	$.fn.yiiCaptcha = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.yiiCaptcha');
			return false;
		}
	};

	var defaults = {
		refreshUrl: undefined,
		hashKey: undefined
	};

	var methods = {
		init: function (options) {
			return this.each(function () {
				var $e = $(this);
				var settings = $.extend({}, defaults, options || {});
				$e.data('yiiCaptcha', {
					settings: settings
				});

				$e.on('click.yiiCaptcha', function() {
					methods.refresh.apply($e);
					return false;
				});

			});
		},

		refresh: function () {
			var $e = this,
				settings = this.data('yiiCaptcha').settings;
			$.ajax({
				url: $e.data('yiiCaptcha').settings.refreshUrl,
				dataType: 'json',
				cache: false,
				success: function(data) {
					$e.attr('src', data.url);
					$('body').data(settings.hashKey, [data.hash1, data.hash2]);
				}
			});
		},

		destroy: function () {
			return this.each(function () {
				$(window).unbind('.yiiCaptcha');
				$(this).removeData('yiiCaptcha');
			});
		},

		data: function() {
			return this.data('yiiCaptcha');
		}
	};
})(window.jQuery);

