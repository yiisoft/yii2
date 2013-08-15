yii.gii = (function ($) {
	return {
		init: function () {
			$('.hint-block').each(function() {
				var $hint = $(this);
				$hint.parent().find('input,select,textarea').popover({
					html: true,
					trigger: 'focus',
					placement: 'right',
					content: $hint.html()
				});
			});
		}
	};
})(jQuery);
