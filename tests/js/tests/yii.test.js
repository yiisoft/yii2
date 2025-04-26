var assert = require('chai').assert;
var sinon;
var withData = require('leche').withData;
var jsdom = require('mocha-jsdom');

var fs = require('fs');
var vm = require('vm');

var StringUtils = {
    /**
     * Removes line breaks and redundant whitespaces from the given string. Used to compare HTML strings easier,
     * regardless of the formatting.
     * @param str Initial string to clean
     * @returns {string} Cleaned string
     */
    cleanHTML: function (str) {
        return str.replace(/\r?\n|\r|\s\s+/g, '');
    }
};

describe('yii', function () {
    var yiiPath = 'framework/assets/yii.js';
    var jQueryPath = 'vendor/bower-asset/jquery/dist/jquery.js';
    var pjaxPath = 'vendor/bower-asset/yii2-pjax/jquery.pjax.js';
    var sandbox;
    var $;
    var yii;
    var yiiGetBaseCurrentUrlStub;
    var yiiGetCurrentUrlStub;

    function registerPjax() {
        var code = fs.readFileSync(pjaxPath);
        var script = new vm.Script(code);
        var sandbox = {jQuery: $, window: window, navigator: window.navigator};
        var context = new vm.createContext(sandbox);
        script.runInContext(context);
    }

    function registerTestableCode() {
        registerPjax();

        var code = fs.readFileSync(yiiPath);
        var script = new vm.Script(code);
        sandbox = {window: window, document: window.document, XMLHttpRequest: window.XMLHttpRequest};
        var context = new vm.createContext(sandbox);

        script.runInContext(context);
        yii = sandbox.window.yii;
    }

    /**
     * Mapping of pjax data attributes with according plugin options
     * @type {{}}
     */
    var pjaxAttributes = {
        'data-pjax-push-state': 'push',
        'data-pjax-replace-state': 'replace',
        'data-pjax-scrollto': 'scrollTo',
        'data-pjax-push-redirect': 'pushRedirect',
        'data-pjax-replace-redirect': 'replaceRedirect',
        'data-pjax-skip-outer-containers': 'skipOuterContainers',
        'data-pjax-timeout': 'timeout'
    };

    /**
     * Add pjax related attributes to all elements with "data-pjax" attribute. Used to prevent copy pasting and for
     * better readability of the test HTML data.
     */
    function addPjaxAttributes() {
        $.each(pjaxAttributes, function (name, value) {
            $('[data-pjax]').attr(name, value);
        });
    }

    jsdom({
        html: fs.readFileSync('tests/js/data/yii.html', 'utf-8'),
        src: fs.readFileSync(jQueryPath, 'utf-8'),
        url: "http://foo.bar"
    });

    before(function () {
        $ = window.$;
        registerTestableCode();
        sinon = require('sinon');
        addPjaxAttributes();
        yiiGetBaseCurrentUrlStub = sinon.stub(yii, 'getBaseCurrentUrl', function () {
            return 'http://foo.bar';
        });
        yiiGetCurrentUrlStub = sinon.stub(yii, 'getCurrentUrl', function () {
            return 'http://foo.bar/';
        });
    });

    after(function () {
        yiiGetBaseCurrentUrlStub.restore();
        yiiGetCurrentUrlStub.restore();
    });

    describe('getCsrfParam method', function () {
        it('should return current CSRF parameter name', function () {
            assert.equal(yii.getCsrfParam(), '_csrf');
        });
    });

    describe('getCsrfToken method', function () {
        it('should return current CSRF parameter value', function () {
            assert.equal(yii.getCsrfToken(), 'foobar');
        });
    });

    describe('CSRF modifying methods', function () {
        var initialCsrfParam;
        var initialCsrfToken;

        beforeEach(function () {
            initialCsrfParam = $('meta[name="csrf-param"]').attr('content');
            initialCsrfToken = $('meta[name="csrf-token"]').attr('content');
        });

        // Restore CSRF parameter name and value to initial values because they are used in different tests

        afterEach(function () {
            $('meta[name="csrf-param"]').attr('content', initialCsrfParam);
            $('meta[name="csrf-token"]').attr('content', initialCsrfToken);
        });

        describe('setCsrfToken method', function () {
            it('should update CSRF parameter name and value with new values', function () {
                yii.setCsrfToken('_csrf1', 'foobar1');

                assert.equal(yii.getCsrfParam(), '_csrf1');
                assert.equal(yii.getCsrfToken(), 'foobar1');
            });
        });

        describe('refreshCsrfToken method', function () {
            it('should assign CSRF token values for all forms during initialization', function () {
                assert.equal($('#form1').find('input[name="_csrf"]').val(), 'foobar');
                assert.equal($('#form2').find('input[name="_csrf"]').val(), 'foobar');
            });

            it('should update CSRF token values for all forms after modifying current CSRF token value', function () {
                $('meta[name="csrf-token"]').attr('content', 'foobar1');
                yii.refreshCsrfToken();

                assert.equal($('#form1').find('input[name="_csrf"]').val(), 'foobar1');
                assert.equal($('#form2').find('input[name="_csrf"]').val(), 'foobar1');
            });
        });
    });

    describe('confirm method', function () {
        var windowConfirmStub;
        var confirmed;
        var okSpy;
        var cancelSpy;

        beforeEach(function () {
            windowConfirmStub = sinon.stub(window, 'confirm', function () {
                return confirmed;
            });
            okSpy = sinon.spy();
            cancelSpy = sinon.spy();
        });

        afterEach(function () {
            windowConfirmStub.restore();
            okSpy.reset();
            cancelSpy.reset();
        });

        withData({
            'ok and cancel not set, "OK" selected': [{
                setOk: false,
                setCancel: false,
                confirmChoice: true,
                expectOkCalled: false,
                expectCancelCalled: false
            }],
            'ok and cancel not set, "Cancel" selected': [{
                setOk: false,
                setCancel: false,
                confirmChoice: false,
                expectOkCalled: false,
                expectCancelCalled: false
            }],
            'ok set, "OK" selected': [{
                setOk: true,
                setCancel: false,
                confirmChoice: true,
                expectOkCalled: true,
                expectCancelCalled: false
            }],
            'ok set, "Cancel" selected': [{
                setOk: true,
                setCancel: false,
                confirmChoice: false,
                expectOkCalled: false,
                expectCancelCalled: false
            }],
            'cancel set, "OK" selected': [{
                setOk: false,
                setCancel: true,
                confirmChoice: true,
                expectOkCalled: false,
                expectCancelCalled: false
            }],
            'cancel set, "Cancel" selected': [{
                setOk: false,
                setCancel: true,
                confirmChoice: false,
                expectOkCalled: false,
                expectCancelCalled: true
            }],
            'ok and cancel set, "OK" selected': [{
                setOk: true,
                setCancel: true,
                confirmChoice: true,
                expectOkCalled: true,
                expectCancelCalled: false
            }],
            'ok and cancel set, "Cancel" selected': [{
                setOk: true,
                setCancel: true,
                confirmChoice: false,
                expectOkCalled: false,
                expectCancelCalled: true
            }]
        }, function (data) {
            var setOk = data.setOk;
            var setCancel = data.setCancel;
            var confirmChoice = data.confirmChoice;
            var expectOkCalled = data.expectOkCalled;
            var expectCancelCalled = data.expectCancelCalled;

            var message = 'should return undefined, confirm should be called once with according message, ';
            if (expectOkCalled && !expectCancelCalled) {
                message += 'ok callback should be called once';
            } else if (!expectOkCalled && expectCancelCalled) {
                message += 'cancel callback should be called once';
            } else if (!expectOkCalled && !expectCancelCalled) {
                message += 'ok and cancel callbacks should not be called';
            } else {
                message += 'ok and cancel callbacks should be called once';
            }

            it(message, function () {
                confirmed = confirmChoice;

                var result = yii.confirm('Are you sure?', setOk ? okSpy : undefined, setCancel ? cancelSpy : undefined);

                assert.isUndefined(result);
                assert.isTrue(windowConfirmStub.calledOnce);
                assert.deepEqual(windowConfirmStub.getCall(0).args, ['Are you sure?']);
                expectOkCalled ? assert.isTrue(okSpy.calledOnce) : assert.isFalse(okSpy.called);
                expectCancelCalled ? assert.isTrue(cancelSpy.calledOnce) : assert.isFalse(cancelSpy.called);
            });
        });
    });

    describe('handleAction method', function () {
        var windowLocationAssignStub;
        var pjaxClickStub;
        var pjaxSubmitStub;
        var formSubmitsCount;
        var initialFormsCount;
        var $savedSubmittedForm;

        beforeEach(function () {
            windowLocationAssignStub = sinon.stub(window.location, 'assign');
            pjaxClickStub = sinon.stub($.pjax, 'click');
            pjaxSubmitStub = sinon.stub($.pjax, 'submit');
            initialFormsCount = $('form').length;
            countFormSubmits();
        });

        afterEach(function () {
            windowLocationAssignStub.restore();
            pjaxClickStub.restore();
            pjaxSubmitStub.restore();
            formSubmitsCount = undefined;
            initialFormsCount = undefined;
            $savedSubmittedForm = undefined;
            $(document).off('submit');
            $('form').off('submit');
        });

        function countFormSubmits() {
            formSubmitsCount = 0;
            $(document).on('submit', 'form', function () {
                formSubmitsCount++;
                $savedSubmittedForm = $(this).clone();

                return false;
            });
        }

        function verifyNoActions() {
            assert.isFalse(windowLocationAssignStub.called);
            assert.isFalse(pjaxClickStub.called);

            assert.equal(formSubmitsCount, 0);
            assert.isFalse(pjaxSubmitStub.called);
            assert.equal($('form').length, initialFormsCount);
        }

        function verifyPageLoad(url) {
            assert.isTrue(windowLocationAssignStub.calledOnce);
            assert.deepEqual(windowLocationAssignStub.getCall(0).args, [url]);
            assert.isFalse(pjaxClickStub.called);

            assert.equal(formSubmitsCount, 0);
            assert.isFalse(pjaxSubmitStub.called);
            assert.equal($('form').length, initialFormsCount);
        }

        function verifyPageLoadWithPjax($element, event, pjaxContainerId) {
            assert.isFalse(windowLocationAssignStub.called);
            assert.isTrue(pjaxClickStub.calledOnce);

            assert.equal(formSubmitsCount, 0);
            assert.isFalse(pjaxSubmitStub.called);
            assert.equal($('form').length, initialFormsCount);

            assert.strictEqual(pjaxClickStub.getCall(0).args[0], event);

            var pjaxOptions = pjaxClickStub.getCall(0).args[1];

            // container needs to be checked separately
            assert.equal(pjaxOptions.container, pjaxContainerId || 'body');
            delete pjaxOptions.container;

            assert.deepEqual(pjaxOptions, {
                push: true,
                replace: true,
                scrollTo: 'scrollTo',
                pushRedirect: 'pushRedirect',
                replaceRedirect: 'replaceRedirect',
                skipOuterContainers: 'skipOuterContainers',
                timeout: 'timeout',
                originalEvent: event,
                originalTarget: $element
            });
        }

        function verifyFormSubmit($form) {
            assert.isFalse(windowLocationAssignStub.called);
            assert.isFalse(pjaxClickStub.called);

            assert.equal(formSubmitsCount, 1);
            assert.isFalse(pjaxSubmitStub.called);
            assert.equal($('form').length, initialFormsCount);

            if ($form) {
                assert.equal($form.attr('id'), $savedSubmittedForm.attr('id'));
            }
        }

        function verifyFormSubmitWithPjax($element, event, $form) {
            assert.isFalse(windowLocationAssignStub.called);
            assert.isFalse(pjaxClickStub.called);

            assert.equal(formSubmitsCount, 1);
            assert.isTrue(pjaxSubmitStub.calledOnce);
            assert.equal($('form').length, initialFormsCount);

            if ($form) {
                assert.equal($form.attr('id'), $savedSubmittedForm.attr('id'));
            }

            var pjaxEvent = pjaxSubmitStub.getCall(0).args[0];
            assert.instanceOf(pjaxEvent, $.Event);
            assert.equal(pjaxEvent.type, 'submit');

            var pjaxOptions = pjaxSubmitStub.getCall(0).args[1];

            // container needs to be checked separately
            if (typeof pjaxOptions.container === 'string') {
                assert.equal(pjaxOptions.container, 'body');
            } else {
                assert.instanceOf(pjaxOptions.container, $);
                assert.equal(pjaxOptions.container.attr('id'), 'body');
            }

            delete pjaxOptions.container;

            assert.deepEqual(pjaxOptions, {
                push: true,
                replace: true,
                scrollTo: 'scrollTo',
                pushRedirect: 'pushRedirect',
                replaceRedirect: 'replaceRedirect',
                skipOuterContainers: 'skipOuterContainers',
                timeout: 'timeout',
                originalEvent: event,
                originalTarget: $element
            });
        }

        describe('with no data-method', function () {
            var noActionsMessage = 'should not do any actions related with page load and form submit';
            var pageLoadMessage = 'should load new page using the link from "href" attribute';
            var pageLoadWithPjaxMessage = pageLoadMessage + ' with pjax';

            describe('with invalid elements or configuration', function () {
                describe('with no form', function () {
                    withData({
                        // Links
                        'link, no href': ['.link-no-href'],
                        'link, empty href': ['.link-empty-href'],
                        'link, href contains anchor ("#") only': ['.link-anchor-href'],
                        'link, no href, data-pjax': ['.link-no-href-pjax'],
                        'link, empty href, data-pjax': ['.link-empty-href-pjax'],
                        'link, href contains anchor ("#") only, data-pjax': ['.link-anchor-href-pjax'],
                        // Not links
                        'not submit, no form': ['.not-submit-no-form'],
                        'submit, no form': ['.submit-no-form'],
                        'submit, data-form, form does not exist': ['.submit-form-not-exist'],
                        'not submit, no form, data-pjax': ['.not-submit-no-form-pjax'],
                        'submit, no form, data-pjax': ['.submit-no-form-pjax'],
                        'submit, data-form, form does not exist, data-pjax': ['.submit-form-not-exist-pjax']
                    }, function (elementSelector) {
                        it(noActionsMessage, function () {
                            var $element = $('.handle-action .no-method .invalid .no-form').find(elementSelector);
                            assert.lengthOf($element, 1);

                            yii.handleAction($element);
                            verifyNoActions();
                        });
                    });
                });

                describe('with form', function () {
                    withData({
                        'not submit, data-form': ['.not-submit-outside-form', '#not-submit-separate-form'],
                        'not submit, inside a form': ['.not-submit-inside-form', '#not-submit-parent-form'],
                        'not submit, data-form, data-pjax': [
                            '.not-submit-outside-form-pjax', '#not-submit-separate-form'
                        ],
                        'not submit, inside a form, data-pjax': [
                            '.not-submit-inside-form-pjax', '#not-submit-parent-form-pjax'
                        ]
                    }, function (elementSelector, formSelector) {
                        it(noActionsMessage, function () {
                            var $element = $('.handle-action .no-method .invalid .form').find(elementSelector);
                            assert.lengthOf($element, 1);

                            var $form = $(formSelector);
                            assert.lengthOf($form, 1);

                            yii.handleAction($element);
                            verifyNoActions();
                        });
                    });
                });
            });

            describe('with valid elements and configuration', function () {
                describe('with no form', function () {
                    withData({
                        'link': ['.link'],
                        'link, data-pjax="0"': ['.link-pjax-0']
                    }, function (elementSelector) {
                        it(pageLoadMessage, function () {
                            var $element = $('.handle-action .no-method .valid').find(elementSelector);
                            assert.lengthOf($element, 1);

                            yii.handleAction($element);
                            verifyPageLoad('/tests/index');
                        });
                    });

                    describe('with link, data-pjax and no pjax support', function () {
                        before(function () {
                            $.support.pjax = false;
                        });

                        after(function () {
                            $.support.pjax = true;
                        });

                        it(pageLoadMessage, function () {
                            var $element = $('.handle-action .no-method .valid .link-pjax');
                            assert.lengthOf($element, 1);

                            yii.handleAction($element);
                            verifyPageLoad('/tests/index');
                        });
                    });

                    withData({
                        'link, data-pjax': ['.link-pjax', 'body'],
                        'link, data-pjax="1"': ['.link-pjax-1', 'body'],
                        'link, data-pjax="true"': ['.link-pjax-true', 'body'],
                        'link, data-pjax, outside a container': [
                            '.link-pjax-outside-container', '#pjax-separate-container'
                        ],
                        'link href, data-pjax, inside a container': ['.link-pjax-inside-container', '#pjax-container-2']
                    }, function (elementSelector, expectedPjaxContainerId) {
                        it(pageLoadWithPjaxMessage, function () {
                            var event = $.Event('click');
                            var $element = $('.handle-action .no-method .valid').find(elementSelector);
                            assert.lengthOf($element, 1);

                            yii.handleAction($element, event);
                            verifyPageLoadWithPjax($element, event, expectedPjaxContainerId);
                        });
                    });
                });

                describe('with form', function () {
                    withData({
                        'submit, data-form': ['.submit-outside-form', '#submit-separate-form'],
                        'submit, inside a form': ['.submit-inside-form', '#submit-parent-form']
                    }, function (elementSelector, formSelector) {
                        it('should submit according existing form', function () {
                            var $element = $('.handle-action .no-method .valid').find(elementSelector);
                            assert.lengthOf($element, 1);

                            var $form = $(formSelector);
                            var initialFormHtml = $form.get(0).outerHTML;
                            assert.lengthOf($form, 1);

                            yii.handleAction($element);

                            verifyFormSubmit($form);
                            assert.equal($savedSubmittedForm.get(0).outerHTML, initialFormHtml);
                        });
                    });

                    withData({
                        'submit, data-form, data-pjax': ['.submit-outside-form-pjax', '#submit-separate-form'],
                        'submit, inside a form, data-pjax': ['.submit-inside-form-pjax', '#submit-parent-form-pjax']
                    }, function (elementSelector, formSelector) {
                        it('should submit according existing form with pjax', function () {
                            var event = $.Event('click');
                            var $element = $('.handle-action .no-method .valid').find(elementSelector);
                            assert.lengthOf($element, 1);

                            var $form = $(formSelector);
                            var initialFormHtml = $form.get(0).outerHTML;
                            assert.lengthOf($form, 1);

                            yii.handleAction($element, event);

                            verifyFormSubmitWithPjax($element, event, $form);
                            assert.equal($savedSubmittedForm.get(0).outerHTML, initialFormHtml);
                        });
                    });
                });
            });
        });

        describe('with data-method', function () {
            describe('with no form', function () {
                withData({
                    'invalid href': [
                        '.bad-href',
                        '<form method="get" action="http://foo.bar/" style="display: none;"></form>'
                    ],
                    'invalid data-params': [
                        '.bad-params',
                        '<form method="get" action="/tests/index" style="display: none;"></form>'
                    ],
                    'data-method="get", data-params, target': [
                        '.get-params-target',
                        '<form method="get" action="/tests/index" target="_blank" style="display: none;">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    'data-method="head", data-params': [
                        '.head',
                        '<form method="post" action="/tests/index" style="display: none;">' +
                        '<input name="_method" value="head" type="hidden">' +
                        '<input name="_csrf" value="foobar" type="hidden">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    'data-method="post", data-params': [
                        '.post',
                        '<form method="post" action="/tests/index" style="display: none;">' +
                        '<input name="_csrf" value="foobar" type="hidden">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    'data-method="post", data-params, upper case': [
                        '.post-upper-case',
                        '<form method="POST" action="/tests/index" style="display: none;">' +
                        '<input name="_csrf" value="foobar" type="hidden">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    'data-method="put", data-params': [
                        '.put',
                        '<form method="post" action="/tests/index" style="display: none;">' +
                        '<input name="_method" value="put" type="hidden">' +
                        '<input name="_csrf" value="foobar" type="hidden">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ]
                }, function (elementSelector, expectedFormHtml) {
                    it('should create temporary form and submit it', function () {
                        var $element = $('.handle-action .method .no-form').find(elementSelector);
                        assert.lengthOf($element, 1);

                        yii.handleAction($element);

                        verifyFormSubmit();
                        assert.equal($savedSubmittedForm.get(0).outerHTML, expectedFormHtml);
                    });
                });

                describe('with data-method="get", data-params, data-pjax', function () {
                    it('should create temporary form and submit it with pjax', function () {
                        var event = $.Event('click');
                        var $element = $('.handle-action .method .no-form .get-params-pjax');
                        assert.lengthOf($element, 1);

                        yii.handleAction($element, event);

                        verifyFormSubmitWithPjax($element, event);

                        var expectedFormHtml = '<form method="get" action="/tests/index" style="display: none;">' +
                            '<input name="foo" value="1" type="hidden">' +
                            '<input name="bar" value="2" type="hidden">' +
                            '</form>';
                        assert.equal($savedSubmittedForm.get(0).outerHTML, expectedFormHtml);
                    });
                });
            });

            describe('with form', function () {
                withData({
                    'data-form, new action, new method, data-params': [
                        '.new-action-new-method',
                        '#method-form',
                        '<form id="method-form" method="post" action="/search">' +
                        '<input name="query" value="a">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    'data-form, same action, same method, data-params': [
                        '.same-action-same-method',
                        '#method-form',
                        '<form id="method-form" method="get" action="/tests/search">' +
                        '<input name="query" value="a">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    'data-form, invalid action, new method, data-params': [
                        '.bad-action-new-method',
                        '#method-form',
                        '<form id="method-form" method="post" action="/tests/search">' +
                        '<input name="query" value="a">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ],
                    // This is a test for this PR:
                    // https://github.com/yiisoft/yii2/pull/8014
                    //
                    // However the bug currently can not be reproduced in jsdom:
                    // https://github.com/tmpvar/jsdom/issues/1688
                    'data-form, same action, same method, hidden "method" and "action" inputs in data-params': [
                        '.hidden-method-action',
                        '#form-hidden-method-action',
                        '<form id="form-hidden-method-action" method="get" action="/tests/search">' +
                        '<input name="query" value="a">' +
                        '<input name="method" value="b" type="hidden">' +
                        '<input name="action" value="c" type="hidden">' +
                        '<input name="foo" value="1" type="hidden">' +
                        '<input name="bar" value="2" type="hidden">' +
                        '</form>'
                    ]
                }, function (elementSelector, formSelector, expectedSubmittedFormHtml) {
                    var message = 'should modify according existing form, submit it and restore to initial condition';
                    it(message, function () {
                        var $element = $('.handle-action .method .form').find(elementSelector);
                        assert.lengthOf($element, 1);

                        var $form = $(formSelector);
                        var initialFormHtml = $form.get(0).outerHTML;
                        assert.lengthOf($form, 1);

                        $form.data('yiiActiveForm', {});

                        yii.handleAction($element);

                        verifyFormSubmit($form);

                        var submittedFormHtml = StringUtils.cleanHTML($savedSubmittedForm.get(0).outerHTML);
                        assert.equal(submittedFormHtml, expectedSubmittedFormHtml);
                        assert.equal($form.get(0).outerHTML, initialFormHtml);

                        // When activeForm is used for this form, the element triggered the submit should be remembered
                        // in jQuery data under according key
                        assert.strictEqual($form.data('yiiActiveForm').submitObject, $element);
                    });
                });

                describe('with data-form, new action, new method, data-params, data-pjax', function () {
                    var message = 'should modify according existing form, submit it with pjax and restore to ' +
                        ' initial condition';
                    it(message, function () {
                        var event = $.Event('click');
                        var $element = $('.handle-action .method .form .new-action-new-method-pjax');
                        assert.lengthOf($element, 1);

                        var $form = $('#method-form');
                        var initialFormHtml = $form.get(0).outerHTML;
                        assert.lengthOf($form, 1);

                        yii.handleAction($element, event);

                        verifyFormSubmitWithPjax($element, event, $form);

                        var expectedSubmittedFormHtml = '<form id="method-form" method="post" action="/search">' +
                            '<input name="query" value="a">' +
                            '<input name="foo" value="1" type="hidden">' +
                            '<input name="bar" value="2" type="hidden">' +
                            '</form>';
                        var submittedFormHtml = StringUtils.cleanHTML($savedSubmittedForm.get(0).outerHTML);
                        assert.equal(submittedFormHtml, expectedSubmittedFormHtml);
                        assert.equal($form.get(0).outerHTML, initialFormHtml);
                    });
                });
            });
        });
    });

    describe('getQueryParams method', function () {
        withData({
            'no query parameters': ['/posts/index', {}],
            // https://github.com/yiisoft/yii2/issues/13738
            'question mark, no query parameters': ['/posts/index?', {}],
            'query parameters': ['/posts/index?foo=1&bar=2', {foo: '1', bar: '2'}],
            'query parameter with multiple values (not array)': ['/posts/index?foo=1&foo=2', {'foo': ['1', '2']}],
            'query parameter with multiple values (array)': ['/posts/index?foo[]=1&foo[]=2', {'foo[]': ['1', '2']}],
            'query parameter with empty value': ['/posts/index?foo=1&foo2', {'foo': '1', 'foo2': ''}],
            'anchor': ['/posts/index#post', {}],
            'query parameters, anchor': ['/posts/index?foo=1&bar=2#post', {foo: '1', bar: '2'}],
            'relative url, query parameters': ['?foo=1&bar=2', {foo: '1', bar: '2'}],
            'relative url, anchor': ['#post', {}],
            'relative url, query parameters, anchor': ['?foo=1&bar=2#post', {foo: '1', bar: '2'}],
            'skipped parameter name': ['?foo=1&=2&baz=3#post', {foo: '1', baz: '3'}],
            'skipped values': [
                '?foo=&PostSearch[tags][]=1&PostSearch[tags][]=', {foo: '', 'PostSearch[tags][]': ['1', '']}
            ],
            'encoded URI component': ['/posts/index?query=' + encodeURIComponent('count >= 1'), {query: 'count >= 1'}],
            // https://github.com/yiisoft/yii2/issues/11921
            'encoded URI component, "+" signs': [
                '/posts/index?next+celebration+day=Sunday+January+1st&' +
                'increase+' + encodeURIComponent('++') + '+' + encodeURIComponent('%') +
                '='
                + encodeURIComponent('++') + '+20+' + encodeURIComponent('%'),
                {'next celebration day': 'Sunday January 1st', 'increase ++ %': '++ 20 %'}
            ],
            'multiple arrays, anchor': [
                '/posts/index?CategorySearch[id]=1&CategorySearch[name]=a' +
                '&PostSearch[name]=b&PostSearch[category_id]=2&PostSearch[tags][]=3&PostSearch[tags][]=4' +
                '&foo[]=5&foo[]=6&bar=7#post',
                {
                    'CategorySearch[id]': '1',
                    'CategorySearch[name]': 'a',
                    'PostSearch[name]': 'b',
                    'PostSearch[category_id]': '2',
                    'PostSearch[tags][]': ['3', '4'],
                    'foo[]': ['5', '6'],
                    bar: '7'
                }
            ]
        }, function (url, expectedParams) {
            it('should parse all query parameters from string and return them within a object', function () {
                assert.deepEqual(yii.getQueryParams(url), expectedParams);
            });
        });
    });

    describe('initModule method', function () {
        var calledInitMethods = [];
        var rootModuleInit = function () {
            calledInitMethods.push('rootModule');
        };

        afterEach(function () {
            calledInitMethods = [];
        });

        withData({
            'isActive is undefined in the root module': [
                undefined,
                rootModuleInit,
                ['rootModule', 'isActiveUndefined', 'isActiveTrue', 'subModule', 'subModule2']
            ],
            'isActive is true in the root module': [
                true,
                rootModuleInit,
                ['rootModule', 'isActiveUndefined', 'isActiveTrue', 'subModule', 'subModule2']
            ],
            'isActive is false in the root module': [false, rootModuleInit, []],
            'isActive is undefined in the root module, init is not a method': [
                undefined,
                'init',
                ['isActiveUndefined', 'isActiveTrue', 'subModule', 'subModule2']
            ]
        }, function (rootModuleIsActive, rootModuleInit, expectedCalledInitMethods) {
            var message = 'should call init method in the root module and all submodules depending depending on ' +
                'activity and if init is a valid method';
            it(message, function () {
                // Root module

                var module = (function () {
                    return {
                        isActive: rootModuleIsActive,
                        init: rootModuleInit
                    };
                })();

                // Submodules

                module.isActiveUndefined = (function () {
                    return {
                        init: function () {
                            calledInitMethods.push('isActiveUndefined');
                        }
                    };
                })();

                module.isActiveTrue = (function () {
                    return {
                        isActive: true,
                        init: function () {
                            calledInitMethods.push('isActiveTrue');
                        }
                    };
                })();

                module.isActiveFalse = (function () {
                    return {
                        isActive: false,
                        init: function () {
                            calledInitMethods.push('isActiveFalse');
                        }
                    };
                })();

                module.initNotFunction = (function () {
                    return {
                        init: 'init'
                    };
                })();

                module.someInteger = 1;
                module.someString = 'string';

                module.subModule = (function () {
                    return {
                        init: function () {
                            calledInitMethods.push('subModule');
                        }
                    };
                })();

                module.subModule.subModule2 = (function () {
                    return {
                        init: function () {
                            calledInitMethods.push('subModule2');
                        }
                    };
                })();

                yii.initModule(module);
                assert.deepEqual(calledInitMethods, expectedCalledInitMethods);
            });
        });
    });

    describe('CSRF handler', function () {
        var server;
        var yiiGetCsrfParamStub;
        var fakeCsrfParam;

        beforeEach(function () {
            server = sinon.fakeServer.create();
            window.XMLHttpRequest = global.XMLHttpRequest;
            yiiGetCsrfParamStub = sinon.stub(yii, 'getCsrfParam', function () {
                return fakeCsrfParam;
            })
        });

        afterEach(function () {
            server.restore();
            yiiGetCsrfParamStub.restore();
        });

        withData({
            'crossDomain is false, csrfParam is not set': [false, undefined, undefined],
            'crossDomain is false, csrfParam is set': [false, 'foobar', 'foobar'],
            'crossDomain is true, csrfParam is not set': [true, undefined, undefined],
            'crossDomain is true, csrfParam is set': [true, 'foobar', undefined]
        }, function (crossDomain, csrfParam, expectedHeaderValue) {
            var message = 'should add header with CSRF token to AJAX requests only when crossDomain is false and ' +
                'csrf parameter is set';
            it(message, function () {
                fakeCsrfParam = csrfParam;
                $.ajax({
                    url: '/tests/index',
                    crossDomain: crossDomain
                });
                server.requests[0].respond(200, {}, '');

                assert.lengthOf(server.requests, 1);
                assert.equal(server.requests[0].requestHeaders['X-CSRF-Token'], expectedHeaderValue);
            });
        });
    });

    describe('redirect handler', function () {
        var windowLocationAssignStub;

        beforeEach(function () {
            windowLocationAssignStub = sinon.stub(window.location, 'assign');
        });

        afterEach(function () {
            windowLocationAssignStub.restore();
        });

        // https://github.com/yiisoft/yii2/pull/10974
        describe('with xhr undefined', function () {
            it('should not perform redirect', function () {
                var e = $.Event('ajaxComplete');
                $('body').trigger(e);

                assert.isFalse(windowLocationAssignStub.called);
            });
        });

        describe('with xhr defined', function () {
            var server;

            beforeEach(function () {
                server = sinon.fakeServer.create();
                window.XMLHttpRequest = global.XMLHttpRequest;
            });

            afterEach(function () {
                server.restore();
            });

            describe('with custom header not set', function () {
                it('should not perform redirect', function () {
                    $.get('/tests/index');
                    server.requests[0].respond(200, {}, '');

                    assert.lengthOf(server.requests, 1);
                    assert.isFalse(windowLocationAssignStub.called);
                });
            });

            describe('with custom header set', function () {
                it('should perform redirect', function () {
                    $.get('/tests/index');
                    server.requests[0].respond(200, {'X-Redirect': 'http://redirect.yii'}, '');

                    assert.lengthOf(server.requests, 1);
                    assert.isTrue(windowLocationAssignStub.calledOnce);
                    assert.deepEqual(windowLocationAssignStub.getCall(0).args, ['http://redirect.yii']);
                });
            });
        });
    });

    describe('asset filters', function () {
        var server;
        var prefilterCallback = function (options) {
            options.crossDomain = false;
        };
        var jsResponse = {
            status: 200,
            headers: {'Content-Type': 'application/javascript'},
            body: 'var foobar = 1;'
        };

        before(function () {
            $.ajaxPrefilter('script', function (options) {
                prefilterCallback(options);
            });
        });

        beforeEach(function () {
            server = sinon.fakeServer.create();
            // Allowed: /js/test.js, http://foo.bar/js/test.js
            server.respondWith(/(http:\/\/foo\.bar)?\/js\/.+\.js/, [
                jsResponse.status,
                jsResponse.headers,
                jsResponse.body
            ]);
            window.XMLHttpRequest = global.XMLHttpRequest;
        });

        after(function () {
            prefilterCallback = function () {
            };
        });

        afterEach(function () {
            server.restore();
        });

        function respondToRequestWithSuccess(requestIndex) {
            server.requests[requestIndex].respond(jsResponse.status, jsResponse.headers, jsResponse.body);
        }

        function respondToRequestWithError(requestIndex) {
            server.requests[requestIndex].respond(404, {}, '');
        }

        // Note: Please do not test loading of the script with the same name in different tests. After successful
        // loading it will stay in loadedScripts and the load will be aborted unless this script is reloadable.

        describe('with scripts', function () {
            var XHR_UNSENT;
            var XHR_OPENED;
            var XHR_DONE;

            before(function () {
                XHR_UNSENT = window.XMLHttpRequest.UNSENT;
                XHR_OPENED = window.XMLHttpRequest.OPENED;
                XHR_DONE = window.XMLHttpRequest.DONE;
            });

            describe('with jsonp dataType', function () {
                it('should load it as many times as it was requested', function () {
                    $.ajax({
                        url: '/js/jsonp.js',
                        dataType: 'jsonp'
                    });
                    server.respond();

                    $.ajax({
                        url: '/js/jsonp.js',
                        dataType: 'jsonp'
                    });
                    server.respond();

                    assert.lengthOf(server.requests, 2);
                    assert.equal(server.requests[0].readyState, XHR_DONE);
                    assert.equal(server.requests[1].readyState, XHR_DONE);
                });
            });

            describe('with scripts loaded on the page load', function () {
                it('should prevent of loading them again for both relative and absolute urls', function () {
                    $.getScript('/js/existing1.js');
                    server.respond();

                    $.getScript('http://foo.bar/js/existing1.js');
                    server.respond();

                    $.getScript('/js/existing2.js');
                    server.respond();

                    $.getScript('http://foo.bar/js/existing2.js');
                    server.respond();

                    assert.lengthOf(server.requests, 0);
                });
            });

            describe('with script not loaded before', function () {
                it('should load it only once for both relative and absolute urls', function () {
                    $.getScript('/js/new.js');
                    server.respond();

                    $.getScript('/js/new.js');
                    server.respond();

                    $.getScript('http://foo.bar/js/new.js');
                    server.respond();

                    assert.lengthOf(server.requests, 1);
                    assert.equal(server.requests[0].readyState, XHR_DONE);
                });
            });

            describe('with reloadableScripts set', function () {
                before(function () {
                    yii.reloadableScripts = [
                        '/js/reloadable.js',
                        // https://github.com/yiisoft/yii2/issues/11494
                        '/js/reloadable/script*.js?v=*'
                    ];
                });

                after(function () {
                    yii.reloadableScripts = [];
                });

                describe('with match', function () {
                    withData({
                        'relative url, exact': ['/js/reloadable.js'],
                        'relative url, wildcard': ['/js/reloadable/script1.js?v=1'],
                        'absolute url, exact': ['http://foo.bar/js/reloadable.js'],
                        'absolute url, wildcard': ['http://foo.bar/js/reloadable/script2.js?v=2']
                    }, function (url) {
                        it('should load it as many times as it was requested', function () {
                            $.getScript(url);
                            server.respond();

                            $.getScript(url);
                            server.respond();

                            assert.lengthOf(server.requests, 2);
                            assert.equal(server.requests[0].readyState, XHR_DONE);
                            assert.equal(server.requests[1].readyState, XHR_DONE);
                        });
                    });
                });

                describe('with no match', function () {
                    withData({
                        'relative url': ['/js/not_reloadable.js'],
                        'relative url, all wildcards are empty': ['/js/reloadable/script.js?v='],
                        'absolute url': ['http://foo.bar/js/reloadable/not_reloadable_script.js'],
                        'absolute url, 1 empty wildcard': ['http://foo.bar/js/reloadable/script1.js?v=']
                    }, function (url) {
                        it('should load it only once for both relative and absolute urls', function () {
                            $.getScript(url);
                            server.respond();

                            $.getScript(url);
                            server.respond();

                            assert.lengthOf(server.requests, 1);
                            assert.equal(server.requests[0].readyState, XHR_DONE);
                        });
                    });
                });

                describe('with failed load after successful load and making it not reloadable', function () {
                    it('should allow to load it again', function () {
                        $.getScript('/js/reloadable/script_fail.js?v=1');
                        respondToRequestWithSuccess(0);

                        $.getScript('/js/reloadable/script_fail.js?v=1');
                        respondToRequestWithError(1);
                        yii.reloadableScripts = [];

                        $.getScript('/js/reloadable/script_fail.js?v=1');
                        respondToRequestWithError(2);

                        assert.lengthOf(server.requests, 3);
                        assert.equal(server.requests[0].readyState, XHR_DONE);
                        assert.equal(server.requests[1].readyState, XHR_DONE);
                        assert.equal(server.requests[2].readyState, XHR_DONE);
                    });
                });
            });

            // https://github.com/yiisoft/yii2/issues/10358
            // https://github.com/yiisoft/yii2/issues/13307

            describe('with concurrent requests', function () {
                // Note: it's not possible to imitate successful loading of all requests, because after the first one
                // loads, the rest will be aborted by yii.js (readyState will be 0 (UNSENT)).
                // Sinon requires request to have state 1 (OPENED) for the response to be sent.
                // Anyway one of the requests will be loaded at least a bit earlier than the others, so we can test
                // that.
                describe('with one successfully completed after one failed', function () {
                    it('should abort remaining requests and disallow to load the script again', function () {
                        $.getScript('/js/concurrent_success.js');
                        $.getScript('/js/concurrent_success.js');
                        $.getScript('/js/concurrent_success.js');

                        assert.lengthOf(server.requests, 3);

                        assert.equal(server.requests[0].readyState, XHR_OPENED);
                        assert.equal(server.requests[1].readyState, XHR_OPENED);
                        assert.equal(server.requests[2].readyState, XHR_OPENED);

                        respondToRequestWithError(2);
                        respondToRequestWithSuccess(1);

                        assert.equal(server.requests[0].readyState, XHR_UNSENT);
                        assert.isTrue(server.requests[0].aborted);
                        assert.equal(server.requests[1].readyState, XHR_DONE);
                        assert.isUndefined(server.requests[1].aborted);
                        assert.equal(server.requests[2].readyState, XHR_DONE);
                        assert.isUndefined(server.requests[2].aborted);

                        $.getScript('/js/concurrent_success.js');
                        server.respond();

                        assert.lengthOf(server.requests, 3);
                    });
                });

                describe('with all requests failed', function () {
                    it('should allow to load the script again', function () {
                        $.getScript('/js/concurrent_fail.js');
                        $.getScript('/js/concurrent_fail.js');
                        $.getScript('/js/concurrent_fail.js');

                        respondToRequestWithError(0);
                        respondToRequestWithError(1);
                        respondToRequestWithError(2);

                        $.getScript('/js/concurrent_fail.js');

                        respondToRequestWithSuccess(3);

                        assert.lengthOf(server.requests, 4);
                        assert.equal(server.requests[0].readyState, XHR_DONE);
                        assert.equal(server.requests[1].readyState, XHR_DONE);
                        assert.equal(server.requests[2].readyState, XHR_DONE);
                        assert.equal(server.requests[3].readyState, XHR_DONE);
                    });
                });

                describe('with requests to different urls successfully completed', function () {
                    it('should not cause any conflicts and disallow to load these scripts again', function () {
                        $.getScript('/js/concurrent_url1.js');
                        $.getScript('/js/concurrent_url2.js');

                        $.getScript('/js/concurrent_url1.js');
                        $.getScript('/js/concurrent_url2.js');

                        respondToRequestWithSuccess(0);
                        respondToRequestWithSuccess(3);

                        $.getScript('/js/concurrent_url1.js');
                        $.getScript('/js/concurrent_url2.js');

                        assert.lengthOf(server.requests, 4);
                        assert.equal(server.requests[0].readyState, XHR_DONE);
                        assert.isUndefined(server.requests[0].aborted);
                        assert.equal(server.requests[1].readyState, XHR_UNSENT);
                        assert.isTrue(server.requests[1].aborted);
                        assert.equal(server.requests[2].readyState, XHR_UNSENT);
                        assert.isTrue(server.requests[2].aborted);
                        assert.equal(server.requests[3].readyState, XHR_DONE);
                        assert.isUndefined(server.requests[3].aborted);
                    });
                });
            });
        });

        describe('with stylesheets', function () {
            // Note: All added stylesheets for the tests must have ".added-stylesheet" class for the proper cleanup

            afterEach(function () {
                $('.added-stylesheet').remove();
            });

            describe('with not reloadable assets', function () {
                it('should not allow to add duplicate stylesheets for both relative and absolute urls', function () {
                    var $styleSheets = $('.asset-filters .stylesheets');
                    assert.lengthOf($styleSheets, 1);

                    $.get('/tests/index', function () {
                        $styleSheets.append(
                            '<link class="added-stylesheet" href="/css/stylesheet1.css" rel="stylesheet">'
                        );
                        $styleSheets.append(
                            '<link class="added-stylesheet" href="/css/stylesheet2.css" rel="stylesheet">'
                        );
                    });

                    server.requests[0].respond(200, {}, '');

                    assert.lengthOf($('link[rel="stylesheet"]'), 2);
                    assert.lengthOf($('#stylesheet1'), 1);
                    assert.lengthOf($('#stylesheet2'), 1);
                });
            });

            describe('with reloadable assets', function () {
                before(function () {
                    yii.reloadableScripts = [
                        '/css/reloadable.css',
                        // https://github.com/yiisoft/yii2/issues/11494
                        '/css/reloadable/stylesheet*.css'
                    ];
                });

                after(function () {
                    yii.reloadableScripts = [];
                });

                it('should allow to add duplicate stylesheets for both relative and absolute urls', function () {
                    var $styleSheets = $('.asset-filters .stylesheets');
                    assert.lengthOf($styleSheets, 1);

                    $.get('/tests/index', function () {
                        $styleSheets.append(
                            '<link class="added-stylesheet" href="/css/reloadable.css" rel="stylesheet">'
                        );
                        $styleSheets.append(
                            '<link class="added-stylesheet" href="http://foo.bar/css/reloadable.css" rel="stylesheet">'
                        );
                        $styleSheets.append(
                            '<link class="added-stylesheet" href="/css/reloadable/stylesheet1.css" rel="stylesheet">'
                        );
                        $styleSheets.append(
                            '<link class="added-stylesheet" href="http://foo.bar/css/reloadable/stylesheet1.css" ' +
                            'rel="stylesheet">'
                        );
                    });

                    server.requests[0].respond(200, {}, '');

                    assert.lengthOf($('link[rel=stylesheet]'), 6);
                });
            });
        });
    });

    describe('data methods', function () {
        var windowConfirmStub;
        var yiiConfirmSpy;
        var yiiHandleActionStub;
        var extraEventHandlerSpy;

        beforeEach(function () {
            windowConfirmStub = sinon.stub(window, 'confirm', function () {
                return true;
            });
            yiiConfirmSpy = sinon.spy(yii, 'confirm');
            yiiHandleActionStub = sinon.stub(yii, 'handleAction');
            extraEventHandlerSpy = sinon.spy();
            $(window.document).on('click change', '.data-methods-element', extraEventHandlerSpy);
        });

        afterEach(function () {
            windowConfirmStub.restore();
            yiiConfirmSpy.restore();
            yiiHandleActionStub.restore();
            extraEventHandlerSpy.reset();
            $(window.document).off('click change', '.data-methods-element');
        });

        describe('with data not set', function () {
            it('should continue handling interaction with element', function () {
                var event = $.Event('click');
                var $element = $('#data-methods-no-data');
                assert.lengthOf($element, 1);

                $element.trigger(event);

                assert.isFalse(yiiConfirmSpy.called);
                assert.isFalse(yiiHandleActionStub.called);
                assert.isTrue(extraEventHandlerSpy.calledOnce);
            });
        });

        describe('disabled confirm dialog', function () {
            it('confirm data param = false', function () {
                var element = $('#data-methods-no-data');
                element.attr('data-confirm', false);
                element.trigger($.Event('click'));

                assert.isFalse(yiiConfirmSpy.called);
                assert.isTrue(yiiHandleActionStub.called);
            });
            it('confirm data param = empty', function () {
                var element = $('#data-methods-no-data');
                element.attr('data-confirm', '');
                element.trigger($.Event('click'));

                assert.isFalse(yiiConfirmSpy.called);
                assert.isTrue(yiiHandleActionStub.called);
            });
            it('confirm data param = undefined', function () {
                var element = $('#data-methods-no-data');
                element.attr('data-confirm', undefined);
                element.trigger($.Event('click'));

                assert.isFalse(yiiConfirmSpy.called);
                assert.isTrue(yiiHandleActionStub.called);
            });
        });

        describe('with clickableSelector with data-confirm', function () {
            it('should call confirm and handleAction methods', function () {
                var event = $.Event('click');
                var elementId = 'data-methods-click-confirm';
                var $element = $('#' + elementId);
                assert.lengthOf($element, 1);

                $element.trigger(event);

                assert.isTrue(yiiConfirmSpy.calledOnce);
                assert.equal(yiiConfirmSpy.getCall(0).args[0], 'Are you sure?');
                assert.isFunction(yiiConfirmSpy.getCall(0).args[1]);
                // https://github.com/yiisoft/yii2/issues/10097
                assert.instanceOf(yiiConfirmSpy.getCall(0).thisValue, window.HTMLAnchorElement);
                assert.equal(yiiConfirmSpy.getCall(0).thisValue.id, elementId);

                assert.isTrue(yiiHandleActionStub.calledOnce);
                assert.equal(yiiHandleActionStub.getCall(0).args[0].attr('id'), elementId);
                assert.strictEqual(yiiHandleActionStub.getCall(0).args[1], event);

                assert.isFalse(extraEventHandlerSpy.called);
            });
        });

        describe('with changeableSelector without data-confirm', function () {
            var elementId = 'data-methods-change';
            var $element;

            before(function () {
                $element = $('#' + elementId);
            });

            after(function () {
                $element.val('');
            });

            it('should call handleAction method only', function () {
                var event = $.Event('change');
                assert.lengthOf($element, 1);

                $element.val(1);
                $element.trigger(event);

                assert.isFalse(yiiConfirmSpy.called);

                assert.isTrue(yiiHandleActionStub.calledOnce);
                assert.equal(yiiHandleActionStub.getCall(0).args[0].attr('id'), elementId);
                assert.strictEqual(yiiHandleActionStub.getCall(0).args[1], event);

                assert.isFalse(extraEventHandlerSpy.called);
            });
        });
    });
});
