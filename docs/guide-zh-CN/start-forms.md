使用表单
==================

本章节介绍如何创建一个让用户提交数据的表单页。
该页将显示一个包含 name 输入框和 email 输入框的表单。
当提交这两部分信息后，页面将会显示用户所输入的信息。

为了实现这个目标，除了创建一个[操作](structure-controllers.md)和两个[视图](structure-views)外，
还需要创建一个[模型](structure-models.md)。

通过本教程，你将会学到：

* 创建一个[模型](structure-models.md)代表用户通过表单输入的数据
* 声明规则去验证输入的数据
* 在[视图](structure-views.md)中生成一个 HTML 表单


创建模型 <span id="creating-model"></span>
----------------

模型类 `EntryForm` 代表从用户那请求的数据，
该类如下所示并存储在 `models/EntryForm.php` 文件中。
请参考[类自动加载](concept-autoloading.md)章节获取更多关于类命名约定的介绍。

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;

class EntryForm extends Model
{
    public $name;
    public $email;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
        ];
    }
}
```

该类继承自Yii 提供的一个基类 [[yii\base\Model]]，
该基类通常用来表示数据。

> Info: [[yii\base\Model]] 被用于普通模型类的父类并与数据表**无关**。[[yii\db\ActiveRecord]] 
  通常是普通模型类的父类但与数据表有关联（译注：[[yii\db\ActiveRecord]] 类其实也是继承自 [[yii\base\Model]]，增加了数据库处理）。

`EntryForm` 类包含 `name` 和 `email` 两个公共成员，
用来储存用户输入的数据。它还包含一个名为 `rules()` 的方法，
用来返回数据验证规则的集合。上面声明的验证规则表示：

* `name` 和 `email` 值都是必须的
* `email` 的值必须满足email规则验证

如果你有一个处理用户提交数据的 `EntryForm` 对象，
你可以调用它的 [[yii\base\Model::validate()|validate()]] 方法触发数据验证。
如果有数据验证失败，将把 [[yii\base\Model::hasErrors|hasErrors]] 属性设为 ture，
想要知道具体发生什么错误就调用 [[yii\base\Model::getErrors|getErrors]]。

```php
<?php
$model = new EntryForm();
$model->name = 'Qiang';
$model->email = 'bad';
if ($model->validate()) {
    // 验证成功！
} else {
    // 失败！
    // 使用 $model->getErrors() 获取错误详情
}
```


创建动作 <span id="creating-action"></span>
------------------

下面你得在 `site` 控制器中创建一个 `entry` 操作用于新建的模型。
操作的创建和使用已经在[说一声你好](start-hello.md)小节中解释了。

```php
<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\EntryForm;

class SiteController extends Controller
{
    // ...现存的代码...

    public function actionEntry()
    {
        $model = new EntryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // 验证 $model 收到的数据

            // 做些有意义的事 ...

            return $this->render('entry-confirm', ['model' => $model]);
        } else {
            // 无论是初始化显示还是数据验证错误
            return $this->render('entry', ['model' => $model]);
        }
    }
}
```

该操作首先创建了一个 `EntryForm` 对象。然后尝试从 `$_POST` 搜集用户提交的数据，
由 Yii 的 [[yii\web\Request::post()]] 方法负责搜集。
如果模型被成功填充数据（也就是说用户已经提交了 HTML 表单），
操作将调用 [[yii\base\Model::validate()|validate()]] 去确保用户提交的是有效数据。

> Info: 表达式 `Yii::$app` 代表[应用](structure-applications.md)实例，它是一个全局可访问的单例。
  同时它也是一个[服务定位器](concept-service-locator.md)，
  能提供 `request`，`response`，`db` 等等特定功能的组件。
  在上面的代码里就是使用 `request` 组件来访问应用实例收到的 `$_POST` 数据。

用户提交表单后，操作将会渲染一个名为 `entry-confirm` 的视图去确认用户输入的数据。
如果没填表单就提交，或数据包含错误（译者：如 email 格式不对），
`entry` 视图将会渲染输出，连同表单一起输出的还有验证错误的详细信息。

> Note: 在这个简单例子里我们只是呈现了有效数据的确认页面。
  实践中你应该考虑使用 [[yii\web\Controller::refresh()|refresh()]] 
  或 [[yii\web\Controller::redirect()|redirect()]] 去避免[表单重复提交问题](https://en.wikipedia.org/wiki/Post/Redirect/Get)。


创建视图 <span id="creating-views"></span>
--------------

最后创建两个视图文件 `entry-confirm` 和 `entry`。
他们会被刚才创建的 `entry` 操作渲染。

`entry-confirm` 视图简单地显示提交的 name 和 email 数据。视图文件应该保存在 `views/site/entry-confirm.php`。

```php
<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->name) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->email) ?></li>
</ul>
```

`entry` 视图显示一个 HTML 表单。视图文件应该保存在 `views/site/entry.php`。

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'email') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
```

