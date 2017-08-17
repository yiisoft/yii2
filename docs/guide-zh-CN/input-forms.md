创建表单
====

基于活动记录(ActiveRecord)的表单：ActiveForm
-----------------------
在yii中使用表单的主要方式是通过[[yii\widgets\ActiveForm]]。当某个表单是基于一个模型时，应该首选这种方式。此外，在[[yii\helpers\Html]]中有很多实用的方法为表单添加按钮和帮助文档。

在客户端显示的表单，大多数情况下都有一个相应的[模型](structure-models.md),用来在服务器上验证其输入的数据(可在[输入验证](input-validation.md)一节获取关于验证的细节)。当创建一个基于模型的表单时，第一步是定义模型本身。该模型可以是一个基于[活动记录](db-active-record.md)的类，表示数据库中的数据，也可以是一个基于通用模型的类（继承自[[yii\base\Model]]），来获取任意的输入数据，如登录表单。

> Tip: 如果一个表单的输入域与数据库的字段不匹配，或者它存在只适用于它的特殊的格式或者方法，则最好为它创建一个单独的继承自[[yii\base\Model]]的模型。

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

在控制器中，我们将传递一个模型是实例到视图，其中[[yii\widgets\ActiveForm|ActiveForm]]小部件将用来显示表单。

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

### 用 `begin()` 和 `end()` 包裹 <span id="wrapping-with-begin-and-end"></span>

在上面的代码中，[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] 不仅创建了一个表单实例，同时也标志的表单的开始。所有在[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]]与[[yii\widgets\ActiveForm::end()|ActiveForm::end()]]之中的内容都会被HTML中的 `<form>`标签包裹。与其他小部件一样，你可以制定一些选项，通过传递数组到到 `begin` 中来配置小部件。在这种情况下，一个额外的CSS类和ID会在 `<form>` 标签中使用。要查看更多可用的选项，请查看API文档的 [[yii\widgets\ActiveForm]]。

### ActiveField <span id="activefield"></span>

为了在表单中创建表单元素与元素的标签，以及任意适用的Javascript验证，需要使用[[yii\widgets\ActiveForm::field()]|ActiveForm::field()]方法，其返回一个[[yii\widgets\ActiveField]]实例。当直接输出该方法时，结果是一个普通的（文本）输入。要自定义输出，可以附加上[[yii\widgets\ActiveField|ActiveField]]的其他方法来一起调用：

```php
// 密码输入框
<?= $form->field($model, 'password')->passwordInput() ?>
// 增加提示与自定义标签
<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
// 创建一个HTML5邮件输入元素
<?= $form->field($model, 'email')->input('email') ?>
```

它会通过[[yii\widgets\ActiveField|ActiveField]]中定义的表单字段来创建`<label>`,`<input>`以及其他标签。input输入框的name属性会自动的根据[[yii\base\Model::formName()| form name]] 以及属性来来创建。例如，对于上面例子中的`username`输入字段的name属性将是`LoginForm[username]`。这种命名方式会使登录表单的所有属性在服务器端的`$_POST['LoginForm']`数组中是可用。

