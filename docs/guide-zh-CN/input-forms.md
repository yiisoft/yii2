创建表单
========

基于活动记录（ActiveRecord）的表单：ActiveForm
-----------------------
在yii中使用表单的主要方式是通过 [[yii\widgets\ActiveForm]]。
当某个表单是基于一个模型时，应该首选这种方式。
此外，在 [[yii\helpers\Html]] 中有很多实用的方法为表单添加按钮和帮助文档。

在客户端显示的表单，大多数情况下都有一个相应的[模型](structure-models.md)，
用来在服务器上验证其输入的数据（可在[输入验证](input-validation.md)一节获取关于验证的细节）。
当创建一个基于模型的表单时，第一步是定义模型本身。该模型可以是一个基于[活动记录](db-active-record.md)的类，
表示数据库中的数据，也可以是一个基于通用模型的类（继承自[[yii\base\Model]]），
来获取任意的输入数据，如登录表单。

> Tip: 如果一个表单的输入域与数据库的字段不匹配，或者它存在只适用于它的特殊的格式或者方法，
> 则最好为它创建一个单独的继承自 [[yii\base\Model]] 的模型。

在接下来的例子中，我们展示了通用模型如何用于登录表单：

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

在控制器中，我们将传递一个模型的实例到视图，其中 [[yii\widgets\ActiveForm|ActiveForm]] 
小部件用来显示表单：

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

### 用 `begin()` 和 `end()` 包裹 <span id="wrapping-with-begin-and-end"></span>
在上面的代码中，[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] 不仅创建了一个表单实例，同时也标志着表单的开始。
放在 [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] 与 [[yii\widgets\ActiveForm::end()|ActiveForm::end()]]
之间的所有内容都被包裹在 HTML 的 `<form>` 标签中。
与任何小部件一样，你可以指定一些选项，通过传递数组到 `begin` 方法中来配置该小部件。在这种情况下，
一个额外的 CSS 类和 ID 会在 `<form>` 标签中使用。要查看所有可用的选项，
请参阅 API 文档的 [[yii\widgets\ActiveForm]]。

### ActiveField <span id="activefield"></span>
为了在表单中创建表单元素与元素的标签，以及任何适用的 JavaScript 验证，[[yii\widgets\ActiveForm::field()|ActiveForm::field()]]
方法在调用时，会返回一个 [[yii\widgets\ActiveField]] 的实例。
直接输出该方法时，结果是一个普通的（文本）输入。要自定义输出，可以附加上 [[yii\widgets\ActiveField|ActiveField]] 
的其它方法来一起调用：

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

