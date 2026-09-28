功能测试
================

功能测试从用户的角度验证场景。它类似于[验收测试](test-acceptance.md)。
但是它不是通过 HTTP 进行通信，而是填充环境，如（填充）POST 和 GET 参数，
然后直接在代码里执行 Application 实例。

功能测试通常比验收测试快，并且在失败时提供详细的堆栈跟踪。
根据老司机的经验，功能测试应该是首选的，除非有专门的 Web 服务器设置
或者由 JavaScript 构建的复杂 UI。

功能测试是借助于具有良好文档的 Codeception 框架来实现的：

- [Codeception for Yii framework](https://codeception.com/for/yii)
- [Codeception 的功能测试](https://codeception.com/docs/04-FunctionalTests)

## 运行基本模板和高级模板的测试

如果你使用的是高级模板，请参阅[测试指南](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md)
中关于运行测试的更多细节。  

如果你使用的是基本模板，请检查其 [README "testing" 部分](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing)。
