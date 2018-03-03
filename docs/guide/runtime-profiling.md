# Performance Profiling

You should profile your code to find out the performance bottlenecks and take appropriate measures accordingly.
Profiling measures the time and memory taken by certain code blocks to execute.

To use it, first identify the code blocks that need to be profiled. Then enclose each
code block like the following:

```php
\Yii::beginProfile('myBenchmark');

...code block being profiled...

\Yii::endProfile('myBenchmark');
```

where `myBenchmark` stands for a unique token identifying a code block. Later when you examine the profiling
result, you will use this token to locate the time spent by the corresponding code block.

It is important to make sure that the pairs of `beginProfile` and `endProfile` are properly nested.
For example,

```php
\Yii::beginProfile('block1');

    // some code to be profiled

    \Yii::beginProfile('block2');
        // some other code to be profiled
    \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

If you miss `\Yii::endProfile('block1')` or switch the order of `\Yii::endProfile('block1')` and
`\Yii::endProfile('block2')`, the performance profiling will not work.

Profiling results could be either reviewed in the
[Yii debugger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md) built-in performance profiling panel or sent to
targets configured.

## Configuring profiler targets

Additionally to using Yii debugger, you may want to send profiling messages to log or other targets. In order to do that you need to
configure profiling targets via application config:

```php
return [
    'profiler' => [
        'targets => [
            '__class' => /yii/profile/LogTarget::class,
        ],
    ],
];
```

After profiler `LogTarget` is configured, for each code block being profiled, a log message with the severity level `debug` is recorded.

## Extra tools

In case you need to profile basically every call it's a good idea to use lower level tools.

### XDebug Profiler

If you have XDebug installed, it has
[built-in profiler](http://xdebug.org/docs/profiler). Note, hovever, that enabling XDebug slows down the application significantly so
both profiling results may be inaccurate and it's not applicable to production environments.

### XHProf

[XHProf](http://www.php.net/manual/en/book.xhprof.php) is an open solution in that is meant to be executed in both development and
production environments. It does not affect performance significantly. Several GUIs exist to analyze its results.

### Blackfire

[Blackfire](https://blackfire.io/) is a commerial PHP profiler. Same as XHProf it does not affect performance. UI to analyze data is
provided as SAAS solution.
