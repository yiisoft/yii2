配置测试环境
======================

> 注意：本章节内容还在开发中

Yii2官方兼容 [`Codeception`](https://github.com/Codeception/Codeception)测试框架，你可以创建以下类型的测试：

- [单元测试](test-unit.md) - 验证一个独立的代码单元是否按照期望的方式运行；
- [功能测试](test-functional.md) - 在浏览器模拟器中以用户视角来验证期望的场景是否发生
- [验收测试](test-acceptance.md) - 在真实的浏览器中以用户视角验证期望的场景是否发生。


Yii为包括[`yii2-basic`](https://github.com/yiisoft/yii2/tree/master/apps/basic) 和
[`yii2-advanced`](https://github.com/yiisoft/yii2/tree/master/apps/advanced)在内的应用模板脚手架提供全部三种类型的即用测试套件。

为了运行测试用例，你需要安装[Codeception](https://github.com/Codeception/Codeception)。你既可以为某个项目单独的安装Codeception，也可以为你的开发机器上的所有项目安装Codeception。

为单个项目安装Codeception，在项目文档运行如下命令：（前提是你已安装[Composer](http://www.phpcomposer.com/))

	composer require "codeception/codeception=2.0.*"
	composer require "codeception/specify=*"
	composer require "codeception/verify=*"

如果是全局安装，你需要增加`global`参数：

	composer global require "codeception/codeception=2.0.*"
	composer global require "codeception/specify=*"
	composer global require "codeception/verify=*"

如果你从未通过Composer安装过全局的扩展包，运行`composer global status`。你的窗口应该输出类似如下：

	Changed current directory to <directory>

然后，将`<directory>/vendor/bin`增加到你的`PATH`环境变量中。现在，我们可以在命令行中全局的使用 `codecept` 命令了。

> 注意：全局安装允许你在你的开发机器上的所有项目中使用Codeception，同时，允许你在命令行中运行`codecept`指令而不用特别的指定路径。当然，这种方式有时候也会有问题，比如，如果两个不同的项目需要不同版本的Codeception时。为了简单起见，本手册中所有与测试相关的指令都假设Codeception是全局安装的。