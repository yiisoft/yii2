创建表单
========

在 Yii 中使用表单的主要方式是通过 [[yii\widgets\ActiveForm]]。如果是基于模型的表单应首选这种方式。此外，在 [[yii\helpers\Html]]
中也有一些实用的方法用于添加按钮和帮助文本。

在客户端上显示的表单，大多数情况下有一个相应的[模型](structure-models.md)，用来验证其输入的服务器数据
(可在 [输入验证](input-validation.md) 一节获取关于验证的细节)。
当创建基于模型的表单时，第一步是定义模型本身。该模式可以是一个基于[活动记录](db-active-record.md)的类，表示数据库中的数据，
也可以是一个基于通用模型的类（继承自 [[yii\base\Model]] ），来获取任意的输入数据，如登录表单。
在下面的例子中，我们展示了一个用来做登录表单的通用模型：

```php
<?php

class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // 在这里定义验证规则
        ];
    }
}
```

在控制器中，我们将传递一个模型的实例到视图，其中 [[yii\widgets\ActiveForm|ActiveForm]] 小部件用来显示表单：

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

在上面的代码中，[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] 不仅创建了一个表单实例，同时也标志着表单的开始。
放在 [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] 与 [[yii\widgets\ActiveForm::end()|ActiveForm::end()]]
之间的所有内容都被包裹在 HTML 的 `<form>` 标签中。
与任何小部件一样，你可以指定一些选项，通过传递数组到 `begin` 方法中来配置该小部件。在这种情况下，
一个额外的 CSS 类和 ID 会在 `<form>` 标签中使用。要查看所有可用的选项，请参阅 API 文档的 [[yii\widgets\ActiveForm]]。

为了在表单中创建表单元素与元素的标签，以及任何适用的 JavaScript 验证，[[yii\widgets\ActiveForm::field()|ActiveForm::field()]]
方法在调用时，会返回一个 [[yii\widgets\ActiveField]] 的实例。
直接输出该方法时，结果是一个普通的（文本）输入。要自定义输出，可以附加上 [[yii\widgets\ActiveField|ActiveField]] 的其它方法来一起调用：

```php
// 一个密码输入框
<?= $form->field($model, 'password')->passwordInput() ?>
// 增加一个提示标签
<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
// 创建一个 HTML5 邮箱输入框
<?= $form->field($model, 'email')->input('email') ?>
```

它会通过在 [[yii\widgets\ActiveField::$template|template]] 中定义的表单字段来创建 `<label>`，`<input>` 以及其它的标签。
input 输入框的 name 属性会自动地根据 [[yii\base\Model::formName()|form name]] 以及属性名来创建。
例如，对于在上面的例子中 `username` 输入字段的 name 属性将是 `LoginForm[username]`。
这种命名规则使所有属性的数组的登录表单在服务器端的 `$_POST['LoginForm']` 数组中是可用的。

指定模型的属性可以以更复杂的方式来完成。例如，当上传时，多个文件或选择多个项目的属性，可能需要一个数组值，
你可以通过附加 `[]` 来指定它的属性名称：

```php
// 允许多个文件被上传：
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// 允许进行选择多个项目：
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

命名表单元素，如提交按钮时要小心。在 [jQuery 文档](https://api.jquery.com/submit/) 中有一些保留的名称，可能会导致冲突：

> 表单和它们的子元素不应该使用与表单的属性冲突的 input name 或 id，例如 `submit`，`length`，或者 `method`。
> 要检查你的标签是否存在这些问题，一个完整的规则列表详见 [DOMLint](http://kangax.github.io/domlint/)。

额外的 HTML 标签可以使用纯 HTML 或者 [[yii\helpers\Html|Html]]-辅助类中的方法来添加到表单中，就如上面例子中的
[[yii\helpers\Html::submitButton()|Html::submitButton()]]。


> 提示: 如果你正在你的应用程序中使用 Twitter Bootstrap CSS 你可以使用[[yii\bootstrap\ActiveForm]] 来代替 [[yii\widgets\ActiveForm]]。
> 前者继承自后者并在生成表单字段时使用 Bootstrap 特有的样式。


> 提示：为了设计带星号的表单字段，你可以使用下面的 CSS：
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```

创建下拉列表 <span id="creating-activeform-dropdownlist"></span>
------------

可以使用 ActiveForm 的 [dropDownList()](http://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#dropDownList()-detail)
方法来创建一个下拉列表：

```php
use app\models\ProductCategory;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\Product */

echo $form->field($model, 'product_category')->dropdownList(
    ProductCategory::find()->select(['category_name', 'id'])->indexBy('id')->column(),
    ['prompt'=>'Select Category']
);
```

模型字段的值将被自动预先选定。

延伸阅读 <span id="further-reading"></span>
--------

下一节 [输入验证](input-validation.md) 处理提交的表单数据的服务器端验证，以及 ajax- 和客户端验证。

要学会有关表格的更复杂的用法，你可以查看以下几节：

- [收集列表输入](input-tabular-input.md) 同一种类型的多个模型的采集数据。
- [多模型同时输入](input-multiple-models.md) 在同一窗口中处理多个不同的模型。
- [文件上传](input-file-upload.md) 如何使用表格来上传文件。
