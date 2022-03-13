创建你自己的应用程序结构
=======================

> 注：本章节正在开发中。

虽然 [basic](https://github.com/yiisoft/yii2-app-basic) 和 [advanced](https://github.com/yiisoft/yii2-app-advanced) 
项目模板能够满足你的大部分需求，但是，
你仍有可能需要创建你自己的项目模板来开始项目。

Yii 的项目模板是一个包含 `composer.json` 文件的仓库，并被注册为一个 Composer package。任何一个仓库都可以被标识为一个 Composer package，
只要让其可以通过 `create-project` Composer 命令安装。

由于完全从新创建一个你自己的模板工作量有点大，最好的方式是以一个内建模板为基础。
这里，我们使用基础应用模板。

克隆基础模板
------------

第一步是从 Git 仓库克隆 Yii 的基础模板：

```bash
git clone git@github.com:yiisoft/yii2-app-basic.git
```

等待仓库下载到你的电脑。因为为调整到你自己的模板所产生的修改不会被 push 回，
你可以删除下载下来的 `.git` 目录及其内容。

修改文件
--------

接下来，你需要修改 `composer.json` 以配置你自己的模板。修改 `name`, `description`, `keywords`, `homepage`, `license`, 和 `support` 的值来描述你自己的模板。
同样，调整 `require`, `require-dev`, `suggest`
和其他的参数来匹配你模板的环境需求。

> Note: 在 `composer.json` 文件中，使用 `extra` 下的 `writeable` 参数来指定使用模板创建的应用程序后
> 需要设置文件权限的文件列表。

接下来，真正的修改你的应用程序默认的目录结构和内容。
最后，更新 README 文件以符合你的模板。

发布一个 Package（Make a Package）
--------------------------------

模板调整好后，通过其创建一个 Git 仓库并提交你的代码。
如果你希望将你的应用程序模板开源，[Github](https://github.com) 将是最好的托管服务。
如果你不喜欢其他的人来跟你一起协作，你可以使用任意的 Git 仓库服务。

接下来，你需要为 Composer 注册你的 package。对于公有的模板，你可以将 package 注册到 [Packagist](https://packagist.org/)。对于私有的模板，
注册 package 将会麻烦一点。
参考 [Composer documentation](https://getcomposer.org/doc/05-repositories.md#hosting-your-own) 获取更多的指示。

使用模板
-------

以上就是为了创建一个新的 Yii 项目模板你需要做的事情。现在，你可以使用你自己的模板创建项目了：

```
composer create-project --prefer-dist --stability=dev mysoft/yii2-app-coolone new-project
```
