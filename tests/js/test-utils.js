var JSDOM = require('jsdom').JSDOM;

var defaultHtml = '<!doctype html><html><head><meta charset="utf-8"></head><body></body></html>';

function useJsdom(options) {
    var dom;
    var globalKeys = [];

    before(function () {
        if (global.window) {
            throw new Error('A browser environment is already registered.');
        }

        dom = new JSDOM(options.html || defaultHtml, {
            runScripts: 'outside-only',
            url: options.url
        });

        var sources = Array.isArray(options.src) ? options.src : [options.src];
        sources.filter(Boolean).forEach(function (source) {
            dom.window.eval(source);
        });

        Object.getOwnPropertyNames(dom.window).forEach(function (key) {
            if (key === 'constructor' || Object.prototype.hasOwnProperty.call(global, key)) {
                return;
            }

            globalKeys.push(key);
            global[key] = dom.window[key];
        });

        dom.window.console = global.console;
    });

    after(function () {
        globalKeys.forEach(function (key) {
            delete global[key];
        });
        dom.window.close();
    });
}

function withData(dataset, testFunction) {
    Object.keys(dataset).forEach(function (name) {
        describe('with ' + name, function () {
            var args = Array.isArray(dataset[name]) ? dataset[name] : [dataset[name]];
            testFunction.apply(this, args);
        });
    });
}

module.exports = {
    useJsdom: useJsdom,
    withData: withData
};
