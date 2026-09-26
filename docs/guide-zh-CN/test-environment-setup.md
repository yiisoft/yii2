测试环境设置
======================

Yii 2 官方兼容 [`Codeception`](https://github.com/Codeception/Codeception) 测试框架，
你可以创建以下类型的测试：

- [单元测试](test-unit.md) - 验证一个独立的代码单元是否按照期望的方式运行；
- [功能测试](test-functional.md) - 在浏览器模拟器中以用户视角来验证期望的场景是否发生
- [验收测试](test-acceptance.md) - 在真实的浏览器中以用户视角验证期望的场景是否发生。

Yii 为包括 [`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) 和
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced) 
在内的应用模板脚手架提供全部三种类型的即用测试套件。

Codeception 预装了基本和高级项目模板。
如果您没有使用这些模板中的一个，则可以安装 Codeception
通过输入以下控制台命令：

```
composer require codeception/codeception
composer require codeception/specify
composer require codeception/verify
```
