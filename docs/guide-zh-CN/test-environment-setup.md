配置测试环境
======================

> 注意：本章节内容还在开发中

Yii 2 官方兼容 [`Codeception`](https://github.com/Codeception/Codeception) 测试框架，你可以创建以下类型的测试：

- [单元测试](test-unit.md) - 验证一个独立的代码单元是否按照期望的方式运行；
- [功能测试](test-functional.md) - 在浏览器模拟器中以用户视角来验证期望的场景是否发生
- [验收测试](test-acceptance.md) - 在真实的浏览器中以用户视角验证期望的场景是否发生。


Yii 为包括 [`yii2-basic`](https://github.com/yiisoft/yii2/tree/master/apps/basic) 和
 [`yii2-advanced`](https://github.com/yiisoft/yii2/tree/master/apps/advanced) 在内的应用模板脚手架提供全部三种类型的即用测试套件。

为了运行测试用例，你需要安装 [Codeception](https://github.com/Codeception/Codeception)。一个较好的安装方式是：

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

如果你从未通过 Composer 安装过全局的扩展包，运行 `composer global status` 。你的窗口应该输出类似如下：

```
Changed current directory to <directory>
```

然后，将 `<directory>/vendor/bin` 增加到你的 `PATH` 环境变量中。现在，我们可以在命令行中全局的使用 `codecept` 命令了。
