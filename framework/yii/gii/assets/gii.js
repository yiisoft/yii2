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

			var $checkAll = $('#check-all');
			$checkAll.click(function() {
				$('.code-files .check input').prop('checked', this.checked);
			});
			$('.code-files .check input').click(function() {
				$checkAll.prop('checked', !$('.code-files .check input:not(:checked)').length);
			});
			$checkAll.prop('checked', !$('.code-files .check input:not(:checked)').length);
		}
	};
})(jQuery);
