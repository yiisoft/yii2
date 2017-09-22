var assert = require('chai').assert;
var sinon;
var jsdom = require('mocha-jsdom');

var fs = require('fs');
var vm = require('vm');

describe('yii.activeForm', function () {
    var yiiActiveFormPath = 'framework/jquery/assets/yii.activeForm.js';
    var yiiPath = 'framework/jquery/assets/yii.js';
    var jQueryPath = 'vendor/bower-asset/jquery/dist/jquery.js';
    var $;
    var $activeForm;

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
        var code = fs.readFileSync(yiiActiveFormPath);
        var script = new vm.Script(code);
        var context = new vm.createContext({window: window, document: window.document, yii: yii});
        script.runInContext(context);
    }

    var activeFormHtml = fs.readFileSync('tests/js/data/yii.activeForm.html', 'utf-8');
    var html = '<!doctype html><html><head><meta charset="utf-8"></head><body>' + activeFormHtml + '</body></html>';

    jsdom({
        html: html,
        src: fs.readFileSync(jQueryPath, 'utf-8')
    });

    before(function () {
        $ = window.$;
        registerTestableCode();
        sinon = require('sinon');
    });

    describe('validate method', function () {
        var windowSetTimeoutStub;
        var afterValidateSpy;

        beforeEach(function () {
            windowSetTimeoutStub = sinon.stub(window, 'setTimeout', function (callback) {
                callback();
            });
            afterValidateSpy = sinon.spy();
        });

        afterEach(function () {
            windowSetTimeoutStub.restore();
            afterValidateSpy.reset();
        });

        describe('with forceValidate parameter set to true', function () {
            it('should trigger manual form validation', function () {
                var inputId = 'name';

                $activeForm = $('#w0');
                $activeForm.yiiActiveForm([
                    {
                        id: inputId,
                        input: '#' + inputId
                    }
                ]).on('afterValidate', afterValidateSpy);

                $activeForm.yiiActiveForm('validate', true);
                // https://github.com/yiisoft/yii2/issues/14510
                assert.isTrue($activeForm.data('yiiActiveForm').validated);
                // https://github.com/yiisoft/yii2/issues/14186
                assert.isTrue(afterValidateSpy.calledOnce);
            });
        });
    });

    describe('events', function () {
        describe('afterValidateAttribute', function () {
            var afterValidateAttributeSpy;
            var eventData;

            before(function () {
                afterValidateAttributeSpy = sinon.spy(function (event, data) {
                    eventData = data;
                });
            });

            after(function () {
                afterValidateAttributeSpy.reset();
            });

            // https://github.com/yiisoft/yii2/issues/14318

            it('should allow to get updated attribute value', function () {
                var inputId = 'name';
                var $input = $('#' + inputId);

                $activeForm = $('#w0');
                $activeForm.yiiActiveForm([
                    {
                        id: inputId,
                        input: '#' + inputId
                    }
                ]).on('afterValidateAttribute', afterValidateAttributeSpy);

                $input.val('New value');
                $activeForm.yiiActiveForm('updateAttribute', inputId);
                assert.equal('New value', eventData.value);
            });
        });
    });
});
