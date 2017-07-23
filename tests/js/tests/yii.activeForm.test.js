var assert = require('chai').assert;
var sinon;
var jsdom = require('mocha-jsdom');

var fs = require('fs');
var vm = require('vm');

describe('yii.activeForm', function () {
    var yiiActiveFormPath = 'framework/assets/yii.activeForm.js';
    var yiiPath = 'framework/assets/yii.js';
    var jQueryPath = 'vendor/bower-asset/jquery/dist/jquery.js';
    var $;
    var $mainForm;

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
        var context = new vm.createContext({
            window: window,
            document: window.document,
            yii: yii
        });
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

    beforeEach(function () {
        $mainForm = $('#main');
    });

    describe('events', function () {
        describe('afterValidateAttribute', function () {
            var afterValidateAttributeEventSpy;
            var dataForAssert;

            before(function () {
                afterValidateAttributeEventSpy = sinon.spy(function (event, data) {
                    dataForAssert = data.value;
                });
            });

            after(function () {
                afterValidateAttributeEventSpy.reset();
            });

            it('should update attribute value', function () {
                var inputId = 'nameInput',
                    $input = $('#' + inputId);
                $mainForm.yiiActiveForm([
                    {
                        id : inputId,
                        input: '#' + inputId
                    }
                ]).on('afterValidateAttribute', afterValidateAttributeEventSpy);

                // Set new value, update attribute
                $input.val('newValue');
                $mainForm.yiiActiveForm('updateAttribute', inputId);

                assert.equal('newValue', dataForAssert);
            });
        });
    });
});