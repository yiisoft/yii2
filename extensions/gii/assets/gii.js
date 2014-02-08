yii.gii = (function ($) {
	var isActive = $('.default-view').length > 0;

	var initHintBlocks = function () {
		$('.hint-block').each(function () {
			var $hint = $(this);
			$hint.parent().find('label').addClass('help').popover({
				html: true,
				trigger: 'hover',
				placement: 'right',
				content: $hint.html()
			});
		});
	};

	var initStickyInputs = function () {
		$('.sticky:not(.error)').find('input[type="text"],select,textarea').each(function () {
			var value;
			if (this.tagName === 'SELECT') {
				value = this.options[this.selectedIndex].text;
			} else if (this.tagName === 'TEXTAREA') {
				value = $(this).html();
			} else {
				value = $(this).val();
			}
			if (value === '') {
				value = '[empty]';
			}
			$(this).before('<div class="sticky-value">' + value + '</div>').hide();
		});
		$('.sticky-value').on('click', function () {
			$(this).hide();
			$(this).next().show().get(0).focus();
		});
	};

	var initPreviewDiffLinks = function () {
		$('.preview-code, .diff-code, .modal-refresh').on('click', function () {
			var $modal = $('#preview-modal');
			var $link = $(this);
			$modal.find('.modal-refresh').attr('href', $link.prop('href'));
			$modal.find('.modal-title').text($link.data('title'));
			$modal.find('.modal-body').html('Loading ...');
			$modal.modal('show');
			$.ajax({
				type: 'POST',
				cache: false,
				url: $link.prop('href'),
				data: $('.default-view form').serializeArray(),
				success: function (data) {
					$modal.find('.modal-body').html(data);
					$modal.find('.content').css('max-height', ($(window).height() - 200) + 'px');
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$modal.find('.modal-body').html('<div class="error">' + XMLHttpRequest.responseText + '</div>');
				}
			});
			return false;
		});
	};

	var initConfirmationCheckboxes = function () {
		var $checkAll = $('#check-all');
		$checkAll.click(function () {
			$('.default-view-files table .check input').prop('checked', this.checked);
		});
		$('.default-view-files table .check input').click(function () {
			$checkAll.prop('checked', !$('.default-view-files table .check input:not(:checked)').length);
		});
		$checkAll.prop('checked', !$('.default-view-files table .check input:not(:checked)').length);
	};

	return {
		init: function () {
			initHintBlocks();
			initStickyInputs();
			initPreviewDiffLinks();
			initConfirmationCheckboxes();

			// model generator: hide class name input when table name input contains *
			$('#model-generator #generator-tablename').on('change', function () {
				$('#model-generator .field-generator-modelclass').toggle($(this).val().indexOf('*') == -1);
			}).change();

			// hide Generate button if any input is changed
			$('.default-view .form-group input,select,textarea').change(function () {
				$('.default-view-results,.default-view-files').hide();
				$('.default-view button[name="generate"]').hide();
			});

			$('.module-form #generator-moduleclass').change(function () {
				var value = $(this).val().match(/(\w+)\\\w+$/);
				var $idInput = $('#generator-moduleid');
				if (value && value[1] && $idInput.val() == '') {
					$idInput.val(value[1]);
				}
			});
		}
	};
})(jQuery);
