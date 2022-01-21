var assert = require('chai').assert;

assert.isDeferred = function (object) {
    if (typeof object.resolve !== 'function') {
        return false;
    }

    return String(object.resolve) === String($.Deferred().resolve);
};

var sinon;
var withData = require('leche').withData;

var StringUtils = {
    repeatString: function (value, times) {
        return (new Array(times + 1)).join(value);
    }
};

var jsdom = require('mocha-jsdom');
var punycode = require('../../../vendor/bower-asset/punycode/punycode');

var fs = require('fs');
var vm = require('vm');
var yii;

describe('yii.validation', function () {
    var VALIDATOR_SUCCESS_MESSAGE = 'should leave messages as is';
    var VALIDATOR_ERROR_MESSAGE = 'should add appropriate errors(s) to messages';

    function getValidatorMessage(expectedResult) {
        var isTrueBoolean = typeof expectedResult === 'boolean' && expectedResult === true;
        var isEmptyArray = Array.isArray(expectedResult) && expectedResult.length === 0;

        return isTrueBoolean || isEmptyArray ? VALIDATOR_SUCCESS_MESSAGE : VALIDATOR_ERROR_MESSAGE;
    }

    var $;
    var code;
    var script;

    function FileReader() {
        this.readAsDataURL = function() {
        };
    }

    function Image() {
    }

    function registerTestableCode(customSandbox) {
        if (customSandbox === undefined) {
            customSandbox = {
                File: {},
                FileReader: FileReader,
                Image: Image,
                punycode: punycode
            };
        }

        var path = 'framework/assets/yii.validation.js';

        if (code === undefined) {
            code = fs.readFileSync(path);
        }

        if (script === undefined) {
            script = new vm.Script(code);
        }

        var defaultSandbox = {yii: {}, jQuery: $};
        var sandbox = $.extend({}, defaultSandbox, customSandbox);
        var context = new vm.createContext(sandbox);

        script.runInContext(context);
        yii = sandbox.yii;
    }

    jsdom({
        src: fs.readFileSync('vendor/bower-asset/jquery/dist/jquery.js', 'utf-8')
    });

    before(function () {
        $ = window.$;
        registerTestableCode();
        sinon = require('sinon');
    });

    it('should exist', function () {
        assert.isObject(yii.validation);
    });

    describe('isEmpty method', function () {
        withData({
            'undefined': [undefined, true],
            'null': [null, true],
            'empty array': [[], true],
            'empty string': ['', true],
            'string containing whitespace': [' ', false],
            'empty object': [{}, false],
            'non-zero integer': [1, false],
            'non-empty string': ['a', false],
            'non-empty array': [[1], false]
        }, function (value, expectedValue) {
            var message = expectedValue ? 'should return "true"' : 'should return "false"';
            it(message, function () {
                assert.strictEqual(yii.validation.isEmpty(value), expectedValue);
            });
        });
    });

    describe('addMessage method', function () {
        withData({
            'empty messages': [[], 'Message', 1, ['Message']],
            'non-empty messages': [['Message 1'], 'Message 2', 1, ['Message 1', 'Message 2']],
            'message as template': [[], 'Message with value {value}', 1, ['Message with value 1']]
        }, function (messages, message, value, expectedMessages) {
            it('should extend messages and replace value in template', function () {
                yii.validation.addMessage(messages, message, value);
                assert.deepEqual(messages, expectedMessages);
            });
        });
    });

    describe('required validator', function () {
        withData({
            'empty string': ['', {}, false],
            'empty string, strict mode': ['', {strict: true}, true],
            'string containing whitespace': [' ', {}, false],
            'string containing whitespace, strict mode': [' ', {strict: true}, true],
            'non-empty string': ['a', {}, true],
            'undefined': [undefined, {}, false],
            'undefined, strict mode': [undefined, {strict: true}, false],
            // requiredValue
            'integer and required value set to different integer': [1, {requiredValue: 2}, false],
            'string and required value set to integer with the same value': ['1', {requiredValue: 1}, true],
            'string and required value set to integer with the same value, strict mode': [
                '1',
                {requiredValue: 1, strict: true},
                false
            ],
            'integer and required value set to same integer, strict mode': [
                1,
                {requiredValue: 1, strict: true},
                true
            ]
        }, function (value, options, expectValid) {
            it(getValidatorMessage(expectValid), function () {
                options.message = 'This field is required.';
                var messages = [];
                var expectedMessages = expectValid ? [] : ['This field is required.'];

                yii.validation.required(value, messages, options);
                assert.deepEqual(messages, expectedMessages);
            });
        });
    });

    describe('boolean validator', function () {
        var defaultOptions = {
            message: 'The value must have a boolean type.',
            trueValue: '1',
            falseValue: '0'
        };

        withData({
            'empty string': ['', {}, false],
            'empty string, skip on empty': ['', {skipOnEmpty: true}, true],
            'non-empty string, does not equal neither trueValue no falseValue': ['a', {}, false],
            'integer, value equals falseValue': [0, {}, true],
            'integer, value equals trueValue': [1, {}, true],
            'string equals falseValue': ['0', {}, true],
            'string equals trueValue': ['1', {}, true],
            'integer, value equals falseValue, strict mode': [0, {strict: true}, false],
            'integer, value equals trueValue, strict mode': [1, {strict: true}, false],
            // trueValue, falseValue
            'string equals custom trueValue, custom trueValue is set': ['yes', {trueValue: 'yes'}, true],
            'string does not equal neither trueValue no falseValue, custom trueValue is set': [
                'no',
                {trueValue: 'yes'},
                false
            ],
            'string equals custom falseValue, custom falseValue is set': ['no', {falseValue: 'no'}, true],
            'string does not equal neither trueValue no falseValue, custom falseValue is set': [
                'yes',
                {falseValue: 'no'},
                false
            ],
            'string equals custom trueValue, custom trueValue and falseValue are set': [
                'yes',
                {trueValue: 'yes', falseValue: 'no'},
                true
            ],
            'string equals custom falseValue, custom trueValue and falseValue are set': [
                'no',
                {trueValue: 'yes', falseValue: 'no'},
                true
            ],
            'string does not equal neither custom trueValue no falseValue, custom trueValue and falseValue are set': [
                'a',
                {trueValue: 'yes', falseValue: 'no'},
                false
            ]
        }, function (value, customOptions, expectValid) {
            it(getValidatorMessage(expectValid), function () {
                var options = $.extend({}, defaultOptions, customOptions);
                var messages = [];
                var expectedMessages = expectValid ? [] : ['The value must have a boolean type.'];

                yii.validation.boolean(value, messages, options);
                assert.deepEqual(messages, expectedMessages);
            });
        });
    });

    describe('string validator', function () {
        var defaultOptions = {
            message: 'Invalid type.',
            tooShort: 'Too short.',
            tooLong: 'Too long.',
            notEqual: 'Not equal.'
        };

        withData({
            'empty string': ['', {}, []],
            'empty string, skip on empty': ['', {skipOnEmpty: true}, []],
            'non-empty string': ['a', {}, []],
            'integer': [1, {}, ['Invalid type.']],
            // min
            'string less than min': ['Word', {min: 5}, ['Too short.']],
            'string more than min': ['Some string', {min: 5}, []],
            'string equals min': ['Equal', {min: 5}, []],
            // max
            'string less than max': ['Word', {max: 5}, []],
            'string more than max': ['Some string', {max: 5}, ['Too long.']],
            'string equals max': ['Equal', {max: 5}, []],
            // is
            'string equals exact length': ['Equal', {is: 5}, []],
            'string does not equal exact length': ['Does not equal', {is: 5}, ['Not equal.']],
            'string does not equal exact length and less than min': ['Word', {is: 5, min: 5}, ['Not equal.']],
            // min and max
            'string less than min, both min and max are set': ['Word', {min: 5, max: 10}, ['Too short.']],
            'string in between of min and max, both min and max are set': ['Between', {min: 5, max: 10}, []],
            'string more than max, both min and max are set': ['Some string', {min: 5, max: 10}, ['Too long.']]
        }, function (value, customOptions, expectedMessages) {
            it(getValidatorMessage(expectedMessages), function () {
                var options = $.extend({}, defaultOptions, customOptions);
                var messages = [];

                yii.validation.string(value, messages, options);
                assert.deepEqual(messages, expectedMessages);
            });
        });
    });

    describe('file validator', function () {
        var defaultOptions = {
            message: 'Unable to upload a file.',
            uploadRequired: 'Upload is required.',
            tooMany: 'Too many files.',
            wrongExtension: 'File {file} has wrong extension.',
            wrongMimeType: 'File {file} has wrong mime type.',
            tooSmall: 'File {file} is too small.',
            tooBig: 'File {file} is too big.'
        };
        var attribute = {
            input: '#input-id',
            $form: 'jQuery form object'
        };
        var files;
        var filesService = {
            getFiles: function () {
                return files;
            }
        };
        var $input = {
            get: function (value) {
                return value === 0 ? {files: filesService.getFiles()} : undefined;
            }
        };
        var jQueryInitStub;
        var inputGetSpy;
        var filesServiceSpy;

        beforeEach(function () {
            jQueryInitStub = sinon.stub($.fn, 'init');
            jQueryInitStub.withArgs(attribute.input, attribute.$form).returns($input);
            inputGetSpy = sinon.spy($input, 'get');
            filesServiceSpy = sinon.spy(filesService, 'getFiles');
        });

        afterEach(function () {
            jQueryInitStub.restore();
            inputGetSpy.restore();
            filesServiceSpy.restore();
        });

        describe('with File API is not available', function () {
            beforeEach(function () {
                registerTestableCode({File: undefined});
            });

            afterEach(function () {
                registerTestableCode();
            });

            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var messages = [];

                yii.validation.file(attribute, messages, defaultOptions);
                assert.deepEqual(messages, []);

                assert.isFalse(jQueryInitStub.called);
                assert.isFalse(inputGetSpy.called);
                assert.isFalse(filesServiceSpy.called);
            });
        });

        describe('with File API is available', function () {
            withData({
                'files are not available': [undefined, {}, ['Unable to upload a file.']],
                'no files': [[], {}, ['Upload is required.']],
                'no files, skip on empty': [[], {skipOnEmpty: true}, []],
                // maxFiles
                'number of files less than maximum': [
                    {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                    {maxFiles: 2},
                    []
                ],
                'number of files equals maximum': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024}
                    ],
                    {maxFiles: 2},
                    []
                ],
                'number of files more than maximum': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024},
                        {name: 'file.bmp', type: 'image/bmp', size: 200 * 1024}
                    ],
                    {maxFiles: 2},
                    ['Too many files.']
                ],
                // extensions
                'files in extensions list': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024}
                    ],
                    {extensions: ['jpg', 'png']},
                    []
                ],
                'file not in extensions list': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.bmp', type: 'image/bmp', size: 150 * 1024}
                    ],
                    {extensions: ['jpg', 'png']},
                    ['File file.bmp has wrong extension.']
                ],
                // mimeTypes
                'mime type in mime types list': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024}
                    ],
                    {mimeTypes: ['image/jpeg', 'image/png']},
                    []
                ],
                'mime type not in mime types list': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.bmp', type: 'image/bmp', size: 150 * 1024}
                    ],
                    {mimeTypes: ['image/jpeg', 'image/png']},
                    ['File file.bmp has wrong mime type.']
                ],
                // maxSize
                'size less than maximum size': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024}
                    ],
                    {maxSize: 200 * 1024},
                    []
                ],
                'size equals maximum size': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 100 * 1024}
                    ],
                    {maxSize: 100 * 1024},
                    []
                ],
                'size more than maximum size': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024}
                    ],
                    {maxSize: 50 * 1024},
                    ['File file.jpg is too big.', 'File file.png is too big.']
                ],
                // minSize
                'size less than minimum size': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 150 * 1024}
                    ],
                    {minSize: 120 * 1024},
                    ['File file.jpg is too small.']
                ],
                'size equals minimum size': [
                    [
                        {name: 'file.jpg', type: 'image/bmp', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 100 * 1024}
                    ],
                    {maxSize: 100 * 1024},
                    []
                ],
                'size more than minimum size': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.bmp', type: 'image/bmp', size: 150 * 1024}
                    ],
                    {minSize: 80 * 1024},
                    []
                ],
                'one file is less than minimum size, one file is more than maximum size': [
                    [
                        {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024},
                        {name: 'file.png', type: 'image/png', size: 250 * 1024}
                    ],
                    {minSize: 150 * 1024, maxSize: 200 * 1024},
                    ['File file.jpg is too small.', 'File file.png is too big.']
                ]
            }, function (uploadedFiles, customOptions, expectedMessages) {
                it(getValidatorMessage(expectedMessages), function () {
                    files = uploadedFiles;
                    var options = $.extend({}, defaultOptions, customOptions);
                    var messages = [];

                    yii.validation.file(attribute, messages, options);
                    assert.deepEqual(messages, expectedMessages);

                    assert.isTrue(jQueryInitStub.calledOnce);
                    assert.deepEqual(jQueryInitStub.getCall(0).args, [attribute.input, attribute.$form]);
                    assert.isTrue(inputGetSpy.calledOnce);
                    assert.deepEqual(inputGetSpy.getCall(0).args, [0]);
                    assert.isTrue(filesServiceSpy.calledOnce);
                });
            });
        });
    });

    describe('image validator', function () {
        var attribute = {
            input: '#input-id',
            $form: 'jQuery form object'
        };
        var files;
        var filesService = {
            getFiles: function () {
                return files;
            }
        };
        var $input = {
            get: function (value) {
                return value === 0 ? {files: filesService.getFiles()} : undefined;
            }
        };
        var deferred;
        var jQueryInitStub;
        var inputGetSpy;
        var filesServiceSpy;
        var validateImageStub;
        var deferredStub;

        beforeEach(function () {
            jQueryInitStub = sinon.stub($.fn, 'init');
            jQueryInitStub.withArgs(attribute.input, attribute.$form).returns($input);
            inputGetSpy = sinon.spy($input, 'get');
            filesServiceSpy = sinon.spy(filesService, 'getFiles');
            validateImageStub = sinon.stub(yii.validation, 'validateImage');
            deferred = $.Deferred();
            deferredStub = sinon.stub(deferred, 'resolve');
        });

        afterEach(function () {
            jQueryInitStub.restore();
            inputGetSpy.restore();
            filesServiceSpy.restore();
            validateImageStub.restore();
            deferredStub.restore();
        });

        describe('with FileReader API is not available', function () {
            beforeEach(function () {
                registerTestableCode({FileReader: undefined});
            });

            afterEach(function () {
                registerTestableCode();
            });

            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                files = [
                    {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024, width: 100, height: 100},
                    {name: 'file.png', type: 'image/png', size: 150 * 1024, width: 250, height: 250}
                ];
                var messages = [];
                var deferredList = [];

                yii.validation.image(attribute, messages, {}, deferredList);
                assert.deepEqual(messages, []);

                assert.isFalse(validateImageStub.called);
                assert.isFalse(deferredStub.called);
                assert.deepEqual(deferredList, []);
            });
        });

        describe('with FileReader API is available', function () {
            it(VALIDATOR_ERROR_MESSAGE, function () {
                files = [
                    {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024, width: 100, height: 100},
                    {name: 'file.bmp', type: 'image/bmp', size: 150 * 1024, width: 250, height: 250}
                ];
                var options = {
                    extensions: ['jpg', 'png'],
                    wrongExtension: 'File {file} has wrong extension.',
                    minWidth: 200,
                    underWidth: 'File {file} has small width.'
                };
                var messages = [];
                var deferredList = [];

                yii.validation.image(attribute, messages, options, deferredList);
                assert.deepEqual(messages, ['File file.bmp has wrong extension.']);

                assert.equal(validateImageStub.callCount, files.length);

                for (var i = 0; i < validateImageStub.callCount; i++) {
                    assert.equal(validateImageStub.getCall(i).args.length, 6);
                    assert.deepEqual(validateImageStub.getCall(i).args[0], files[i]);
                    assert.deepEqual(validateImageStub.getCall(i).args[1], ['File file.bmp has wrong extension.']);
                    assert.deepEqual(validateImageStub.getCall(i).args[2], options);
                    assert.isDeferred(validateImageStub.getCall(i).args[3]);
                    assert.instanceOf(validateImageStub.getCall(i).args[4], FileReader);
                    assert.instanceOf(validateImageStub.getCall(i).args[5], Image);
                }

                assert.equal(deferredList.length, files.length);

                for (i = 0; i < deferredList.length; i++) {
                    assert.isDeferred(deferredList[i]);
                }
            });
        });
    });

    describe('validateImage method', function () {
        var file = {name: 'file.jpg', type: 'image/jpeg', size: 100 * 1024};
        var image = new Image();
        var deferred;
        var fileReader = new FileReader();
        var deferredStub;
        var fileReaderStub;

        beforeEach(function () {
            deferred = $.Deferred();
            deferredStub = sinon.stub(deferred, 'resolve');
        });

        afterEach(function () {
            deferredStub.restore();
            fileReaderStub.restore();
        });

        function verifyStubs() {
            assert.isTrue(fileReaderStub.calledOnce);
            assert.isTrue(deferredStub.calledOnce);
        }

        describe('with error while reading data', function () {
            beforeEach(function () {
                fileReaderStub = sinon.stub(fileReader, 'readAsDataURL', function () {
                    this.onerror();
                });
            });

            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var messages = [];

                yii.validation.validateImage(file, messages, {}, deferred, fileReader, image);
                assert.deepEqual(messages, []);

                verifyStubs();
            });
        });

        describe('with error while reading image', function () {
            beforeEach(function () {
                fileReaderStub = sinon.stub(fileReader, 'readAsDataURL', function () {
                    this.onload = function () {
                        image.onerror();
                    };

                    this.onload();
                });
            });

            it(VALIDATOR_ERROR_MESSAGE, function () {
                var messages = [];
                var options = {notImage: 'File {file} is not an image.'};

                yii.validation.validateImage(file, messages, options, deferred, fileReader, image);
                assert.deepEqual(messages, ['File file.jpg is not an image.']);

                verifyStubs();
            });
        });

        describe('with successfully read image', function () {
            var defaultOptions = {
                underWidth: 'File {file} has small width.',
                overWidth: 'File {file} has big width.',
                underHeight: 'File {file} has small height.',
                overHeight: 'File {file} has big height.'
            };

            beforeEach(function () {
                fileReaderStub = sinon.stub(fileReader, 'readAsDataURL', function () {
                    this.onload = function () {
                        image.onload();
                    };

                    this.onload();
                });
            });

            withData({
                // minWidth
                'width less than minimum width': [
                    {width: 100, height: 100},
                    {minWidth: 200},
                    ['File file.jpg has small width.']
                ],
                'width equals minimum width': [{width: 100, height: 100}, {minWidth: 100}, []],
                'width more than minimum width': [{width: 200, height: 200}, {minWidth: 100}, []],
                // maxWidth
                'width less than maximum width': [{width: 100, height: 100}, {maxWidth: 200}, []],
                'width equals maximum width': [{width: 100, height: 100}, {maxWidth: 100}, []],
                'width more than maximum width': [
                    {width: 200, height: 200},
                    {maxWidth: 100},
                    ['File file.jpg has big width.']
                ],
                // minHeight
                'height less than minimum height': [
                    {width: 100, height: 100},
                    {minHeight: 200},
                    ['File file.jpg has small height.']
                ],
                'height equals minimum height': [{width: 100, height: 100}, {minHeight: 100}, []],
                'height more than minimum height': [{width: 200, height: 200}, {minHeight: 100}, []],
                // maxHeight
                'height less than maximum height': [{width: 100, height: 100}, {maxHeight: 200}, []],
                'height equals maximum height': [{width: 100, height: 100}, {maxHeight: 100}, []],
                'height more than maximum height': [
                    {width: 200, height: 200},
                    {maxHeight: 100},
                    ['File file.jpg has big height.']
                ],
                // minWidth and minHeight
                'width less than minimum width and height less than minimum height': [
                    {width: 100, height: 100},
                    {minWidth: 200, minHeight: 200},
                    ['File file.jpg has small width.', 'File file.jpg has small height.']
                ]
            }, function (imageSize, customOptions, expectedMessages) {
                it(getValidatorMessage(expectedMessages), function () {
                    image.width = imageSize.width;
                    image.height = imageSize.height;
                    var options = $.extend({}, defaultOptions, customOptions);
                    var messages = [];

                    yii.validation.validateImage(file, messages, options, deferred, fileReader, image);
                    assert.deepEqual(messages, expectedMessages);

                    verifyStubs();
                });
            });
        });
    });

    describe('number validator', function () {
        var integerPattern = /^\s*[+-]?\d+\s*$/;
        var numberPattern = /^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/;
        var defaultOptions = {
            message: 'Not a number.',
            tooSmall: 'Number is too small.',
            tooBig: 'Number is too big.'
        };

        describe('with integer pattern', function () {
            withData({
                'empty string': ['', false],
                'non-empty string': ['a', false],
                'zero': ['0', true],
                'positive integer, no sign': ['2', true],
                'positive integer with sign': ['+2', true],
                'negative integer': ['-2', true],
                'decimal fraction with dot': ['2.5', false],
                'decimal fraction with comma': ['2,5', false]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var options = $.extend({}, defaultOptions, {pattern: integerPattern});
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Not a number.'];

                    yii.validation.number(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with number pattern', function () {
            withData({
                'empty string': ['', false],
                'non-empty string': ['a', false],
                'zero': ['0', true],
                'positive integer, no sign': ['2', true],
                'positive integer with sign': ['+2', true],
                'negative integer': ['-2', true],
                'decimal fraction with dot, no sign': ['2.5', true],
                'positive decimal fraction with dot and sign': ['+2.5', true],
                'negative decimal fraction with dot': ['-2.5', true],
                'decimal fraction with comma': ['2,5', false],
                'floating number with exponential part': ['-1.23e-10', true]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var options = $.extend({}, defaultOptions, {pattern: numberPattern});
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Not a number.'];

                    yii.validation.number(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with different options, integer pattern', function () {
            withData({
                'empty string, skip on empty': ['', {skipOnEmpty: true}, []],
                // Not a string
                'undefined': [undefined, {}, []],
                'integer, fits pattern': [2, {}, []],
                'integer, does not fit pattern': [2.5, {}, []],
                // min
                'less than minimum': ['1', {min: 2}, ['Number is too small.']],
                'equals minimum': ['2', {min: 2}, []],
                'more than minimum': ['3', {min: 2}, []],
                'wrong integer and less than min': ['1.5', {min: 2}, ['Not a number.']],
                // max
                'less than maximum': ['1', {max: 2}, []],
                'equals maximum': ['2', {max: 2}, []],
                'more than maximum': ['3', {max: 2}, ['Number is too big.']]
            }, function (value, customOptions, expectedMessages) {
                it(getValidatorMessage(expectedMessages), function () {
                    customOptions.pattern = integerPattern;
                    var options = $.extend({}, defaultOptions, customOptions);
                    var messages = [];

                    yii.validation.number(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });
    });

    describe('range validator', function () {
        withData({
            'empty string, skip on empty': ['', {skipOnEmpty: true}, []],
            'array and arrays are not allowed': [['a', 'b'], {}, ['Invalid value.']],
            'string in array': ['a', {range: ['a', 'b', 'c']}, []],
            'string not in array': ['d', {range: ['a', 'b', 'c']}, ['Invalid value.']],
            'array in array': [['a', 'b'], {range: ['a', 'b', 'c'], allowArray: true}, []],
            'array not in array': [['a', 'd'], {range: ['a', 'b', 'c'], allowArray: true}, ['Invalid value.']],
            'string in array and inverted logic': ['a', {range: ['a', 'b', 'c'], not: true}, ['Invalid value.']],
            'string not in array and inverted logic': ['d', {range: ['a', 'b', 'c'], not: true}, []],
            'array in array and inverted logic': [
                ['a', 'b'],
                {range: ['a', 'b', 'c'], allowArray: true, not: true},
                ['Invalid value.']
            ],
            'array not in array and inverted logic': [
                ['a', 'd'],
                {range: ['a', 'b', 'c'], allowArray: true, not: true},
                []
            ]
        }, function (value, options, expectedMessages) {
            it(getValidatorMessage(expectedMessages), function () {
                options.message = 'Invalid value.';
                var messages = [];

                yii.validation.range(value, messages, options);
                assert.deepEqual(messages, expectedMessages);
            });
        });
    });

    describe('regular expression validator', function () {
        var integerPattern = /^\s*[+-]?\d+\s*$/;

        describe('with integer pattern', function () {
            withData({
                'empty string, skip on empty': ['', {skipOnEmpty: true}, []],
                'regular integer': ['2', {}, []],
                'non-integer': ['2.5', {}, ['Invalid value.']],
                'regular integer, inverted logic': ['2', {not: true}, ['Invalid value.']],
                'integer pattern, non-integer, inverted logic': ['2.5', {pattern: integerPattern, not: true}, []]
            }, function (value, options, expectedMessages) {
                it(getValidatorMessage(expectedMessages), function () {
                    options.message = 'Invalid value.';
                    options.pattern = integerPattern;
                    var messages = [];

                    yii.validation.regularExpression(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });
    });

    describe('email validator', function () {
        var pattern = "^[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9]" +
            "(?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$";
        pattern = new RegExp(pattern);
        var fullPattern = "^[^@]*<[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+(?:\\.[a-zA-Z0-9!#$%&'*+\\/=?^_`{|}~-]+)*@" +
            "(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$";
        fullPattern = new RegExp(fullPattern);
        var defaultOptions = {
            pattern: pattern,
            fullPattern: fullPattern,
            message: 'Invalid value.'
        };

        describe('with empty string, skip on empty', function () {
            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var messages = [];
                var options = $.extend({}, defaultOptions, {skipOnEmpty: true});

                yii.validation.email('', messages, options);
                assert.deepEqual(messages, []);
            });
        });

        describe('with basic configuration', function () {
            withData({
                'letters only': ['sam@rmcreative.ru', true],
                'numbers in local-part': ['5011@gmail.com', true],
                'uppercase and lowercase letters, dot and numbers in local-part': ['Abc.123@example.com', true],
                'user mailbox': ['user+mailbox/department=shipping@example.com', true],
                'special symbols in local-part': ['!#$%&\'*+-/=?^_`.{|}~@example.com', true],
                'domain only': ['rmcreative.ru', false],
                'double dot': ['ex..ample@example.com', false],
                'unicode in domain': ['example@äüößìà.de', false],
                'unicode (russian characters) in domain': ['sam@рмкреатиф.ru', false],
                'ASCII in domain': ['example@xn--zcack7ayc9a.de', true],
                'angle brackets, name': ['Carsten Brandt <mail@cebe.cc>', false],
                'angle brackets, quoted name': ['"Carsten Brandt" <mail@cebe.cc>', false],
                'angle brackets, no name': ['<mail@cebe.cc>', false],
                'angle brackets, name, dot in local-part': ['John Smith <john.smith@example.com>', false],
                'angle brackets, name, domain only': ['John Smith <example.com>', false],
                'no angle brackets, name': ['Information info@oertliches.de', false],
                'no angle brackets, name, unicode in domain': ['Information info@örtliches.de', false],
                'angle brackets, long quoted name': [
                    '"' + StringUtils.repeatString('a', 300) + '" <shortmail@example.com>',
                    false
                ],
                'angle brackets, name, local part more than 64 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 65) + '@example.com>',
                    false
                ],
                'angle brackets, name, domain more than 254 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 255) + '.com>',
                    false
                ],
                'angle brackets, name, unicode in domain': ['Information <info@örtliches.de>', false],
                'angle brackets, name, unicode, local-part length is close to 64 characters': [
                    // 21 * 3 = 63
                    'Короткое имя <' + StringUtils.repeatString('бла', 21) + '@пример.com>',
                    false
                ],
                'angle brackets, name, unicode, domain length is close to 254 characters': [
                    // 83 * 3 + 4 = 253
                    'Короткое имя <тест@' + StringUtils.repeatString('бла', 83) + '.com>',
                    false
                ]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];

                    yii.validation.email(value, messages, defaultOptions);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with allowed name', function () {
            withData({
                'letters only': ['sam@rmcreative.ru', true],
                'numbers in local-part': ['5011@gmail.com', true],
                'uppercase and lowercase letters, dot and numbers in local-part': ['Abc.123@example.com', true],
                'user mailbox': ['user+mailbox/department=shipping@example.com', true],
                'special symbols in local-part': ['!#$%&\'*+-/=?^_`.{|}~@example.com', true],
                'domain only': ['rmcreative.ru', false],
                'unicode in domain': ['example@äüößìà.de', false],
                'unicode (russian characters) in domain': ['sam@рмкреатиф.ru', false],
                'ASCII in domain': ['example@xn--zcack7ayc9a.de', true],
                'angle brackets, name': ['Carsten Brandt <mail@cebe.cc>', true],
                'angle brackets, quoted name': ['"Carsten Brandt" <mail@cebe.cc>', true],
                'angle brackets, no name': ['<mail@cebe.cc>', true],
                'angle brackets, name, dot in local-part': ['John Smith <john.smith@example.com>', true],
                'angle brackets, name, domain only': ['John Smith <example.com>', false],
                'no angle brackets, name': ['Information info@oertliches.de', false],
                'no angle brackets, name, unicode in domain': ['Information info@örtliches.de', false],
                'angle brackets, long quoted name': [
                    '"' + StringUtils.repeatString('a', 300) + '" <shortmail@example.com>',
                    true
                ],
                'angle brackets, name, local part more than 64 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 65) + '@example.com>',
                    false
                ],
                'angle brackets, name, domain more than 254 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 255) + '.com>',
                    false
                ],
                'angle brackets, name, unicode in domain': ['Information <info@örtliches.de>', false],
                'angle brackets, name, unicode, local-part length is close to 64 characters': [
                    // 21 * 3 = 63
                    'Короткое имя <' + StringUtils.repeatString('бла', 21) + '@пример.com>',
                    false
                ],
                'angle brackets, name, unicode, domain length is close to 254 characters': [
                    // 83 * 3 + 4 = 253
                    'Короткое имя <тест@' + StringUtils.repeatString('бла', 83) + '.com>',
                    false
                ]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var options = $.extend({}, defaultOptions, {allowName: true});
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];

                    yii.validation.email(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with enabled IDN', function () {
            withData({
                'letters only': ['sam@rmcreative.ru', true],
                'numbers in local-part': ['5011@gmail.com', true],
                'uppercase and lowercase letters, dot and numbers in local-part': ['Abc.123@example.com', true],
                'user mailbox': ['user+mailbox/department=shipping@example.com', true],
                'special symbols in local-part': ['!#$%&\'*+-/=?^_`.{|}~@example.com', true],
                'domain only': ['rmcreative.ru', false],
                'unicode in domain': ['example@äüößìà.de', true],
                'unicode (russian characters) in domain': ['sam@рмкреатиф.ru', true],
                'ASCII in domain': ['example@xn--zcack7ayc9a.de', true],
                'angle brackets, name': ['Carsten Brandt <mail@cebe.cc>', false],
                'angle brackets, quoted name': ['"Carsten Brandt" <mail@cebe.cc>', false],
                'angle brackets, no name': ['<mail@cebe.cc>', false],
                'angle brackets, name, dot in local-part': ['John Smith <john.smith@example.com>', false],
                'angle brackets, name, domain only': ['John Smith <example.com>', false],
                'no angle brackets, name': ['Information info@oertliches.de', false],
                'no angle brackets, name, unicode in domain': ['Information info@örtliches.de', false],
                'angle brackets, long quoted name': [
                    '"' + StringUtils.repeatString('a', 300) + '" <shortmail@example.com>',
                    false
                ],
                'angle brackets, name, local part more than 64 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 65) + '@example.com>',
                    false
                ],
                'angle brackets, name, domain more than 254 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 255) + '.com>',
                    false
                ],
                'angle brackets, name, unicode in domain': ['Information <info@örtliches.de>', false],
                'angle brackets, name, unicode, local-part length is close to 64 characters': [
                    // 21 * 3 = 63
                    'Короткое имя <' + StringUtils.repeatString('бла', 21) + '@пример.com>',
                    false
                ],
                'angle brackets, name, unicode, domain length is close to 254 characters': [
                    // 83 * 3 + 4 = 253
                    'Короткое имя <тест@' + StringUtils.repeatString('бла', 83) + '.com>',
                    false
                ]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var options = $.extend({}, defaultOptions, {enableIDN: true});
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];

                    yii.validation.email(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with allowed name and enabled IDN', function () {
            withData({
                'letters only': ['sam@rmcreative.ru', true],
                'numbers in local-part': ['5011@gmail.com', true],
                'uppercase and lowercase letters, dot and numbers in local-part': ['Abc.123@example.com', true],
                'user mailbox': ['user+mailbox/department=shipping@example.com', true],
                'special symbols in local-part': ['!#$%&\'*+-/=?^_`.{|}~@example.com', true],
                'domain only': ['rmcreative.ru', false],
                'unicode in domain': ['example@äüößìà.de', true],
                'unicode (russian characters) in domain': ['sam@рмкреатиф.ru', true],
                'ASCII in domain': ['example@xn--zcack7ayc9a.de', true],
                'angle brackets, name': ['Carsten Brandt <mail@cebe.cc>', true],
                'angle brackets, quoted name': ['"Carsten Brandt" <mail@cebe.cc>', true],
                'angle brackets, no name': ['<mail@cebe.cc>', true],
                'angle brackets, name, dot in local-part': ['John Smith <john.smith@example.com>', true],
                'angle brackets, name, domain only': ['John Smith <example.com>', false],
                'no angle brackets, name': ['Information info@oertliches.de', false],
                'no angle brackets, name, unicode in domain': ['Information info@örtliches.de', false],
                'angle brackets, long quoted name': [
                    '"' + StringUtils.repeatString('a', 300) + '" <shortmail@example.com>',
                    true
                ],
                'angle brackets, name, local part more than 64 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 65) + '@example.com>',
                    false
                ],
                'angle brackets, name, domain more than 254 characters': [
                    'Short Name <' + StringUtils.repeatString('a', 255) + '.com>',
                    false
                ],
                'angle brackets, name, unicode in domain': ['Information <info@örtliches.de>', true],
                'angle brackets, name, unicode, local-part length is close to 64 characters': [
                    // 21 * 3 = 63
                    'Короткое имя <' + StringUtils.repeatString('бла', 21) + '@пример.com>',
                    false
                ],
                'angle brackets, name, unicode, domain length is close to 254 characters': [
                    // 83 * 3 + 4 = 253
                    'Короткое имя <тест@' + StringUtils.repeatString('бла', 83) + '.com>',
                    false
                ]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var options = $.extend({}, defaultOptions, {allowName: true, enableIDN: true});
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];

                    yii.validation.email(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });
    });

    describe('url validator', function () {
        function getPattern(validSchemes) {
            if (validSchemes === undefined) {
                validSchemes = ['http', 'https'];
            }

            var pattern = '^{schemes}://(([A-Z0-9][A-Z0-9_-]*)(\\.[A-Z0-9][A-Z0-9_-]*)+)(?::\\d{1,5})?(?:$|[?\\/#])';
            pattern = pattern.replace('{schemes}', '(' + validSchemes.join('|') + ')');

            return new RegExp(pattern, 'i');
        }

        var defaultOptions = {
            pattern: getPattern(),
            message: 'Invalid value.'
        };

        describe('with empty string, skip on empty', function () {
            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var messages = [];
                var options = $.extend({}, defaultOptions, {skipOnEmpty: true});

                yii.validation.url('', messages, options);
                assert.deepEqual(messages, []);
            });
        });

        describe('with basic configuration', function () {
            withData({
                'domain only': ['google.de', false],
                'http': ['http://google.de', true],
                'https': ['https://google.de', true],
                'scheme with typo': ['htp://yiiframework.com', false],
                'https, action with get parameters': [
                    'https://www.google.de/search?q=yii+framework&ie=utf-8&oe=utf-8&rls=org.mozilla:de:official' +
                    '&client=firefox-a&gws_rd=cr',
                    true
                ],
                'scheme not in valid schemes': ['ftp://ftp.ruhr-uni-bochum.de/', false],
                'invalid domain': ['http://invalid,domain', false],
                'not allowed symbol (comma) after domain': ['http://example.com,', false],
                'not allowed symbol (star) after domain': ['http://example.com*12', false],
                'symbols after slash': ['http://example.com/*12', true],
                'get parameter without value': ['http://example.com/?test', true],
                'anchor': ['http://example.com/#test', true],
                'port, anchor': ['http://example.com:80/#test', true],
                'port (length equals limit), anchor': ['http://example.com:65535/#test', true],
                'port, get parameter without value': ['http://example.com:81/?good', true],
                'get parameter without value and slash': ['http://example.com?test', true],
                'anchor without slash': ['http://example.com#test', true],
                'port and anchor without slash': ['http://example.com:81#test', true],
                'port and get parameter without value and slash': ['http://example.com:81?good', true],
                'not allowed symbol after domain followed by get parameter without value': [
                    'http://example.com,?test',
                    false
                ],
                'skipped port and get parameter without value': ['http://example.com:?test', false],
                'skipped port and action': ['http://example.com:test', false],
                'port (length more than limit) and action': ['http://example.com:123456/test', false],
                'unicode, special symbols': ['http://äüö?=!"§$%&/()=}][{³²€.edu', false]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];

                    yii.validation.url(value, messages, defaultOptions);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with default scheme', function () {
            withData({
                'no scheme': ['yiiframework.com', true],
                'http': ['http://yiiframework.com', true]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];
                    var options = $.extend({}, defaultOptions, {defaultScheme: 'https'});

                    yii.validation.url(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('without scheme', function () {
            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var messages = [];
                var options = $.extend({}, defaultOptions, {
                    pattern: /(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i
                });

                yii.validation.url('yiiframework.com', messages, options);
                assert.deepEqual(messages, []);
            });
        });

        describe('with default scheme and custom schemes', function () {
            withData({
                'ftp': ['ftp://ftp.ruhr-uni-bochum.de/', true],
                'no scheme': ['google.de', true],
                'http': ['http://google.de', true],
                'https': ['https://google.de', true],
                'scheme with typo': ['htp://yiiframework.com', false],
                'relative url': ['//yiiframework.com', false]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];
                    var options = $.extend({}, defaultOptions, {
                        pattern: getPattern(['http', 'https', 'ftp', 'ftps']),
                        defaultScheme: 'http'
                    });

                    yii.validation.url(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });

        describe('with enabled IDN', function () {
            withData({
                'unicode in domain': ['http://äüößìà.de', true],
                // converted via http://mct.verisign-grs.com/convertServlet
                'ASCII in domain': ['http://xn--zcack7ayc9a.de', true]
            }, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];
                    var options = $.extend({}, defaultOptions, {enableIDN: true});

                    yii.validation.url(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);
                });
            });
        });
    });

    describe('trim filter', function () {
        var attribute = {input: '#input-id'};
        var getInputVal;
        var $input = {
            val: function () {
                return getInputVal();
            },
            is: function () {
                return false;
            }
        };
        var $form = {
            find: function () {
                return $input;
            }
        };

        var formSpy;
        var inputSpy;

        beforeEach(function () {
            formSpy = sinon.spy($form, 'find');
            inputSpy = sinon.spy($input, 'val');
        });

        afterEach(function () {
            formSpy.restore();
            inputSpy.restore();
        });

        describe('with empty string, skip on empty', function () {
            it('should leave value and element value as is and return not changed value', function () {
                getInputVal = function () {
                    return '';
                };

                assert.strictEqual(yii.validation.trim($form, attribute, {skipOnEmpty: true}), '');

                assert.isTrue(formSpy.calledOnce);
                assert.equal(formSpy.getCall(0).args[0], attribute.input);

                assert.isTrue(inputSpy.calledOnce);
                assert.strictEqual(inputSpy.getCall(0).args[0], undefined);
            });
        });

        withData({
            'nothing to trim': ['value', 'value'],
            'spaces at the beginning and end': [' value ', 'value'],
            'newlines at the beginning and end': ['\nvalue\n', 'value'],
            'spaces and newlines at the beginning and end': ['\n value \n', 'value']
        }, function (value, expectedValue) {
            it('should return trimmed value and set it as value of element', function () {
                getInputVal = function (val) {
                    return val === undefined ? value : undefined;
                };

                assert.equal(yii.validation.trim($form, attribute, {}), expectedValue);

                assert.isTrue(formSpy.calledOnce);
                assert.equal(formSpy.getCall(0).args[0], attribute.input);

                assert.equal(inputSpy.callCount, 2);
                assert.strictEqual(inputSpy.getCall(0).args[0], undefined);
                assert.equal(inputSpy.getCall(1).args[0], expectedValue);
            });
        });
    });

    describe('trim filter on checkbox', function () {
        var attribute = {input: '#input-id'};
        var getInputVal;
        var $checkbox = {
            is: function (selector) {
                if (selector === ':checked') {
                    return true;
                }

                if (selector === ':checkbox, :radio') {
                    return true;
                }
            }
        };
        var $form = {
            find: function () {
                return $checkbox;
            }
        };


        it('should be left as is', function () {
            assert.strictEqual(yii.validation.trim($form, attribute, {}, true), true);
        });
    });

    describe('captcha validator', function () {
        // Converted using yii\captcha\CaptchaAction generateValidationHash() method
        var hashes = {'Code': 1497, 'code': 1529};
        var caseInSensitiveData = {
            'valid code in lowercase': ['code', true],
            'valid code in uppercase': ['CODE', true],
            'valid code as is': ['Code', true],
            'invalid code': ['invalid code', false]
        };
        var caseSensitiveData = {
            'valid code in lowercase': ['code', false],
            'valid code in uppercase': ['CODE', false],
            'valid code as is': ['Code', true],
            'invalid code': ['invalid code', false]
        };
        var defaultOptions = {
            message: 'Invalid value.',
            hashKey: 'hashKey'
        };
        var hashesData = [hashes['Code'], hashes['code']];
        var jQueryDataStub;

        beforeEach(function () {
            jQueryDataStub = sinon.stub($.prototype, 'data', function () {
                return hashesData;
            });
        });

        afterEach(function () {
            jQueryDataStub.restore();
        });

        function verifyJQueryDataStub() {
            assert.isTrue(jQueryDataStub.calledOnce);
            assert.equal(jQueryDataStub.getCall(0).args[0], defaultOptions.hashKey);
        }

        describe('with empty string, skip on empty', function () {
            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var messages = [];
                var options = $.extend({}, defaultOptions, {skipOnEmpty: true});

                yii.validation.captcha('', messages, options);
                assert.deepEqual(messages, []);

                assert.isFalse(jQueryDataStub.called);
            });
        });

        describe('with ajax, case insensitive', function () {
            withData(caseInSensitiveData, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];

                    yii.validation.captcha(value, messages, defaultOptions);
                    assert.deepEqual(messages, expectedMessages);

                    verifyJQueryDataStub();
                });
            });
        });

        describe('with ajax, case sensitive', function () {
            withData(caseSensitiveData, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];
                    var options = $.extend({}, defaultOptions, {caseSensitive: true});

                    yii.validation.captcha(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);

                    verifyJQueryDataStub();
                });
            });
        });

        describe('with hash, case insensitive', function () {
            withData(caseInSensitiveData, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    hashesData = undefined;
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];
                    var options = $.extend({}, defaultOptions, {hash: hashes['code']});

                    yii.validation.captcha(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);

                    verifyJQueryDataStub();
                });
            });
        });

        describe('with hash, case sensitive', function () {
            withData(caseSensitiveData, function (value, expectValid) {
                it(getValidatorMessage(expectValid), function () {
                    hashesData = undefined;
                    var messages = [];
                    var expectedMessages = expectValid ? [] : ['Invalid value.'];
                    var options = $.extend({}, defaultOptions, {hash: hashes['Code'], caseSensitive: true});

                    yii.validation.captcha(value, messages, options);
                    assert.deepEqual(messages, expectedMessages);

                    verifyJQueryDataStub();
                });
            });
        });
    });

    describe('compare validator', function () {
        var $input = {
            val: function () {
                return 'b';
            }
        };
        var jQueryInitStub;
        var inputSpy;

        beforeEach(function () {
            jQueryInitStub = sinon.stub($.fn, 'init', function () {
                return $input;
            });
            inputSpy = sinon.spy($input, 'val');
        });

        afterEach(function () {
            jQueryInitStub.restore();
            inputSpy.restore();
        });

        withData({
            'empty string, skip on empty': ['', {skipOnEmpty: true}, true],
            // ==
            '"==" operator, 2 identical integers': [2, {operator: '==', compareValue: 2}, true],
            '"==" operator, 2 different integers': [2, {operator: '==', compareValue: 3}, false],
            '"==" operator, 2 identical decimal fractions': [2.5, {operator: '==', compareValue: 2.5}, true],
            '"==" operator, integer and string with the same values': [2, {operator: '==', compareValue: '2'}, true],
            '"==" operator, integer and string with the different values': [
                2,
                {operator: '==', compareValue: '3'},
                false
            ],
            '"==" operator, 2 identical strings': ['b', {operator: '==', compareValue: 'b'}, true],
            // ===
            '"===" operator, 2 identical integers': [2, {operator: '===', compareValue: 2}, true],
            '"===" operator, 2 different integers': [2, {operator: '===', compareValue: 3}, false],
            '"===" operator, 2 identical decimal fractions': [2.5, {operator: '===', compareValue: 2.5}, true],
            '"===" operator, integer and string with the same value': [2, {operator: '===', compareValue: '2'}, false],
            '"===" operator, integer and string with the different values': [
                2,
                {operator: '===', compareValue: '3'},
                false
            ],
            '"===" operator, 2 identical strings': ['b', {operator: '===', compareValue: 'b'}, true],
            // !=
            '"!=" operator, 2 identical integers': [2, {operator: '!=', compareValue: 2}, false],
            '"!=" operator, 2 different integers': [2, {operator: '!=', compareValue: 3}, true],
            '"!=" operator, 2 identical decimal fractions': [2.5, {operator: '!=', compareValue: 2.5}, false],
            '"!=" operator, integer and string with the same value': [2, {operator: '!=', compareValue: '2'}, false],
            '"!=" operator, integer and string with the different values': [
                2,
                {operator: '!=', compareValue: '3'},
                true
            ],
            '"!=" operator, 2 identical strings': ['b', {operator: '!=', compareValue: 'b'}, false],
            // !==
            '"!==" operator, 2 identical integers': [2, {operator: '!==', compareValue: 2}, false],
            '"!==" operator, 2 different integers': [2, {operator: '!==', compareValue: 3}, true],
            '"!==" operator, 2 identical decimal fractions': [2.5, {operator: '!==', compareValue: 2.5}, false],
            '"!==" operator, integer and string with the same value': [2, {operator: '!==', compareValue: '2'}, true],
            '"!==" operator, integer and string with the different values': [
                2,
                {operator: '!==', compareValue: '3'},
                true
            ],
            '"!==" operator, 2 identical strings': ['b', {operator: '!==', compareValue: 'b'}, false],
            // >
            '">" operator, 2 identical integers': [2, {operator: '>', compareValue: 2}, false],
            '">" operator, 2 integers, 2nd is greater': [2, {operator: '>', compareValue: 3}, false],
            '">" operator, 2 integers, 2nd is lower': [2, {operator: '>', compareValue: 1}, true],
            '">" operator, 2 identical strings': ['b', {operator: '>', compareValue: 'b'}, false],
            '">" operator, 2 strings, 2nd is greater': ['a', {operator: '>', compareValue: 'b'}, false],
            '">" operator, 2 strings, 2nd is lower': ['b', {operator: '>', compareValue: 'a'}, true],
            // >=
            '">=" operator, 2 identical integers': [2, {operator: '>=', compareValue: 2}, true],
            '">=" operator, 2 integers, 2nd is greater': [2, {operator: '>=', compareValue: 3}, false],
            '">=" operator, 2 integers, 2nd is lower': [2, {operator: '>=', compareValue: 1}, true],
            '">=" operator, 2 identical strings': ['b', {operator: '>=', compareValue: 'b'}, true],
            '">=" operator, 2 strings, 2nd is greater': ['a', {operator: '>=', compareValue: 'b'}, false],
            '">=" operator, 2 strings, 2nd is lower': ['b', {operator: '>=', compareValue: 'a'}, true],
            // <
            '"<" operator, 2 identical integers': [2, {operator: '<', compareValue: 2}, false],
            '"<" operator, 2 integers, 2nd is greater': [2, {operator: '<', compareValue: 3}, true],
            '"<" operator, 2 integers, 2nd is lower': [2, {operator: '<', compareValue: 1}, false],
            '"<" operator, 2 identical strings': ['b', {operator: '<', compareValue: 'b'}, false],
            '"<" operator, 2 strings, 2nd is greater': ['a', {operator: '<', compareValue: 'b'}, true],
            '"<" operator, 2 strings, 2nd is lower': ['b', {operator: '<', compareValue: 'a'}, false],
            '"<" operator, strings "10" and "2"': ['10', {operator: '<', compareValue: '2'}, true],
            // <=
            '"<=" operator, 2 identical integers': [2, {operator: '<=', compareValue: 2}, true],
            '"<=" operator, 2 integers, 2nd is greater': [2, {operator: '<=', compareValue: 3}, true],
            '"<=" operator, 2 integers, 2nd is lower': [2, {operator: '<=', compareValue: 1}, false],
            '"<=" operator, 2 identical strings': ['b', {operator: '<=', compareValue: 'b'}, true],
            '"<=" operator, 2 strings, 2nd is greater': ['a', {operator: '<=', compareValue: 'b'}, true],
            '"<=" operator, 2 strings, 2nd is lower': ['b', {operator: '<=', compareValue: 'a'}, false],
            // type
            'number type, "<" operator, strings "10" and "2"': [
                '10',
                {operator: '<', compareValue: '2', type: 'number'},
                false
            ],
            'number type, ">=" operator, 2nd is lower': [
                10,
                {operator: '>=', compareValue: 2, type: 'number'},
                true
            ],
            'number type, "<=" operator, 2nd is lower': [
                10,
                {operator: '<=', compareValue: 2, type: 'number'},
                false
            ],
            'number type, ">" operator, 2nd is lower': [
                10,
                {operator: '>', compareValue: 2, type: 'number'},
                true
            ],
            'number type, ">" operator, compare value undefined': [
                undefined,
                {operator: '>', compareValue: 2, type: 'number'},
                false
            ],
            'number type, "<" operator, compare value undefined': [
                undefined,
                {operator: '<', compareValue: 2, type: 'number'},
                true
            ],
            'number type, ">=" operator, compare value undefined': [
                undefined,
                {operator: '>=', compareValue: 2, type: 'number'},
                false
            ],
            'number type, "<=" operator, compare value undefined': [
                undefined,
                {operator: '<=', compareValue: 2, type: 'number'},
                true
            ],
            // default compare value
            'default compare value, "===" operator, against undefined': [undefined, {operator: '==='}, true]
        }, function (value, options, expectValid) {
            it(getValidatorMessage(expectValid), function () {
                options.message = 'Invalid value.';
                var messages = [];
                var expectedMessages = expectValid ? [] : ['Invalid value.'];

                yii.validation.compare(value, messages, options);
                assert.deepEqual(messages, expectedMessages);

                assert.isFalse(jQueryInitStub.called);
                assert.isFalse(inputSpy.called);
            })
        });

        describe('with compareAttribute, "==" operator and 2 identical strings', function () {
            it(VALIDATOR_SUCCESS_MESSAGE, function () {
                var $form = {
                    find: function(){
                        return $input;
                    }
                };
                var messages = [];
                yii.validation.compare('b', messages, {operator: '==', compareAttribute: 'input-id'}, $form);
                assert.deepEqual(messages, []);

                assert.isTrue(jQueryInitStub.calledOnce);
                assert.equal(jQueryInitStub.getCall(0).args[0], '#input-id');

                assert.isTrue(inputSpy.calledOnce);
                assert.strictEqual(inputSpy.getCall(0).args[0], undefined);
            });
        });
    });

    describe('ip validator', function () {
        var ipParsePattern = '^(\\!?)(.+?)(\/(\\d+))?$';
        var ipv4Pattern = '^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?' +
            '[0-9]?[0-9]))$';
        var ipv6Pattern = '^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:)' +
            '{1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}' +
            '(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}' +
            '(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|' +
            'fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}' +
            '[0-9]){0,1}[0-9])\\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|' +
            '(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$';
        var defaultOptions = {
            messages: {
                message: 'Invalid value.',
                noSubnet: 'No subnet.',
                hasSubnet: 'Has subnet.',
                ipv4NotAllowed: 'IPv4 is not allowed.',
                ipv6NotAllowed: 'IPv6 is not allowed.'
            },
            'ipParsePattern': ipParsePattern,
            'ipv4Pattern': ipv4Pattern,
            'ipv6Pattern': ipv6Pattern,
            ipv4: true,
            ipv6: true
        };

        withData({
            'empty string, skip on empty': ['', {skipOnEmpty: true}, []],
            'not IP': ['not IP', {}, ['Invalid value.']],
            'not IP, IPv4 is disabled': ['not:IP', {ipv4: false}, ['Invalid value.']],
            'not IP, IPv6 is disabled': ['not IP', {ipv6: false}, ['Invalid value.']],
            // subnet, IPv4
            'IPv4, subnet option is not defined': ['192.168.10.0', {}, []],
            'IPv4, subnet option is set to "false"': ['192.168.10.0', {subnet: false}, []],
            'IPv4, subnet option is set to "true"': ['192.168.10.0', {subnet: true}, ['No subnet.']],
            'IPv4 with CIDR subnet, subnet option is not defined': ['192.168.10.0/24', {}, []],
            'IPv4 with CIDR subnet, subnet option is set to "false"': [
                '192.168.10.0/24',
                {subnet: false},
                ['Has subnet.']
            ],
            'IPv4 with CIDR subnet, subnet option is set to "true"': ['192.168.10.0/24', {subnet: true}, []],
            // subnet, IPv6
            'IPv6, subnet option is not defined': ['2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d', {}, []],
            'IPv6, subnet option is set to "false"': ['2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d', {subnet: false}, []],
            'IPv6, subnet option is set to "true"': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {subnet: true},
                ['No subnet.']
            ],
            'IPv6 with CIDR subnet, subnet option is not defined': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d/24',
                {},
                []
            ],
            'IPv6 with CIDR subnet, subnet option is set to "false"': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d/24',
                {subnet: false},
                ['Has subnet.']
            ],
            'IPv6 with CIDR subnet, subnet option is set to "true"': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d/24',
                {subnet: true},
                []
            ],
            // negation, IPv4
            'IPv4, negation option is not defined': ['192.168.10.0', {}, []],
            'IPv4, negation option is set to "false"': ['192.168.10.0', {negation: false}, []],
            'IPv4, negation option is set to "true"': ['192.168.10.0', {negation: true}, []],
            'IPv4 with negation, negation option is not defined': ['!192.168.10.0', {}, []],
            'IPv4 with negation, negation option is set to "false"': [
                '!192.168.10.0',
                {negation: false},
                ['Invalid value.']
            ],
            'IPv4 with negation, negation option is set to "true"': ['!192.168.10.0', {negation: true}, []],
            // negation, IPv6
            'IPv6, negation option is not defined': ['2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d', {}, []],
            'IPv6, negation option is set to "false"': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {negation: false},
                []
            ],
            'IPv6, negation option is set to "true"': ['2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d', {negation: true}, []],
            'IPv6 with negation, negation option is not defined': ['!2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d', {}, []],
            'IPv6 with negation, negation option is set to "false"': [
                '!2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {negation: false},
                ['Invalid value.']
            ],
            'IPv6 with negation, negation option is set to "true"': [
                '!2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {negation: true},
                []
            ],
            // ipv4, ipv6
            'IPv4, IPv4 option is set to "false"': ['192.168.10.0', {ipv4: false}, ['IPv4 is not allowed.']],
            'IPv6, IPv6 option is set to "false"': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {ipv6: false},
                ['IPv6 is not allowed.']
            ],
            'IPv6, short variation (4 groups)': ['2001:db8::ae21:ad12', {}, []],
            'IPv6, short variation (2 groups)': ['::ae21:ad12', {}, []],
            'IPv4, IPv4 and IPv6 options are set to "false"': [
                '192.168.10.0',
                {ipv4: false, ipv6: false},
                ['IPv4 is not allowed.']
            ],
            'IPv6, IPv4 and IPv6 options are set to "false"': [
                '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {ipv4: false, ipv6: false},
                ['IPv6 is not allowed.']
            ],
            'invalid IPv4': ['192,168.10.0', {}, ['Invalid value.']],
            'invalid IPv6': ['2001,0db8:11a3:09d7:1f34:8a2e:07a0:765d', {}, ['Invalid value.']],
            'invalid IPv4, IPv4 option is set to "false"': [
                '192,168.10.0',
                {ipv4: false},
                ['Invalid value.', 'IPv4 is not allowed.']
            ],
            'invalid IPv6, IPv6 option is set to "false"': [
                '2001,0db8:11a3:09d7:1f34:8a2e:07a0:765d',
                {ipv6: false},
                ['Invalid value.', 'IPv6 is not allowed.']
            ]
        }, function (value, customOptions, expectedMessages) {
            it(getValidatorMessage(expectedMessages), function () {
                var messages = [];
                var options = $.extend({}, defaultOptions, customOptions);

                yii.validation.ip(value, messages, options);
                assert.deepEqual(messages, expectedMessages);
            })
        });
    });
});