视图使用了一个功能强大的[小部件](structure-widgets.md) [[yii\widgets\ActiveForm|ActiveForm]] 
去生成 HTML 表单。
其中的 `begin()` 和 `end()` 分别用来渲染表单的开始和关闭标签。
在这两个方法之间使用了 [[yii\widgets\ActiveForm::field()|field()]] 方法去创建输入框。
第一个输入框用于 “name”，第二个输入框用于 “email”。
之后使用 [[yii\helpers\Html::submitButton()]] 方法生成提交按钮。


尝试下 <span id="trying-it-out"></span>
-------------

用浏览器访问下面的 URL 看它能否工作：

```
https://hostname/index.php?r=site/entry
```

你会看到一个包含两个输入框的表单的页面。每个输入框的前面都有一个标签指明应该输入的数据类型。
如果什么都不填就点击提交按钮，或填入格式不正确的 email 地址，将会看到在对应的输入框下显示错误信息。

![验证错误的表单](images/start-form-validation.png)

输入有效的 name 和 email 信息并提交后，
将会看到一个显示你所提交数据的确认页面。

![输入数据的确认页](images/start-entry-confirmation.png)



### 效果说明 <span id="magic-explained"></span>

你可能会好奇 HTML 表单暗地里是如何工作的呢，
看起来它可以为每个输入框显示文字标签，
而当你没输入正确的信息时又不需要刷新页面就能给出错误提示，似乎有些神奇。

是的，其实数据首先由客户端 JavaScript 脚本验证，然后才会提交给服务器通过 PHP 验证。
[[yii\widgets\ActiveForm]] 足够智能到把你在 `EntryForm` 
模型中声明的验证规则转化成客户端 JavaScript 脚本去执行验证。
如果用户浏览器禁用了 JavaScript， 
服务器端仍然会像 `actionEntry()` 方法里这样验证一遍数据。这保证了任何情况下用户提交的数据都是有效的。

> Warning: 客户端验证是提高用户体验的手段。
  无论它是否正常启用，服务端验证则都是必须的，请不要忽略它。

输入框的文字标签是 `field()` 方法生成的，内容就是模型中该数据的属性名。
例如模型中的 `name` 属性生成的标签就是 `Name`。

你可以在视图中自定义标签
按如下方法：

```php
<?= $form->field($model, 'name')->label('自定义 Name') ?>
<?= $form->field($model, 'email')->label('自定义 Email') ?>
```

> Info: Yii 提供了相当多类似的小部件去帮你生成复杂且动态的视图。
  在后面你还会了解到自己写小部件是多么简单。
  你可能会把自己的很多视图代码转化成小部件以提高重用，加快开发效率。


总结 <span id="summary"></span>
-------

本章节指南中你接触了 MVC 设计模式的每个部分。
学到了如何创建一个模型代表用户数据并验证它的有效性。

你还学到了如何从用户那获取数据并在浏览器上回显给用户。
这本来是开发应用的过程中比较耗时的任务，
好在 Yii 提供了强大的小部件让它变得如此简单。

在下一章节中，你将学习如何使用数据库，几乎每个应用都需要数据库。