> Tip: 如果在表单中只有一个模型并且想要简化输入名称，则可以通过覆盖模型的
> [[yii\base\Model::formName()|formName()]] 方法来返回一个空字符串。
> 这对于 [GridView](output-data-widgets.md#grid-view) 中使用的过滤器模型来创建更好的URL很有用。

指定模型的属性可以以更复杂的方式来完成。例如，当上传时，多个文件
或选择多个项目的属性，可能需要一个数组值，你可以通过附加 `[]` 来
指定它的属性名称：

```php
// 允许多个文件被上传：
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// 允许进行选择多个项目：
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

命名表单元素，如提交按钮时要小心。在 [jQuery 文档](https://api.jquery.com/submit/) 
中有一些保留的名称，可能会导致冲突：

> 表单和它们的子元素不应该使用与表单的属性冲突的 input name 或 id，
> 例如 `submit`，`length`，或者 `method`。
> 要检查你的标签是否存在这些问题，一个完整的规则列表详见 [DOMLint](http://kangax.github.io/domlint/)。

额外的 HTML 标签可以使用纯 HTML 或者 [[yii\helpers\Html|Html]]-辅助类中的方法来添加到表单中，就如上面例子中的
[[yii\helpers\Html::submitButton()|Html::submitButton()]]。


> Tip: 如果你正在你的应用程序中使用 Twitter Bootstrap CSS 你可以使用[[yii\bootstrap\ActiveForm]] 
> 来代替 [[yii\widgets\ActiveForm]]。
> 前者继承自后者并在生成表单字段时使用 Bootstrap 特有的样式。


> Tip: 为了设计带星号的表单字段，你可以使用下面的 CSS：
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```

创建下拉列表 <span id="creating-activeform-dropdownlist"></span>
------------

有三种类型的列表：
* 下拉列表
* 单选列表
* 复选框列表

要创建一个列表，你必须准备这些项目。可以手动完成：

```php
$items = [
    1 => 'item 1', 
    2 => 'item 2'
]
```

或从 DB 中检索：

```php
$items = Category::find()
        ->select(['label'])
        ->indexBy('id')
        ->column();
```

这些 `$items` 必须由不同的列表小部件处理。
表单域（和当前活动项目）的值将由
`$model` 属性的当前值自动设置。

#### 创建一个下拉列表 <span id="creating-activeform-dropdownlist"></span>

我们可以使用 ActiveField [[\yii\widgets\ActiveField::dropDownList()]] 方法创建一个下拉列表：

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->dropdownList([
        1 => 'item 1', 
        2 => 'item 2'
    ],
    ['prompt'=>'Select Category']
);
```

#### 创建一个单选列表 <span id="creating-activeform-radioList"></span>

我们可以使用 ActiveField [[\yii\widgets\ActiveField::radioList()]] 方法创建一个单选列表：

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->radioList([
    1 => 'radio 1', 
    2 => 'radio 2'
]);
```

#### 创建一个复选框列表 <span id="creating-activeform-checkboxList"></span>

我们可以使用 ActiveField [[\yii\widgets\ActiveField::checkboxList()]] 方法创建一个复选框列表：

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->checkboxList([
    1 => 'checkbox 1', 
    2 => 'checkbox 2'
]);
```


与 Pjax 一起工作 <span id="working-with-pjax"></span>
-----------------------

[[yii\widgets\Pjax|Pjax]] 小部件允许您更新某个部分
而不是重新加载整个页面。
您可以使用它来仅更新表单并在提交后更换其内容。

你可以配置 [[yii\widgets\Pjax::$formSelector|$formSelector]]
来指定表单提交可能会触发 pjax。如果没有设置，所有封装 Pjax 内容的 `data-pjax`
属性的表单都会触发 pjax 请求。

```php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Pjax::begin([
    // Pjax 配置项
]);
    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
        // more ActiveForm options
    ]);

        // ActiveForm content

    ActiveForm::end();
Pjax::end();
```
> Tip: 请小心处理 [[yii\widgets\Pjax|Pjax]] 小部件中的链接，
> 因为响应也将在小部件内呈现。为了防止这种情况，
> 使用 `data-pjax="0"` HTML 属性。

#### 提交按钮和文件上传中的值

在处理 [[https://github.com/jquery/jquery/issues/2321|files]] 和
[[https://github.com/jquery/jquery/issues/2321|submit button values]] 
时使用 `jQuery.serializeArray()` 
有已知的问题，这将不会被解决，而是被弃用，
以支持 HTML5 中引入的 FormData 类。

这意味着对 ajax 或使用  [[yii\widgets\Pjax|Pjax]]
小部件的文件和提交按钮值的唯一官方支持取决于
`FormData` 类的
[[https://developer.mozilla.org/en-US/docs/Web/API/FormData#Browser_compatibility|浏览器支持]]。

延伸阅读 <span id="further-reading"></span>
---------------

下一节 [输入验证](input-validation.md) 处理提交的表单数据的服务器端验证，以及 ajax 和客户端验证。

要学会有关表格的更复杂的用法，你可以查看以下几节：

- [收集列表输入](input-tabular-input.md) 同一种类型的多个模型的采集数据。
- [多模型同时输入](input-multiple-models.md) 在同一窗口中处理多个不同的模型。
- [文件上传](input-file-upload.md) 如何使用表格来上传文件。
