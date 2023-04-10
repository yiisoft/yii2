var assert = require('chai').assert;
var sinon;
var withData = require('leche').withData;
var jsdom = require('mocha-jsdom');

var fs = require('fs');
var vm = require('vm');

describe('yii.gridView', function () {
    var yiiGridViewPath = 'framework/assets/yii.gridView.js';
    var yiiPath = 'framework/assets/yii.js';
    var jQueryPath = 'vendor/bower-asset/jquery/dist/jquery.js';
    var $;
    var $gridView;
    var settings = {
        filterUrl: '/posts/index',
        filterSelector: '#w0-filters input, #w0-filters select',
        filterOnFocusOut: true
    };
    var commonSettings = {
        filterUrl: '/posts/index',
        filterSelector: '#w-common-filters input, #w-common-filters select',
        filterOnFocusOut: true
    };
    var $textInput;
    var $select;
    var $multipleSelect;
    var $listBox;
    var $checkAllCheckbox;
    var $checkRowCheckboxes;

    function registerYii() {
        var code = fs.readFileSync(yiiPath);
        var script = new vm.Script(code);
        var sandbox = {window: window, jQuery: $};
        var context = new vm.createContext(sandbox);
        script.runInContext(context);
        return sandbox.window.yii;
    }

    function registerTestableCode() {
        var yii = registerYii();
        var code = fs.readFileSync(yiiGridViewPath);
        var script = new vm.Script(code);
        var context = new vm.createContext({window: window, document: window.document, yii: yii});
        script.runInContext(context);
    }

    var gridViewHtml = fs.readFileSync('tests/js/data/yii.gridView.html', 'utf-8');
    var html = '<!doctype html><html><head><meta charset="utf-8"></head><body>' + gridViewHtml + '</body></html>';

    jsdom({
        html: html,
        src: fs.readFileSync(jQueryPath, 'utf-8')
    });

    before(function () {
        $ = window.$;
        registerTestableCode();
        sinon = require('sinon');
    });

    beforeEach(function () {
        $textInput = $('#w0-name');
        $select = $('#w0-category');
        $multipleSelect = $('#w0-tags');
        $listBox = $('#w2-tags');
        $checkAllCheckbox = $('#w0-check-all');
        $checkRowCheckboxes = $('.w0-check-row');
    });

    afterEach(function () {
        if ($gridView.length) {
            $gridView.yiiGridView('destroy');
        }
        $textInput.val('');
        $select.val('');
        $multipleSelect.find('option:selected').prop('selected', false);
        $listBox.find('option:selected').prop('selected', false);
        $checkAllCheckbox.prop('checked', false);
        $checkRowCheckboxes.prop('checked', false);
    });

    /**
     * Simulate pressing "Enter" button while focused on some element
     * @param $el
     */
    function pressEnter($el) {
        var e = $.Event('keydown', {keyCode: 13});
        $el.trigger(e);
    }

    /**
     * Simulate pressing keyboard button while focused on the text input. For simplicity, intended to use with letter
     * buttons, such as "a", "b", etc. Case insensitive.
     * @param $el
     * @param buttonName
     */
    function pressButton($el, buttonName) {
        $el.val(buttonName);
        var keyCode = buttonName.charCodeAt(0);
        var e = $.Event('keydown', {keyCode: keyCode});
        $el.trigger(e);
    }

    /**
     * Simulate changing value in the select
     * @param $el
     * @param value
     */
    function changeValue($el, value) {
        $el.val(value);
        var e = $.Event('change');
        $el.trigger(e);
    }

    /**
     * Simulate losing focus of the element after the value was changed
     * @param $el
     */
    function loseFocus($el) {
        var e = $.Event('change');
        $el.trigger(e);
    }

    /**
     * Simulate click in the checkbox
     * @param $el
     */
    function click($el) {
        $el.click();
    }

    /**
     * Simulate hovering on the new value and pressing "Enter" button in the select
     * @param $el
     */
    function hoverAndPressEnter($el) {
        pressEnter($el);
        // After pressing enter while hovering the value will be immediately changed as well like with losing focus
        loseFocus($el);
    }

    describe('init', function () {
        var customSettings = {
            filterUrl: '/posts/filter',
            filterSelector: '#w-common-filters input',
            filterOnFocusOut: true
        };

        withData({
            'no method specified': [function () {
                $gridView = $('.grid-view').yiiGridView(commonSettings);
            }, commonSettings],
            'no method specified, custom settings': [function () {
                $gridView = $('.grid-view').yiiGridView(customSettings);
            }, customSettings],
            'manual method call': [function () {
                $gridView = $('.grid-view').yiiGridView('init', commonSettings);
            }, commonSettings]
        }, function (initFunction, expectedSettings) {
            it('should save settings for all elements', function () {
                initFunction();
                assert.deepEqual($('#w0').yiiGridView('data'), {settings: expectedSettings});
                assert.deepEqual($('#w1').yiiGridView('data'), {settings: expectedSettings});
            });
        });

        describe('with repeated call', function () {
            var jQuerySubmitStub;

            before(function () {
                jQuerySubmitStub = sinon.stub($.fn, 'submit');
            });

            after(function () {
                jQuerySubmitStub.restore();
            });

            it('should remove "filter" event handler', function () {
                $gridView = $('#w0').yiiGridView(settings);
                $gridView.yiiGridView(settings);
                // Change selector to make sure event handlers are removed regardless of the selector
                $gridView.yiiGridView({
                    filterUrl: '/posts/index',
                    filterSelector: '#w0-filters select'
                });

                pressEnter($textInput);
                assert.isFalse(jQuerySubmitStub.called);

                changeValue($select, 1);
                assert.isTrue(jQuerySubmitStub.calledOnce);
            });
        });
    });

    describe('applyFilter', function () {
        var jQuerySubmit = function () {
        };
        var jQuerySubmitStub;

        beforeEach(function () {
            jQuerySubmitStub = sinon.stub($.fn, 'submit', jQuerySubmit);
        });

        afterEach(function () {
            jQuerySubmitStub.restore();
        });

        describe('with beforeFilter returning not false', function () {
            var calledMethods = []; // For testing the order of called methods
            var beforeFilterSpy;
            var afterFilterSpy;

            before(function () {
                jQuerySubmit = function () {
                    calledMethods.push('submit');

                    return this;
                };
                beforeFilterSpy = sinon.spy(function () {
                    calledMethods.push('beforeFilter');
                });
                afterFilterSpy = sinon.spy(function () {
                    calledMethods.push('afterFilter');
                });
            });

            after(function () {
                jQuerySubmit = function () {
                };
                beforeFilterSpy.reset();
                afterFilterSpy.reset();
                calledMethods = [];
            });

            var message = 'should send the request to correct url with correct parameters and apply events in ' +
                'correct order';
            it(message, function () {
                $gridView = $('#w0').yiiGridView(settings)
                    .on('beforeFilter', beforeFilterSpy)
                    .on('afterFilter', afterFilterSpy);

                $textInput.val('a');
                $select.val(1);
                $multipleSelect.find('option[value="1"]').prop('selected', true);
                $multipleSelect.find('option[value="2"]').prop('selected', true);

                $gridView.yiiGridView('applyFilter');

                var expectedHtml = '<form action="/posts/index" method="get" class="gridview-filter-form" ' +
                    'style="display:none" data-pjax="">' +
                    '<input type="hidden" name="PostSearch[name]" value="a">' +
                    '<input type="hidden" name="PostSearch[category_id]" value="1">' +
                    '<input type="hidden" name="PostSearch[tags][]" value="1">' +
                    '<input type="hidden" name="PostSearch[tags][]" value="2">' +
                    '</form>';
                var $form = $('.grid-view .gridview-filter-form');
                assert.equal($form.get(0).outerHTML, expectedHtml);

                assert.isTrue(beforeFilterSpy.calledOnce);
                assert.instanceOf(beforeFilterSpy.getCall(0).args[0], $.Event);
                assert.equal($(beforeFilterSpy.getCall(0).args[0].target).attr('id'), $gridView.attr('id'));

                assert.isTrue(jQuerySubmitStub.calledOnce);
                assert.equal(jQuerySubmitStub.returnValues[0].attr('class'), 'gridview-filter-form');

                assert.isTrue(afterFilterSpy.calledOnce);
                assert.instanceOf(afterFilterSpy.getCall(0).args[0], $.Event);
                assert.equal($(afterFilterSpy.getCall(0).args[0].target).attr('id'), $gridView.attr('id'));

                assert.deepEqual(calledMethods, ['beforeFilter', 'submit', 'afterFilter']);
            });
        });

        describe('with beforeFilter returning false', function () {
            var beforeFilterSpy;
            var afterFilterSpy;

            before(function () {
                beforeFilterSpy = sinon.spy(function () {
                    return false;
                });
                afterFilterSpy = sinon.spy();
            });

            after(function () {
                beforeFilterSpy.reset();
                afterFilterSpy.reset();
            });

            it('should prevent from sending request and triggering "afterFilter" event', function () {
                $gridView = $('#w0').yiiGridView(settings)
                    .on('beforeFilter', beforeFilterSpy)
                    .on('afterFilter', afterFilterSpy);
                $gridView.yiiGridView('applyFilter');

                assert.isTrue(beforeFilterSpy.calledOnce);
                assert.isFalse(jQuerySubmitStub.called);
                assert.isFalse(afterFilterSpy.called);
            });
        });

        describe('with different urls', function () {
            describe('with no filter data sent', function () {
                withData({
                    // https://github.com/yiisoft/yii2/issues/13738
                    'question mark, no query parameters': [
                        '/posts/index?',
                        '/posts/index',
                        'PostSearch[name]=&PostSearch[category_id]='
                    ],
                    'query parameters': [
                        '/posts/index?foo=1&bar=2',
                        '/posts/index',
                        'PostSearch[name]=&PostSearch[category_id]=&foo=1&bar=2'
                    ],
                    // https://github.com/yiisoft/yii2/pull/10302
                    'query parameter with multiple values (not array)': [
                        '/posts/index?foo=1&foo=2',
                        '/posts/index',
                        'PostSearch[name]=&PostSearch[category_id]=&foo=1&foo=2'
                    ],
                    'query parameter with multiple values (array)': [
                        '/posts/index?foo[]=1&foo[]=2',
                        '/posts/index',
                        'PostSearch[name]=&PostSearch[category_id]=&foo[]=1&foo[]=2'
                    ],
                    // https://github.com/yiisoft/yii2/issues/12836
                    'anchor': [
                        '/posts/index#post',
                        '/posts/index#post',
                        'PostSearch[name]=&PostSearch[category_id]='
                    ],
                    'query parameters, anchor': [
                        '/posts/index?foo=1&bar=2#post',
                        '/posts/index#post',
                        'PostSearch[name]=&PostSearch[category_id]=&foo=1&bar=2'
                    ],
                    'relative url, query parameters': [
                        '?foo=1&bar=2',
                        '',
                        'PostSearch[name]=&PostSearch[category_id]=&foo=1&bar=2'
                    ],
                    'relative url, anchor': [
                        '#post',
                        '#post',
                        'PostSearch[name]=&PostSearch[category_id]='
                    ],
                    'relative url, query parameters, anchor': [
                        '?foo=1&bar=2#post',
                        '#post',
                        'PostSearch[name]=&PostSearch[category_id]=&foo=1&bar=2'
                    ]
                }, function (filterUrl, expectedUrl, expectedQueryString) {
                    it('should send the request to correct url with correct parameters', function () {
                        var customSettings = $.extend({}, settings, {filterUrl: filterUrl});
                        $gridView = $('#w0').yiiGridView(customSettings);
                        $gridView.yiiGridView('applyFilter');

                        var $form = $gridView.find('.gridview-filter-form');
                        assert.isTrue(jQuerySubmitStub.calledOnce);
                        assert.equal($form.attr('action'), expectedUrl);
                        assert.equal(decodeURIComponent($form.serialize()), expectedQueryString);
                    });
                });
            });

            // https://github.com/yiisoft/yii2/pull/10302

            describe('with filter data sent', function () {
                it('should send the request to correct url with new parameter values', function () {
                    var filterUrl = '/posts/index?CategorySearch[id]=5&CategorySearch[name]=c' +
                        '&PostSearch[name]=a&PostSearch[category_id]=1&PostSearch[tags][]=1&PostSearch[tags][]=2' +
                        '&foo[]=1&foo[]=2&bar=1#post';
                    var customSettings = $.extend({}, settings, {filterUrl: filterUrl});
                    $gridView = $('#w0').yiiGridView(customSettings);

                    $textInput.val('b');
                    $select.val('1'); // Leave value as is (simulate setting "selected" in HTML)
                    $multipleSelect.find('option[value="2"]').prop('selected', true);
                    $multipleSelect.find('option[value="3"]').prop('selected', true);

                    $gridView.yiiGridView('applyFilter');

                    var $form = $gridView.find('.gridview-filter-form');
                    assert.isTrue(jQuerySubmitStub.calledOnce);
                    assert.equal($form.attr('action'), '/posts/index#post');
                    // Parameters not related with current filter are appended to the end
                    var expectedQueryString = 'PostSearch[name]=b&PostSearch[category_id]=1' +
                        '&PostSearch[tags][]=2&PostSearch[tags][]=3' +
                        '&CategorySearch[id]=5&CategorySearch[name]=c' +
                        '&foo[]=1&foo[]=2&bar=1';
                    assert.equal(decodeURIComponent($form.serialize()), expectedQueryString);
                });
            });
        });

        describe('with list box', function () {
            describe('with values selected', function () {
                it('should send the request to correct url with correct parameters', function () {
                    $listBox.find('option[value="1"]').prop('selected', true);
                    $listBox.find('option[value="2"]').prop('selected', true);

                    $gridView = $('#w2').yiiGridView({
                        filterUrl: '/posts/index',
                        filterSelector: '#w2-filters input, #w2-filters select'
                    });
                    $gridView.yiiGridView('applyFilter');

                    var $form = $gridView.find('.gridview-filter-form');
                    var expectedQueryString = 'PostSearch[name]=&PostSearch[tags]=-1&PostSearch[tags][]=1' +
                        '&PostSearch[tags][]=2';

                    assert.equal($form.attr('action'), '/posts/index');
                    assert.equal(decodeURIComponent($form.serialize()), expectedQueryString);
                });
            });

            // https://github.com/yiisoft/yii2/pull/10284

            describe('with unselected values after applied filter', function () {
                it('should send the request to correct url with correct parameters', function () {
                    $listBox.find('option[value="1"]').prop('selected', true);
                    $listBox.find('option[value="2"]').prop('selected', true);

                    var filterUrl = '/posts/index/?PostSearch[name]=&PostSearch[tags]=-1&PostSearch[tags][]=1' +
                        '&PostSearch[tags][]=2';
                    $gridView = $('#w2').yiiGridView({
                        filterUrl: filterUrl,
                        filterSelector: '#w2-filters input, #w2-filters select'
                    });
                    $listBox.find('option:selected').prop('selected', false);
                    $gridView.yiiGridView('applyFilter');

                    var $form = $gridView.find('.gridview-filter-form');
                    assert.equal($form.attr('action'), '/posts/index/');
                    assert.equal(decodeURIComponent($form.serialize()), 'PostSearch[name]=&PostSearch[tags]=-1');
                });
            });

            // https://github.com/yiisoft/yii2/issues/13379

            describe('with applied pagination', function () {
                it("should correctly change multiple select's data", function () {
                    $listBox.find('option[value="2"]').prop('selected', true);
                    $listBox.find('option[value="3"]').prop('selected', true);

                    var filterUrl = '/posts/index?PostSearch[tags]=-1PostSearch[tags][0]=2&PostSearch[tags][1]=3' +
                        '&page=2&per-page=2';
                    $gridView = $('#w2').yiiGridView({
                        filterUrl: filterUrl,
                        filterSelector: '#w2-filters input, #w2-filters select'
                    });

                    $listBox.find('option[value="4"]').prop('selected', true);

                    $gridView.yiiGridView('applyFilter');

                    var $form = $gridView.find('.gridview-filter-form');
                    var expectedQueryString = 'PostSearch[name]=' +
                        '&PostSearch[tags]=-1&PostSearch[tags][]=2&PostSearch[tags][]=3&PostSearch[tags][]=4' +
                        '&page=2&per-page=2';

                    assert.equal(decodeURIComponent($form.serialize()), expectedQueryString);
                });
            });
        });

        describe('with repeated method call', function () {
            it('should delete the hidden form', function () {
                $gridView = $('#w0').yiiGridView(settings);
                $gridView.yiiGridView('applyFilter');
                $gridView.yiiGridView('applyFilter');

                var $form = $gridView.find('.gridview-filter-form');
                assert.lengthOf($form, 1);
            });
        });

        describe('with filter event handlers', function () {
            beforeEach(function () {
                $gridView = $('#w0').yiiGridView(settings);
            });

            describe('with option filterOnFocusOut', function () {
                it('set option filterOnFocusOut off and press Enter key', function () {
                    $gridView.yiiGridView({
                        filterUrl: '/posts/index',
                        filterSelector: '#w0-filters input, #w0-filters select',
                        filterOnFocusOut: false
                    });

                    pressEnter($textInput);
                    assert.isTrue(jQuerySubmitStub.calledOnce);
                });
                it('set option filterOnFocusOut off and change value', function () {
                    $gridView.yiiGridView({
                        filterUrl: '/posts/index',
                        filterSelector: '#w0-filters input, #w0-filters select',
                        filterOnFocusOut: false
                    });

                    changeValue($select, 1);
                    assert.isFalse(jQuerySubmitStub.calledOnce);
                });
                it('set option filterOnFocusOut off and lose focus', function () {
                    $gridView.yiiGridView({
                        filterUrl: '/posts/index',
                        filterSelector: '#w0-filters input, #w0-filters select',
                        filterOnFocusOut: false
                    });

                    loseFocus($textInput);
                    assert.isFalse(jQuerySubmitStub.calledOnce);
                });
            });

            describe('with text entered in the text input', function () {
                it('should not submit form', function () {
                    pressButton($textInput, 'a');
                    assert.isFalse(jQuerySubmitStub.called);
                });
            });

            describe('with "Enter" pressed in the text input', function () {
                it('should submit form once', function () {
                    pressEnter($textInput);
                    assert.isTrue(jQuerySubmitStub.calledOnce);
                });
            });

            describe('with text entered in the text input and lost focus', function () {
                it('should submit form once', function () {
                    pressButton($textInput, 'a');
                    loseFocus($textInput);

                    assert.isTrue(jQuerySubmitStub.calledOnce);
                });
            });

            describe('with value changed in the select', function () {
                it('should submit form once', function () {
                    changeValue($select, 1);
                    assert.isTrue(jQuerySubmitStub.calledOnce);
                });
            });

            describe('with hover on different value and "Enter" pressed in select', function () {
                it('should submit form once', function () {
                    // Simulate hovering on new value and pressing "Enter"
                    $select.val(1);
                    hoverAndPressEnter($select);

                    assert.isTrue(jQuerySubmitStub.calledOnce);
                });
            });
        });
    });

    describe('setSelectionColumn method', function () {
        describe('with name option and', function () {
            withData({
                'nothing else': [{}],
                'checkAll option': [{checkAll: 'selection_all'}],
                'multiple option set to true': [{multiple: true}],
                'multiple and checkAll options, multiple set to false': [{multiple: false, checkAll: 'selection_all'}]
            }, function (customOptions) {
                it('should update data and do not activate "check all" functionality', function () {
                    $gridView = $('#w0').yiiGridView(settings);

                    var defaultOptions = {name: 'selection[]'};
                    var options = $.extend({}, defaultOptions, customOptions);
                    $gridView.yiiGridView('setSelectionColumn', options);

                    assert.equal($gridView.yiiGridView('data').selectionColumn, 'selection[]');

                    click($checkAllCheckbox);
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 0);

                    click($checkAllCheckbox); // Back to initial condition
                    click($checkRowCheckboxes);
                    assert.isFalse($checkAllCheckbox.prop('checked'));
                });
            });
        });

        describe('with name, multiple and checkAll options, multiple set to true and', function () {
            var changedSpy;

            before(function () {
                changedSpy = sinon.spy();
            });

            after(function () {
                changedSpy.reset();
            });

            withData({
                'nothing else': [{}],
                // https://github.com/yiisoft/yii2/pull/11729
                'class option': [{'class': 'w0-check-row'}]
            }, function (customOptions) {
                it('should update data and "check all" functionality should work', function () {
                    $gridView = $('#w0').yiiGridView(settings);

                    var defaultOptions = {name: 'selection[]', multiple: true, checkAll: 'selection_all'};
                    var options = $.extend({}, defaultOptions, customOptions);
                    $gridView.yiiGridView('setSelectionColumn', options);

                    assert.equal($gridView.yiiGridView('data').selectionColumn, 'selection[]');

                    $checkRowCheckboxes
                        .off('change.yiiGridView') // unbind any subscriptions for clean expectations
                        .on('change.yiiGridView', changedSpy);

                    var $checkFirstRowCheckbox = $checkRowCheckboxes.filter('[value="1"]');

                    // Check all
                    changedSpy.reset();
                    click($checkAllCheckbox);
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 3);
                    assert.isTrue($checkAllCheckbox.prop('checked'));
                    assert.equal(changedSpy.callCount, 3);

                    // Uncheck all
                    changedSpy.reset();
                    click($checkAllCheckbox);
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 0);
                    assert.isFalse($checkAllCheckbox.prop('checked'));
                    assert.equal(changedSpy.callCount, 3);

                    // Check all manually
                    changedSpy.reset();
                    click($checkRowCheckboxes);
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 3);
                    assert.isTrue($checkAllCheckbox.prop('checked'));
                    assert.equal(changedSpy.callCount, 3);

                    // Uncheck all manually
                    changedSpy.reset();
                    click($checkRowCheckboxes);
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 0);
                    assert.isFalse($checkAllCheckbox.prop('checked'));
                    assert.equal(changedSpy.callCount, 3);

                    // Check first row
                    changedSpy.reset();
                    click($checkFirstRowCheckbox);
                    assert.isTrue($checkFirstRowCheckbox.prop('checked'));
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 1);
                    assert.isFalse($checkAllCheckbox.prop('checked'));
                    assert.equal(changedSpy.callCount, 1);

                    // Then check all
                    changedSpy.reset();
                    click($checkAllCheckbox);
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 3);
                    assert.isTrue($checkAllCheckbox.prop('checked'));
                    // "change" should be called 2 more times for the remaining 2 unchecked rows
                    assert.equal(changedSpy.callCount, 2);

                    // Uncheck first row
                    changedSpy.reset();
                    click($checkFirstRowCheckbox);
                    assert.isFalse($checkFirstRowCheckbox.prop('checked'));
                    assert.lengthOf($checkRowCheckboxes.filter(':checked'), 2);
                    assert.isFalse($checkAllCheckbox.prop('checked'));
                    assert.equal(changedSpy.callCount, 1);
                });
            });
        });

        describe('with repeated calls', function () {
            var jQueryPropStub;

            before(function () {
                jQueryPropStub = sinon.stub($, 'prop');
            });

            after(function () {
                jQueryPropStub.restore();
            });

            it('should not duplicate event handler calls', function () {
                $gridView = $('#w3').yiiGridView({
                    filterUrl: '/posts/index',
                    filterSelector: '#w3-filters input, #w3-filters select'
                });

                $gridView.yiiGridView('setSelectionColumn', {
                    name: 'selection[]',
                    multiple: true,
                    checkAll: 'selection_all'
                });
                // Change selectors to make sure event handlers are removed regardless of the selector
                $gridView.yiiGridView('setSelectionColumn', {
                    name: 'selection2[]',
                    multiple: true,
                    checkAll: 'selection_all2'
                });
                $gridView.yiiGridView('setSelectionColumn', {
                    name: 'selection[]',
                    multiple: true,
                    checkAll: 'selection_all'
                });
                $gridView.yiiGridView('setSelectionColumn', {
                    'class': 'w3-check-row',
                    multiple: true,
                    checkAll: 'selection_all'
                });

                // Click first row checkbox ("prop" on "check all" checkbox should not be called)
                click($gridView.find('input[name="selection[]"][value="1"]'));
                // Click "check all" checkbox ("prop" should be called once on the remaining unchecked row)
                click($gridView.find('input[name="selection_all"]'));

                assert.equal(jQueryPropStub.callCount, 1);
            });
        });
    });

    describe('getSelectedRows method', function () {
        withData({
            'selectionColumn not set, no rows selected': [undefined, [], false, []],
            'selectionColumn not set, 1st and 2nd rows selected': [undefined, [1, 2], false, []],
            'selectionColumn set, no rows selected': ['selection[]', [], false, []],
            'selectionColumn set, 1st row selected': ['selection[]', [1], false, [1]],
            'selectionColumn set, 1st and 2nd rows selected': ['selection[]', [1, 2], false, [1, 2]],
            'selectionColumn set, all rows selected, "Check all" checkbox checked': [
                'selection[]', [1, 2, 3], true, [1, 2, 3]
            ]
        }, function (selectionColumn, selectedRows, checkAll, expectedSelectedRows) {
            it('should return array with ids of selected rows', function () {
                $gridView = $('#w0').yiiGridView(settings);
                $gridView.yiiGridView('setSelectionColumn', {name: selectionColumn});
                for (var i = 0; i < selectedRows.length; i++) {
                    $checkRowCheckboxes.filter('[value="' + selectedRows[i] + '"]').prop('checked', true);
                }
                if (checkAll) {
                    $checkAllCheckbox.prop('checked', true);
                }
                assert.deepEqual($gridView.yiiGridView('getSelectedRows'), expectedSelectedRows);
            });
        });
    });

    describe('destroy method', function () {
        var jQuerySubmitStub;
        var jQueryPropStub;
        var beforeFilterSpy;
        var afterFilterSpy;

        beforeEach(function () {
            jQuerySubmitStub = sinon.stub($.fn, 'submit');
            jQueryPropStub = sinon.stub($, 'prop');
            beforeFilterSpy = sinon.spy();
            afterFilterSpy = sinon.spy();
        });

        afterEach(function () {
            jQuerySubmitStub.restore();
            jQueryPropStub.restore();
            beforeFilterSpy.reset();
            afterFilterSpy.reset();
        });

        it('should remove saved settings for destroyed element only and return initial jQuery object', function () {
            $gridView = $('.grid-view').yiiGridView(commonSettings);
            var $gridView1 = $('#w0');
            var $gridView2 = $('#w1');
            var destroyResult = $gridView1.yiiGridView('destroy');

            assert.strictEqual(destroyResult, $gridView1);
            assert.isUndefined($gridView1.yiiGridView('data'));
            assert.deepEqual($gridView2.yiiGridView('data'), {settings: commonSettings});
        });

        it('should remove "beforeFilter" and "afterFilter" event handlers for destroyed element only', function () {
            $gridView = $('.grid-view').yiiGridView(commonSettings)
                .on('beforeFilter', beforeFilterSpy)
                .on('afterFilter', afterFilterSpy);
            var $gridView1 = $('#w0');
            var $gridView2 = $('#w1');
            $gridView1.yiiGridView('destroy');

            assert.throws(function () {
                $gridView1.yiiGridView('applyFilter');
            }, "Cannot read property 'settings' of undefined");
            $gridView1.yiiGridView(settings); // Reinitialize without "beforeFilter" and "afterFilter" event handlers

            $gridView1.yiiGridView('applyFilter');
            assert.isTrue(jQuerySubmitStub.calledOnce);
            assert.isFalse(beforeFilterSpy.called);
            assert.isFalse(afterFilterSpy.called);

            $gridView2.yiiGridView('applyFilter');
            assert.isTrue(jQuerySubmitStub.calledTwice);
            assert.isTrue(beforeFilterSpy.calledOnce);
            assert.isTrue(afterFilterSpy.calledOnce);
        });

        it('should remove "filter" event handler for destroyed element only', function () {
            var $gridView1 = $('#w0');
            var $gridView2 = $('#w1');
            $gridView1.yiiGridView(settings);
            $gridView2.yiiGridView({
                filterUrl: '/posts/index',
                filterSelector: '#w1-filters input, #w1-filters select'
            });
            $gridView2.yiiGridView('destroy');

            pressEnter($gridView2.find('input[name="PostSearch[id]"]'));
            assert.isFalse(jQuerySubmitStub.called);

            pressEnter($textInput);
            assert.isTrue(jQuerySubmitStub.calledOnce);
        });

        it('should remove "checkRow" and "checkAllRows" filter event handlers for destroyed element only', function () {
            $gridView = $('.grid-view').yiiGridView(commonSettings);
            var options = {name: 'selection[]', multiple: true, checkAll: 'selection_all'};
            var $gridView1 = $('#w0');
            var $gridView2 = $('#w1');
            $gridView1.yiiGridView('setSelectionColumn', options);
            $gridView2.yiiGridView('setSelectionColumn', options);
            $gridView2.yiiGridView('destroy');

            click($gridView2.find('input[name="selection_all"]'));
            click($gridView2.find('input[name="selection[]"][value="1"]'));
            assert.equal(jQueryPropStub.callCount, 0);

            click($checkRowCheckboxes.filter('[value="1"]')); // Click first row checkbox ("prop" on "check all" checkbox should not be called)
            click($checkAllCheckbox); // Click "check all" checkbox ("prop" should be called 2 times on the remaining unchecked rows)
            assert.equal(jQueryPropStub.callCount, 2);
        });
    });

    describe('data method', function () {
        it('should return saved settings', function () {
            $gridView = $('#w0').yiiGridView(settings);
            assert.deepEqual($gridView.yiiGridView('data'), {settings: settings});
        });
    });

    describe('call of not existing method', function () {
        it('should throw according error', function () {
            $gridView = $('#w0').yiiGridView(settings);
            assert.throws(function () {
                $gridView.yiiGridView('foobar');
            }, 'Method foobar does not exist in jQuery.yiiGridView');
        });
    });
});
