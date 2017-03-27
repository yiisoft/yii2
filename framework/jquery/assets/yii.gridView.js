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
            $.error('Method ' + method + ' does not exist in jQuery.yiiGridView');
            return false;
        }
    };

    var defaults = {
        filterUrl: undefined,
        filterSelector: undefined
    };

    var gridData = {};

    var gridEvents = {
        /**
         * beforeFilter event is triggered before filtering the grid.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         *
         * If the handler returns a boolean false, it will stop filter form submission after this event. As
         * a result, afterFilter event will not be triggered.
         */
        beforeFilter: 'beforeFilter',
        /**
         * afterFilter event is triggered after filtering the grid and filtered results are fetched.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         */
        afterFilter: 'afterFilter'
    };

    /**
     * Used for storing active event handlers and removing them later.
     * The structure of single event handler is:
     *
     * {
     *     gridViewId: {
     *         type: {
     *             event: '...',
     *             selector: '...'
     *         }
     *     }
     * }
     *
     * Used types:
     *
     * - filter, used for filtering grid with elements found by filterSelector
     * - checkRow, used for checking single row
     * - checkAllRows, used for checking all rows with according "Check all" checkbox
     *
     * event is the name of event, for example: 'change.yiiGridView'
     * selector is a jQuery selector for finding elements
     *
     * @type {{}}
     */
    var gridEventHandlers = {};

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                var id = $e.attr('id');
                if (gridData[id] === undefined) {
                    gridData[id] = {};
                }

                gridData[id] = $.extend(gridData[id], {settings: settings});

                var filterEvents = 'change.yiiGridView keydown.yiiGridView';
                var enterPressed = false;
                initEventHandler($e, 'filter', filterEvents, settings.filterSelector, function (event) {
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
            var settings = gridData[$grid.attr('id')].settings;
            var data = {};
            $.each($(settings.filterSelector).serializeArray(), function () {
                if (!(this.name in data)) {
                    data[this.name] = [];
                }
                data[this.name].push(this.value);
            });

            var namesInFilter = Object.keys(data);

            $.each(yii.getQueryParams(settings.filterUrl), function (name, value) {
                if (namesInFilter.indexOf(name) === -1 && namesInFilter.indexOf(name.replace(/\[\d*\]$/, '')) === -1) {
                    if (!$.isArray(value)) {
                        value = [value];
                    }
                    if (!(name in data)) {
                        data[name] = value;
                    } else {
                        $.each(value, function (i, val) {
                            if ($.inArray(val, data[name])) {
                                data[name].push(val);
                            }
                        });
                    }
                }
            });

            var pos = settings.filterUrl.indexOf('?');
            var url = pos < 0 ? settings.filterUrl : settings.filterUrl.substring(0, pos);
            var hashPos = settings.filterUrl.indexOf('#');
            if (pos >= 0 && hashPos >= 0) {
                url += settings.filterUrl.substring(hashPos);
            }

            $grid.find('form.gridview-filter-form').remove();
            var $form = $('<form/>', {
                action: url,
                method: 'get',
                'class': 'gridview-filter-form',
                style: 'display:none',
                'data-pjax': ''
            }).appendTo($grid);
            $.each(data, function (name, values) {
                $.each(values, function (index, value) {
                    $form.append($('<input/>').attr({type: 'hidden', name: name, value: value}));
                });
            });

            var event = $.Event(gridEvents.beforeFilter);
            $grid.trigger(event);
            if (event.result === false) {
                return;
            }

            $form.submit();

            $grid.trigger(gridEvents.afterFilter);
        },

        setSelectionColumn: function (options) {
            var $grid = $(this);
            var id = $(this).attr('id');
            if (gridData[id] === undefined) {
                gridData[id] = {};
            }
            gridData[id].selectionColumn = options.name;
            if (!options.multiple || !options.checkAll) {
                return;
            }
            var checkAll = "#" + id + " input[name='" + options.checkAll + "']";
            var inputs = options['class'] ? "input." + options['class'] : "input[name='" + options.name + "']";
            var inputsEnabled = "#" + id + " " + inputs + ":enabled";
            initEventHandler($grid, 'checkAllRows', 'click.yiiGridView', checkAll, function () {
                $grid.find(inputs + ":enabled").prop('checked', this.checked);
            });
            initEventHandler($grid, 'checkRow', 'click.yiiGridView', inputsEnabled, function () {
                var all = $grid.find(inputs).length == $grid.find(inputs + ":checked").length;
                $grid.find("input[name='" + options.checkAll + "']").prop('checked', all);
            });
        },

        getSelectedRows: function () {
            var $grid = $(this);
            var data = gridData[$grid.attr('id')];
            var keys = [];
            if (data.selectionColumn) {
                $grid.find("input[name='" + data.selectionColumn + "']:checked").each(function () {
                    keys.push($(this).parent().closest('tr').data('key'));
                });
            }
            return keys;
        },

        destroy: function () {
            var events = ['.yiiGridView', gridEvents.beforeFilter, gridEvents.afterFilter].join(' ');
            this.off(events);

            var id = $(this).attr('id');
            $.each(gridEventHandlers[id], function (type, data) {
                $(document).off(data.event, data.selector);
            });

            delete gridData[id];

            return this;
        },

        data: function () {
            var id = $(this).attr('id');
            return gridData[id];
        }
    };

    /**
     * Used for attaching event handler and prevent of duplicating them. With each call previously attached handler of
     * the same type is removed even selector was changed.
     * @param {jQuery} $gridView According jQuery grid view element
     * @param {string} type Type of the event which acts like a key
     * @param {string} event Event name, for example 'change.yiiGridView'
     * @param {string} selector jQuery selector
     * @param {function} callback The actual function to be executed with this event
     */
    function initEventHandler($gridView, type, event, selector, callback) {
        var id = $gridView.attr('id');
        var prevHandler = gridEventHandlers[id];
        if (prevHandler !== undefined && prevHandler[type] !== undefined) {
            var data = prevHandler[type];
            $(document).off(data.event, data.selector);
        }
        if (prevHandler === undefined) {
            gridEventHandlers[id] = {};
        }
        $(document).on(event, selector, callback);
        gridEventHandlers[id][type] = {event: event, selector: selector};
    }
})(window.jQuery);