> Tip：如果你在一个表单中只有一个模型，并且想要简化input输入名称，你可以覆盖模型的[[yii\base\Model::formName()|formName()]]方法，使其返回一个空的字符串，以此来跳过数组部分。这在[GridView](output-data-widgets.md#grid-view)的过滤模型创建更好的URLs时是非常有用的。

指定模型是属性时可以以更复杂的方式来完成。比如，当上传多个文件时，或者选择多个多个项目时，可能会需要一个数组值，你可以通过附加`[]`来指定它的属性名称：

```php
// allow multiple files to be uploaded:
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// allow multiple items to be checked:
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

命名表单元素，如提交按钮时需要小心。在[JQuery](https://api.jquery.com/submit/)中有一些保留名词，可能会导致冲突：

> 表单和它们的子元素不应该使用与表单的属性冲突的input name或id，例如submit，length，或者 method。 要检查你的标签是否存在这些问题，一个完整的规则列表详见 DOMLint。

将额外的HTML标签添加到表单中可以通过使用纯HTML或者使用[[yii\helpers\Html|Html]]的方法，比如上述例子中的帮助类的做法[[yii\helpers\Html::submitButton()|Html::submitButton()]]。

> Tip：如果你的应用程序正在使用Twitter的Bootstrap CSS样式，你可以选择使用[[yii\bootstrap\ActiveForm]]代替[[yii\widgets\ActiveForm]]，这个表单继承自后者，并且使用Bootstrap特有的样式初始化表单的输入框。

> Tip: 为了使用星号对必填字段进行样式,你可以使用下面的CSS样式：
>
> ```css
> div.required label.control-label:after {
>     content: " *";
>     color: red;
> }
> ```

创建列表 <span id="creating-activeform-lists"></span>
-----------------------

这里有3中类型的列表：

- 下拉列表
- 单选列表
- 多选列表

为了创建列表，你必须先为它准备选项。这些选项可以手动准备如下：

```php
$items = [
    1 => 'item 1', 
    2 => 'item 2'
]
```

或者直接从数据库中检索：

```php
$items = Category::find()
        ->select(['label'])
        ->indexBy('id')
        ->column();
```

这些 `$items` 必须能被不同的列表组件处理。列表输入框的值（以及当前选中的选项）将会根据当前`$model`的属性的值而自动的生成。

#### 创建一个下拉选择列表 ：<span id="creating-activeform-dropdownlist"></span>

我们可以使用活动领域的[[yii\widgets\ActiveField::dropDownList()]] 方法来创建一个下拉列表：

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->dropdownList([
        1 => 'item 1', 
        2 => 'item 2'
    ],
    ['prompt'=>'Select Category']
);
```

#### 创建一个单选列表 ：<span id="creating-activeform-radioList"></span>

我们可以使用活动领域的[[yii\widgets\ActiveField::radioList()]] 方法来创建一个下拉列表：

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->radioList([
    1 => 'radio 1', 
    2 => 'radio 2'
]);
```

#### 创建一个多选列表： <span id="creating-activeform-checkboxList"></span>

我们可以使用活动领域的[[yii\widgets\ActiveField::checkboxList()]] 方法来创建一个下拉列表：

```php
/* @var $form yii\widgets\ActiveForm */

echo $form->field($model, 'category')->checkboxList([
    1 => 'checkbox 1', 
    2 => 'checkbox 2'
]);
```

使用Pjax <span id="working-with-pjax"></span>
-----------------------

[[yii\widgets\Pjax|Pjax]]组件允许你更新一个页面的某些部分而不用从新加载整个页面。你可以在提交表单后使用Pjax来更新表单，或者替换表单的某些内容。

你可以配置[[yii\widgets\Pjax::$formSelector|$formSelector]]来指定那些表单的提交会触发pjax。如果你没有设置，所有包含`data-pjax`属性，并且含有封闭的Pjax内容的表单，将会触发pjax请求。

```php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Pjax::begin([
    // Pjax options
]);
    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
        // more ActiveForm options
    ]);

        // ActiveForm content

    ActiveForm::end();
Pjax::end();
```

> Tip: 注意小心[[yii\widgets\Pjax|Pjax]]内部的链接，因为响应仍可能在组件内部呈现。为了避免这种现象，可以使用`data-pjax="0"`这个HTML属性。

#### 提交按钮和文件上传

由于`JQuery.serializeArray()`在处理[文件内容](https://github.com/jquery/jquery/issues/2321)和[提交按钮的值](https://github.com/jquery/jquery/issues/2321)时存在已知的问题，同时也为了推广`FormData`在HTML5中的使用，它将直接被弃用而不是等待修复。

这意味着官方对`FormData`在文件上传，ajax提交按钮的值以及组件[[yii\widgets\Pjax]]的使用上取决于[浏览器](https://developer.mozilla.org/en-US/docs/Web/API/FormData#Browser_compatibility)的支持。

扩展阅读 <span id="further-reading"></span> 
---------------

下一节 [输入验证](input-validation.md) 处理提交的表单数据的服务器端验证， 以及 ajax- 和客户端验证。

要学会有关表格的更复杂的用法，你可以查看以下几节：

- [收集列表输入](input-tabular-input.md) 同一种类型的多个模型的采集数据。
- [多模型同时输入](input-multiple-models.md) 在同一窗口中处理多个不同的模型。
- [文件上传](input-file-upload.md) 如何使用表格来上传文件。
