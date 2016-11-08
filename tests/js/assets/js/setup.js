mocha.setup('bdd');

var assert = chai.assert;
var withData = leche.withData;

assert.arraysAreEqual = function (array, expectedArray) {
    expectedArray.length === 0 ? assert.equal(array.length, 0) : assert.deepEqual(array, expectedArray);
};

assert.isDeferred = function (object) {
    if (typeof object.resolve !== 'function') {
        return false;
    }

    return String(object.resolve) === String($.Deferred().resolve);
};
