/**
 * Yii JavaScript module.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
yii = (function ($) {
	var pub = {
		version: '2.0'
	};
	return pub;
})(jQuery);

jQuery(document).ready(function ($) {
	// call the init() method of every module
	var init = function (module) {
		if ($.isFunction(module.init) && (module.trigger == undefined || $(module.trigger).length)) {
			module.init();
		}
		$.each(module, function () {
			if ($.isPlainObject(this)) {
				init(this);
			}
		});
	};
	init(yii);
});
