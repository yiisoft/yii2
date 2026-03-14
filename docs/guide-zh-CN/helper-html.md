Html 帮助类（Html helper）
=======================

任何一个 web 应用程序会生成很多 HTMl 超文本标记。如果超文本标记是静态的，
那么[将 PHP 和 HTML 混合在一个文件里](https://www.php.net/manual/zh/language.basic-syntax.phpmode.php)
这种做法是非常高效的。但是，如果这些超文本标记是动态生成的，那么如果没有额外的辅助工具，这个过程将会变得复杂。
Yii 通过 HTML  帮助类来提供生成超文本标记的方法。这个帮助类包含有一系列的用于处理通用的 HTML 标签和其属性以及内容的静态方法。

> Note: 如果你的超文本标记接近静态的，那么最好是直接使用 HTML。
没有必要把所有的超文本标记都用 HTML 辅助类来生成。


## 基础（Basics） <span id="basics"></span>

由于通过字符串连接来生成动态的 HTML 会很容易变得凌乱，
Yii 提供了一系列的静态方法来操作标签配置并基于这些配置来创建对应的标签。


### 生成标签（Generating Tags） <span id="generating-tags"></span>

生成一个标签的代码类似如下：

```php
<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
```

这个方法的第一个参数是标签名称。第二个是要装入到开始和结束标签间的内容。
注意到我们使用 `Html::encode` 。那是因为内容不会被自动的转码以允许在有需要的时候嵌套 HTML。
第三个参数是一个 HTML 配置数组，或者换言之，标签属性。在这个数组中，数组的下标是属性名称，
比如 `class`，`href` 或者 `target`，而值则是对应属性的值。

以上代码会生成如下 HTML ：

```html
<p class="username">samdark</p>
```

如果你只需要开启一个标签或者关闭一个标签，你可以使用 `Html::beginTag()` 和 `Html::endTag()` 方法。

标签属性（ Options ）在 Html 帮助类很多方法和大量的小部件中都有使用。在这些情况下，
有一些额外的处理我们需要知道：

- 如果一个值为 null ，那么对应的属性将不会被渲染。
- 如果是布尔类型的值的属性，将会被当做 
  [布尔属性](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attributes) 来处理。
- 属性的值将会用 [[yii\helpers\Html::encode()|Html::encode()]] 方法进行 HTML 转码处理。
- 如果一个属性的值是一个数组，那么它将会被如下处理：
    
    * 如果这个属性是一个如 [[yii\helpers\Html::$dataAttributes]] 所列的数据属性，
      比如 `data` 或者 `ng`，一系列的属性列表将会被渲染，每个代表值数组中的元素。
      比如： `'data' => ['id' => 1, 'name' => 'yii']` 将会生成  `data-id="1" data-name="yii"`；
      `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` 生成
      `data-params='{"id":1,"name":"yii"}' data-status="ok"`。
      注意后者 中，一个子数组被输出为 JSON 。
    * 如果这个属性不是一个数据属性，那么值将会被 JSON-encoded。比如：`['params' => ['id' => 1, 'name' => 'yii']` 
      生成 `params='{"id":1,"name":"yii"}'`。


### 生成 CSS 类和样式（Forming CSS Classes and Styles） <span id="forming-css"></span>

当开始构造一个 HTML 标签的属性时，我们经常需要对默认的属性进行修改。
为了添加或者删除 CSS 类，你可以使用如下代码：

```php
$options = ['class' => 'btn btn-default'];

if ($type === 'success') {
    Html::removeCssClass($options, 'btn-default');
    Html::addCssClass($options, 'btn-success');
}

echo Html::tag('div', 'Pwede na', $options);

// in case of $type of 'success' it will render
// <div class="btn btn-success">Pwede na</div>
```

基于同样的目的，针对 `style` 属性：

```php
$options = ['class' => ['btn', 'btn-default']];

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

在添加或删除类时，您也可以使用数组格式：

```php
$options = ['class' => 'btn'];

if ($type === 'success') {
    Html::addCssClass($options, ['btn-success', 'btn-lg']);
}

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-success btn-lg">Save</div>'
```

`Html::addCssClass()` 防止重复类，所以你不必担心同一个类可能会出现两次：

```php
$options = ['class' => 'btn btn-default'];

Html::addCssClass($options, 'btn-default'); // class 'btn-default' is already present

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

如果通过数组格式指定 CSS 类选项，则可以使用命名键来标记该类的逻辑用途。
在这种情况下，在 `Html::addCssClass()` 类中会忽略数组格式中具有相同键：

```php
$options = [
    'class' => [
        'btn',
        'theme' => 'btn-default',
    ]
];

Html::addCssClass($options, ['theme' => 'btn-success']); // 'theme' 键已被使用

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

可以使用 `style` 属性以类似的方式设置 CSS 样式：

```php
$options = ['style' => ['width' => '100px', 'height' => '100px']];

// gives style="width: 100px; height: 200px; position: absolute;"
Html::addCssStyle($options, 'height: 200px; position: absolute;');

// gives style="position: absolute;"
Html::removeCssStyle($options, ['width', 'height']);
```

当使用 [[yii\helpers\Html::addCssStyle()|addCssStyle()]] 方法时，你可以指定一个和 CSS 属性相关的名值对的数组，
也可以直接是一个类似 `width: 100px; height: 200px;` 的字符串。这些格式将会自动的被 
[[yii\helpers\Html::cssStyleFromArray()|cssStyleFromArray()]] 和[[yii\helpers\Html::cssStyleToArray()|cssStyleToArray()]] 
方法进行转换。方法 [[yii\helpers\Html::removeCssStyle()|removeCssStyle()]] 接收一个包含要被移除的属性数组作为参数。
如果只想移除一个属性，你可以直接传递一个字符串。


### 标签内容的转码和解码（Encoding and Decoding Content） <span id="encoding-and-decoding-content"></span>

为了让内容能够正确安全的显示，一些 HTML 特殊字符应该被转码。在 PHP 中，
这个操作由 [htmlspecialchars](https://www.php.net/manual/zh/function.htmlspecialchars.php) 和
[htmlspecialchars_decode](https://www.php.net/manual/zh/function.htmlspecialchars-decode.php) 完成。
直接使用这些方法的问题是，你总是需要指定转码所需的额外标志。由于标志一般总是不变的，而内容转码的过程为了避免一些安全问题，
需要和应用的默认过程匹配，
Yii 提供了两个简单可用的对 PHP 原生方法的封装：

```php
$userName = Html::encode($user->name);
echo $userName;

$decodedUserName = Html::decode($userName);
```


## 表单（Forms） <span id="forms"></span>

处理表单标签是大量的重复性劳动并且易错。因此，
Yii 也提供了一系列的方法来辅助处理表单标签。

> Note: 考虑在处理 models 以及需要验证的情形下，使用 [[yii\widgets\ActiveForm|ActiveForm]] 组件。


### 创建表单（Creating Forms） <span id="creating-forms"></span>

表单可以用类似如下代码，使用 [[yii\helpers\Html::beginForm()|beginForm()]] 方法开启：

```php
<?= Html::beginForm(['order/update', 'id' => $id], 'post', ['enctype' => 'multipart/form-data']) ?>
```

方法的第一个参数为表单将要被提交的 URL 地址。它可以以 Yii 路由的形式被指定，并由 [[yii\helpers\Url::to()|Url::to()]] 来接收处理。
第二个参数是使用的方法，默认为 `post` 方法。第三个参数为表单标签的属性数组。在上面的例子中，
我们把编码 POST 请求中的表单数据的方式改为 `multipart/form-data`。
如果是上传文件，这个调整是必须的。

关闭表单标签非常简单：

```php
<?= Html::endForm() ?>
```


### 按钮（Buttons） <span id="buttons"></span>

你可以用如下代码生成按钮：

```php
<?= Html::button('Press me!', ['class' => 'teaser']) ?>
<?= Html::submitButton('Submit', ['class' => 'submit']) ?>
<?= Html::resetButton('Reset', ['class' => 'reset']) ?>
```

上述三个方法的第一个参数为按钮的标题，第二个是标签属性。标题默认没有进行转码，如果标题是由终端用输入的，
那么请自行用 [[yii\helpers\Html::encode()|Html::encode()]] 方法进行转码。


### 输入栏（Input Fields） <span id="input-fields"></span>

input 相关的方法有两组：以 `active` 开头的被称为 active inputs，
另一组则不以其开头。active inputs 依据指定的模型和属性获取数据，
而普通 input 则是直接指定数据。

一般用法如下：

```php
type, input name, input value, options
<?= Html::input('text', 'username', $user->name, ['class' => $username]) ?>

type, model, model attribute name, options
<?= Html::activeInput('text', $user, 'name', ['class' => $username]) ?>
```

如果你知道 input 类型，更方便的做法是使用以下快捷方法：

- [[yii\helpers\Html::buttonInput()]]
- [[yii\helpers\Html::submitInput()]]
- [[yii\helpers\Html::resetInput()]]
- [[yii\helpers\Html::textInput()]], [[yii\helpers\Html::activeTextInput()]]
- [[yii\helpers\Html::hiddenInput()]], [[yii\helpers\Html::activeHiddenInput()]]
- [[yii\helpers\Html::passwordInput()]] / [[yii\helpers\Html::activePasswordInput()]]
- [[yii\helpers\Html::fileInput()]], [[yii\helpers\Html::activeFileInput()]]
- [[yii\helpers\Html::textarea()]], [[yii\helpers\Html::activeTextarea()]]

Radios 和 checkboxes 在方法的声明上有一点点不同：

```php
<?= Html::radio('agree', true, ['label' => 'I agree']);
<?= Html::activeRadio($model, 'agree', ['class' => 'agreement'])

<?= Html::checkbox('agree', true, ['label' => 'I agree']);
<?= Html::activeCheckbox($model, 'agree', ['class' => 'agreement'])
```

Dropdown list 和 list box 将会如下渲染：

```php
<?= Html::dropDownList('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeDropDownList($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>

<?= Html::listBox('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeListBox($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>
```

第一个参数是 input 的名称，第二个是当前选中的值，第三个则是一个下标为列表值，值为列表标签的名值对数组。

如果你需要使用多项选择， checkbox list 应该能够符合你的需求：

```php
<?= Html::checkboxList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeCheckboxList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```

否则，用 radio list ：

```php
<?= Html::radioList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeRadioList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```


### Labels 和 Errors（Labels and Errors） <span id="labels-and-errors"></span>

如同 inputs 一样，Yii 也提供了两个方法用于生成表单 label。带 ative  方法用于从 model 中取数据，另外一个则是直接接收数据。

```php
<?= Html::label('User name', 'username', ['class' => 'label username']) ?>
<?= Html::activeLabel($user, 'username', ['class' => 'label username'])
```

为了从一个或者一组 model 中显示表单的概要错误，你可以使用如下方法：

```php
<?= Html::errorSummary($posts, ['class' => 'errors']) ?>
```

为了显示单个错误：

```php
<?= Html::error($post, 'title', ['class' => 'error']) ?>
```


### Input 的名和值（Input Names and Values） <span id="input-names-and-values"></span>

Yii 提供了方法用于从 model 中获取 input 的名称，ids，值。这些主要用于内部调用，
但是有时候你也需要使用它们：

```php
// Post[title]
echo Html::getInputName($post, 'title');

// post-title
echo Html::getInputId($post, 'title');

// my first post
echo Html::getAttributeValue($post, 'title');

// $post->authors[0]
echo Html::getAttributeValue($post, '[0]authors[0]');
```

在上面的例子中，第一个参数为模型，而第二个参数是属性表达式。在最简单的表单中，这个属性表达式就是属性名称，但是在一些多行输入的时候，它也可以是属性名以数组下标前缀或者后缀（也可能是同时）。

- `[0]content` 代表多行输入时第一个 model 的 content 属性的数据值。
- `dates[0]` 代表 dates 属性的第一个数组元素。
- `[0]dates[0]` 代表多行输入时第一个 model 的 dates 属性的第一个数组元素。

为了获取一个没有前缀或者后缀的属性名称，我们可以如下做：

```php
// dates
echo Html::getAttributeName('dates[0]');
```


## 样式表和脚本（Styles and Scripts） <span id="styles-and-scripts"></span>

Yii 提供两个方法用于生成包含内联样式和脚本代码的标签。

```php
<?= Html::style('.danger { color: #f00; }') ?>

Gives you

<style>.danger { color: #f00; }</style>


<?= Html::script('alert("Hello!");', ['defer' => true]);

Gives you

<script defer>alert("Hello!");</script>
```

如果你想要外联 css 样式文件，可以如下做：

```php
<?= Html::cssFile('@web/css/ie5.css', ['condition' => 'IE 5']) ?>

generates

<!--[if IE 5]>
    <link href="https://example.com/css/ie5.css" />
<![endif]-->
```

第一个参数是 URL。第二个参数是标签属性数组。比普通的标签配置项额外多出的是，你可以指定：

- `condition` 来让 `<link` 被条件控制注释包裹（ IE hacker ）。
  希望你在未来不再需要条件控制注释。
- `noscript` 可以被设置为 `true` ，这样 `<link`就会被 `<noscript>`包裹，如此那么这段代码只有在浏览器不支持 
  JavaScript 或者被用户禁用的时候才会被引入进来。

为了外联 JavaScript 文件：

```php
<?= Html::jsFile('@web/js/main.js') ?>
```

这个方法的第一个参数同 CSS 一样用于指定外联链接。第二个参数是一个标签属性数组。
同 `cssFile` 一样，你可以指定 `condtion` 配置项。


## 超链接（Hyperlinks） <span id="hyperlinks"></span>

有一个方法可以用于便捷的生成超链接：

```php
<?= Html::a('Profile', ['user/view', 'id' => $id], ['class' => 'profile-link']) ?>
```

第一个参数是超链接的标题。它不会被转码，所以如果是用户输入数据，
你需要使用 `Html::encode()` 方法进行转码。第二个参数是 `<a` 标签的 `href` 属性的值。
关于该参数能够接受的更详细的数据值，请参阅 [Url::to()](helper-url.md)。
第三个参数是标签的属性数组。

在需要的时候，你可以用如下代码生成 `mailto` 链接：

```php
<?= Html::mailto('Contact us', 'admin@example.com') ?>
```


## 图片（Images） <span id="images"></span>

为了生成图片标签，你可以如下做：

```php
<?= Html::img('@web/images/logo.png', ['alt' => 'My logo']) ?>

generates

<img src="https://example.com/images/logo.png" alt="My logo" />
```

除了 [aliases](concept-aliases.md) 之外，第一个参数可以接受 路由，查询，URLs。同 [Url::to()](helper-url.md) 一样。


## 列表（Lists） <span id="lists"></span>

无序列表可以如下生成：

```php
<?= Html::ul($posts, ['item' => function($item, $index) {
    return Html::tag(
        'li',
        $this->render('post', ['item' => $item]),
        ['class' => 'post']
    );
}]) ?>
```

有序列表请使用 `Html::ol()` 方法。
