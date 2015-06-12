单元测试
==========

> 注意：本章节正在开发中

单元测试用于验证一个单独的代码单元是否是按照期望的方式运行。在OOP中，最基础的代码单元是一个类。一个单元测试通常需要验证类的接口方法正常运行。即，提供不同的输入参数，测试方法能够返回期望的结果。单元测试经常由被测试的类的开发人员编写。

Yii的单元测试是基于PHPUnit和Codecetion，所以推荐阅读他们的文档：

- [PHPUnit docs starting from chapter 2](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html).
- [Codeception Unit Tests](http://codeception.com/docs/06-UnitTests).

运行basic和advanced脚手架中的单元测试
----------------------------------------------

请参考文档 `apps/advanced/tests/README.md`和`apps/basic/tests/README.md`.

框架的单元测试
--------------------

如果你想要运行Yii框架本身的单元测试，参阅
"[Getting started with Yii2 development](https://github.com/yiisoft/yii2/blob/master/docs/internals/getting-started.md)".
