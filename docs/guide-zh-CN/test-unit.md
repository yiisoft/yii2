单元测试
==========

> 注意： 此部分在开发环境下使用.

单元测试验证了一个单元代码是否正如预期那样运行工作。 在面向对象程序设计
中，最基本的代码单元就是类。 因此，单元测试主要需要验证每个类接口方法的
正确性。也就是说，单元测试验证了方法在给定不同的输入参数的情况下，该方法
是否能够返回预期的结果。单元测试通常由编写待测试类的人开发。

Yii的单元测试框架Codeception基于PHPUnit, Codeception建议遵从PHPUnit的文档的进行开发：

- [PHPUnit docs starting from chapter 2](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)。
- [Codeception Unit Tests](http://codeception.com/docs/05-UnitTests)。

运行基本和高级模板单元测试
----------------------------------------------

请参阅`apps/advanced/tests/README.md` 和 `apps/basic/tests/README.md`提供的说明。 

框架单元测试
--------------------

如果你想运行Yii框架的单元测试
“[Getting started with Yii 2 development](https://github.com/yiisoft/yii2/blob/master/docs/internals/getting-started.md)”。