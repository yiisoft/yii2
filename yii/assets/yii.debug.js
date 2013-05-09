/**
 * Yii debug module.
 *
 * This JavaScript module provides the functions needed by the Yii debug toolbar.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */

yii.debug = (function ($) {
	return {
		load: function (id, url) {
			$.ajax({
				url: url,
				//dataType: 'json',
				success: function(data) {
					var $e = $('#' + id);
					$e.html(data);
				}
			});
		}
	};
})(jQuery);
