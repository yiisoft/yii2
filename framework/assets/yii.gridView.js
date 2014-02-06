/**
 * Yii GridView widget.
 *
 * This is the JavaScript widget used by the yii\grid\GridView widget.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
(function ($) {
	$.fn.yiiGridView = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.yiiGridView');
			return false;
		}
	};

	var defaults = {
		filterUrl: undefined,
		filterSelector: undefined
	};

	var methods = {
		init: function (options) {
			return this.each(function () {
				var $e = $(this);
				var settings = $.extend({}, defaults, options || {});
				$e.data('yiiGridView', {
					settings: settings
				});

				var enterPressed = false;
				$(settings.filterSelector).on('change.yiiGridView keydown.yiiGridView', function (event) {
					if (event.type === 'keydown') {
						if (event.keyCode !== 13) {
							return; // only react to enter key
						} else {
							enterPressed = true;
						}
					} else {
						// prevent processing for both keydown and change events
						if (enterPressed) {
							enterPressed = false;
							return;
						}
					}

					methods.applyFilter.apply($e);

					return false;
				});
			});
		},

		applyFilter: function () {
			var $grid = $(this);
			var settings = $grid.data('yiiGridView').settings;
			var data = {};
			$.each($(settings.filterSelector).serializeArray(), function () {
				data[this.name] = this.value;
			});

			$.each(yii.getQueryParams(settings.filterUrl), function (name, value) {
				if (data[name] === undefined) {
					data[name] = value;
				}
			});

			var pos = settings.filterUrl.indexOf('?');
			var url = pos < 0 ? settings.filterUrl : settings.filterUrl.substring(0, pos);

			$grid.find('form.gridview-filter-form').remove();
			var $form = $('<form action="' + url + '" method="get" class="gridview-filter-form" style="display:none" data-pjax></form>').appendTo($grid);
			$.each(data, function (name, value) {
				$form.append($('<input type="hidden" name="t" value="" />').attr('name', name).val(value));
			});
			$form.submit();
		},

		setSelectionColumn: function (options) {
			var $grid = $(this);
			var data = $grid.data('yiiGridView');
			data.selectionColumn = options.name;
			if (!options.multiple) {
				return;
			}
			$grid.on('click.yiiGridView', "input[name='" + options.checkAll + "']", function () {
				$grid.find("input[name='" + options.name + "']:enabled").prop('checked', this.checked);
			});
			$grid.on('click.yiiGridView', "input[name='" + options.name + "']:enabled", function () {
				var all = $grid.find("input[name='" + options.name + "']").length == $grid.find("input[name='" + options.name + "']:checked").length;
				$grid.find("input[name='" + options.checkAll + "']").prop('checked', all);
			});
		},

		getSelectedRows: function () {
			var $grid = $(this);
			var data = $grid.data('yiiGridView');
			var keys = [];
			if (data.selectionColumn) {
				$grid.find("input[name='" + data.selectionColumn + "']:checked").each(function () {
					keys.push($(this).parent().closest('tr').data('key'));
				});
			}
			return keys;
		},

		destroy: function () {
			return this.each(function () {
				$(window).unbind('.yiiGridView');
				$(this).removeData('yiiGridView');
			});
		},

		data: function() {
			return this.data('yiiGridView');
		}
	};
})(window.jQuery);

