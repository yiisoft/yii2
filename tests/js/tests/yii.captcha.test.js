var assert = require('chai').assert;
var sinon;
var withData = require('leche').withData;
var jsdom = require('mocha-jsdom');

var fs = require('fs');
var vm = require('vm');

describe('yii.captcha', function () {
    var yiiCaptchaPath = 'framework/assets/yii.captcha.js';
    var jQueryPath = 'vendor/bower-asset/jquery/dist/jquery.js';
    var $;
    var $captcha;
    var settings = {
        refreshUrl: '/site/captcha?refresh=1',
        hashKey: 'yiiCaptcha/site/captcha'
    };

    function registerTestableCode() {
        var code = fs.readFileSync(yiiCaptchaPath);
        var script = new vm.Script(code);
        var context = new vm.createContext({window: window});

        script.runInContext(context);
    }

    var imgHtml = '<img id="captcha" class="captcha" src="/site/captcha/">' +
        '<img id="captcha-2" class="captcha" src="/site/captcha/">';
    var html = '<!doctype html><html><head><meta charset="utf-8"></head><body>' + imgHtml + '</body></html>';

    jsdom({
        html: html,
        src: fs.readFileSync(jQueryPath, 'utf-8'),
        url: "http://foo.bar"
    });

    before(function () {
        $ = window.$;
        registerTestableCode();
        sinon = require('sinon');
    });

    afterEach(function () {
        if ($captcha.length) {
            $captcha.yiiCaptcha('destroy');
        }
    });

    describe('init', function () {
        var customSettings = {
            refreshUrl: '/posts/captcha?refresh=1',
            hashKey: 'yiiCaptcha/posts/captcha'
        };

        withData({
            'no method specified': [function () {
                $captcha = $('.captcha').yiiCaptcha(settings);
            }, settings],
            'no method specified, custom options': [function () {
                $captcha = $('.captcha').yiiCaptcha(customSettings);
            }, customSettings],
            'manual method call': [function () {
                $captcha = $('.captcha').yiiCaptcha('init', settings);
            }, settings]
        }, function (initFunction, expectedSettings) {
            it('should save settings for all elements', function () {
                initFunction();
                assert.deepEqual($('#captcha').data('yiiCaptcha'), {settings: expectedSettings});
                assert.deepEqual($('#captcha-2').data('yiiCaptcha'), {settings: expectedSettings});
            });
        });
    });

    describe('refresh', function () {
        var server;
        var response = {hash1: 747, hash2: 748, url: '/site/captcha?v=584696959e038'};

        beforeEach(function () {
            server = sinon.fakeServer.create();
            window.XMLHttpRequest = global.XMLHttpRequest;
        });

        afterEach(function () {
            server.restore();
        });

        withData({
            'click on the captcha': [function () {
                $captcha.trigger('click');
            }],
            'manual method call': [function () {
                $captcha.yiiCaptcha('refresh');
            }]
        }, function (refreshFunction) {
            it('should send ajax request, update the image and data for client-side validation', function () {
                $captcha = $('#captcha').yiiCaptcha(settings);
                refreshFunction();
                server.requests[0].respond(200, {"Content-Type": "application/json"}, JSON.stringify(response));

                assert.lengthOf(server.requests, 1);
                assert.include(server.requests[0].url, settings.refreshUrl + '&_=');
                assert.include(server.requests[0].requestHeaders.Accept, 'application/json');
                assert.equal($captcha.attr('src'), response.url);
                assert.deepEqual($('body').data(settings.hashKey), [response.hash1, response.hash2]);
            });
        });
    });

    describe('destroy method', function () {
        var ajaxStub;

        before(function () {
            ajaxStub = sinon.stub($, 'ajax');
        });

        after(function () {
            ajaxStub.restore();
        });

        var message = 'should remove event handlers with saved settings for destroyed element only and return ' +
            'initial jQuery object';
        it(message, function () {
            $captcha = $('.captcha').yiiCaptcha(settings);
            var $captcha1 = $('#captcha');
            var $captcha2 = $('#captcha-2');
            var destroyResult = $captcha1.yiiCaptcha('destroy');
            $captcha1.trigger('click');
            $captcha2.trigger('click');

            assert.strictEqual(destroyResult, $captcha1);
            assert.isTrue(ajaxStub.calledOnce);
            assert.isUndefined($captcha1.data('yiiCaptcha'));
            assert.deepEqual($captcha2.data('yiiCaptcha'), {settings: settings});
        });
    });

    describe('data method', function () {
        it('should return saved settings', function () {
            $captcha = $('#captcha').yiiCaptcha(settings);
            assert.deepEqual($captcha.yiiCaptcha('data'), {settings: settings});
        });
    });

    describe('call of not existing method', function () {
        it('should throw according error', function () {
            $captcha = $('#captcha').yiiCaptcha(settings);
            assert.throws(function () {
                $captcha.yiiCaptcha('foobar');
            }, 'Method foobar does not exist in jQuery.yiiCaptcha');
        });
    });
});
