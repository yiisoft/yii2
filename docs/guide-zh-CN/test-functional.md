功能测试
================

功能测试从用户的角度验证场景。 它类似于[acceptance test](测试-验收.md)。 但是它不是通过HTTP进行通信， 而是填充环境， 如POST和GET参数， 然后直接从代码中执行应用程序实例。



功能测试通常比验收测试快， 并且在失败时提供详细的堆栈跟踪。 作为经验法则， 除非有专门的 Web 服务器设置或者 JavaScript 驱动的复杂 UI， 否则它们应该是首选的。



功能测试是借助于具有良好文档的 Codeception 框架来实现的：

- [基于YII框架的 Codeception](http://codeception.com/for/yii)
- [Codeception 的功能测试](http://codeception.com/docs/04-FunctionalTests)

## 运行基本的和高级的模板测试

如果你已经开始使用高级模板， 请参阅["testing" 指南](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md)
有关运行测试的更多细节。  

如果你已经开始使用基本模板， 请检查其 [README "testing" 部分](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing).
