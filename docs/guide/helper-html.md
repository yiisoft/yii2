Html helper
===========

Every web application generates lots of HTML markup. If the markup is static, it can be done efficiently by
[mixing PHP and HTML in a single file](https://www.php.net/manual/en/language.basic-syntax.phpmode.php), but when it is
generated dynamically it starts to get tricky to handle it without extra help. Yii provides such help in the form
of an Html helper, which provides a set of static methods for handling commonly used HTML tags, their options, and their content.

> Note: If your markup is nearly static, it's better to use HTML directly. There's no need to wrap absolutely everything
  in Html helper calls.


## Basics <span id="basics"></span>

Since building dynamic HTML by string concatenation can get messy very fast, Yii provides a set of methods to
manipulate tag options and build tags based on these options.


### Generating Tags <span id="generating-tags"></span>

The code for generating a tag looks like the following:

```php
<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
```

The first argument is the tag name. The second one is the content to be enclosed between the start and end tags.
Note that we are using `Html::encode` &mdash; that's because the content isn't encoded automatically to allow using HTML when needed. 
The third one is an array of HTML options, or in other words, tag attributes. 
In this array the key is the name of the attribute (such as `class`, `href` or `target`), and the value is its value.

The code above will generate the following HTML:

```html
<p class="username">samdark</p>
```

In case you need just an opening or closing tag, you can use the `Html::beginTag()` and `Html::endTag()` methods.

Options are used in many methods of the Html helper and various widgets. In all these cases there is some extra handling to
know about:

- If a value is `null`, the corresponding attribute will not be rendered.
- Attributes whose values are of boolean type will be treated as
  [boolean attributes](https://html.spec.whatwg.org/multipage/common-microsyntaxes.html#boolean-attributess).
- The values of attributes will be HTML-encoded using [[yii\helpers\Html::encode()|Html::encode()]].
- If the value of an attribute is an array, it will be handled as follows:
 
   * If the attribute is a data attribute as listed in [[yii\helpers\Html::$dataAttributes]], such as `data` or `ng`,
     a list of attributes will be rendered, one for each element in the value array. For example,
     `'data' => ['id' => 1, 'name' => 'yii']` generates `data-id="1" data-name="yii"`; and 
     `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` generates
     `data-params='{"id":1,"name":"yii"}' data-status="ok"`. Note that in the latter example JSON format is used
     to render a sub-array.
   * If the attribute is NOT a data attribute, the value will be JSON-encoded. For example,
     `['params' => ['id' => 1, 'name' => 'yii']` generates `params='{"id":1,"name":"yii"}'`.


### Forming CSS Classes and Styles <span id="forming-css"></span>

When building options for HTML tags we often start with defaults which we need to modify. In order to add or
remove a CSS class you can use the following:

```php
$options = ['class' => 'btn btn-default'];

if ($type === 'success') {
    Html::removeCssClass($options, 'btn-default');
    Html::addCssClass($options, 'btn-success');
}

echo Html::tag('div', 'Pwede na', $options);

// if the value of $type is 'success' it will render
// <div class="btn btn-success">Pwede na</div>
```

You may specify multiple CSS classes using the array style as well:

```php
$options = ['class' => ['btn', 'btn-default']];

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

You may also use the array style when adding or removing classes:

```php
$options = ['class' => 'btn'];

if ($type === 'success') {
    Html::addCssClass($options, ['btn-success', 'btn-lg']);
}

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-success btn-lg">Save</div>'
```

`Html::addCssClass()` prevents duplication, so you don't need to worry about the same class appearing twice:

```php
$options = ['class' => 'btn btn-default'];

Html::addCssClass($options, 'btn-default'); // class 'btn-default' is already present

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

If the CSS class option is specified using the array style, you may use a named key to mark the logical purpose of the class.
In this case, a class with the same key in the array style will be ignored in `Html::addCssClass()`:

```php
$options = [
    'class' => [
        'btn',
        'theme' => 'btn-default',
    ]
];

Html::addCssClass($options, ['theme' => 'btn-success']); // 'theme' key is already taken

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

CSS styles can be set up in similar way using the `style` attribute:

```php
$options = ['style' => ['width' => '100px', 'height' => '100px']];

// gives style="width: 100px; height: 200px; position: absolute;"
Html::addCssStyle($options, 'height: 200px; position: absolute;');

// gives style="position: absolute;"
Html::removeCssStyle($options, ['width', 'height']);
```

When using [[yii\helpers\Html::addCssStyle()|addCssStyle()]], you can specify either an array of key-value pairs,
corresponding to CSS property names and values, or a string such as `width: 100px; height: 200px;`. These formats
can be converted from one to the other using [[yii\helpers\Html::cssStyleFromArray()|cssStyleFromArray()]] and
[[yii\helpers\Html::cssStyleToArray()|cssStyleToArray()]]. The [[yii\helpers\Html::removeCssStyle()|removeCssStyle()]]
method accepts an array of properties to remove. If it's a single property, it can be specified as a string.


### Encoding and Decoding Content <span id="encoding-and-decoding-content"></span>

In order for content to be displayed properly and securely in HTML, special characters in the content should be encoded.
In PHP this is done with [htmlspecialchars](https://www.php.net/manual/en/function.htmlspecialchars.php) and
[htmlspecialchars_decode](https://www.php.net/manual/en/function.htmlspecialchars-decode.php). The issue with using
these methods directly is that you have to specify encoding and extra flags all the time. Since these flags are the same
all the time and the encoding should match the one of the application in order to prevent security issues, Yii provides two
compact and simple-to-use methods:

```php
$userName = Html::encode($user->name);
echo $userName;

$decodedUserName = Html::decode($userName);
```


## Forms <span id="forms"></span>

Dealing with form markup is quite repetitive and error prone. Because of that, there is a group of methods to help
dealing with them.

> Note: consider using [[yii\widgets\ActiveForm|ActiveForm]] in case you're dealing with models and need validation.


### Creating Forms <span id="creating-forms"></span>

Forms can be opened with [[yii\helpers\Html::beginForm()|beginForm()]] method like the following:

```php
<?= Html::beginForm(['order/update', 'id' => $id], 'post', ['enctype' => 'multipart/form-data']) ?>
```

The first argument is the URL the form will be submitted to. It can be specified in the form of a Yii route and parameters accepted by [[yii\helpers\Url::to()|Url::to()]].
The second one is the method to use. `post` is the default. The third one is an array of options
for the form tag. In this case we're changing the encoding of the form data in the POST request to `multipart/form-data`,
which is required in order to upload files.

Closing the form tag is simple:

```php
<?= Html::endForm() ?>
```


### Buttons <span id="buttons"></span>

In order to generate buttons, you can use the following code:

```php
<?= Html::button('Press me!', ['class' => 'teaser']) ?>
<?= Html::submitButton('Submit', ['class' => 'submit']) ?>
<?= Html::resetButton('Reset', ['class' => 'reset']) ?>
```

The first argument for all three methods is the button title, and the second one is an array of options.
The title isn't encoded, so if you're displaying data from the end user, encode it with [[yii\helpers\Html::encode()|Html::encode()]].


### Input Fields <span id="input-fields"></span>

There are two groups of input methods. The ones starting with `active`, which are called active inputs, and the ones not starting with it.
Active inputs take data from the model and attribute specified, while in the case of a regular input, data is specified
directly.

The most generic methods are:

```php
type, input name, input value, options
<?= Html::input('text', 'username', $user->name, ['class' => $username]) ?>

type, model, model attribute name, options
<?= Html::activeInput('text', $user, 'name', ['class' => $username]) ?>
```

If you know the input type in advance, it's more convenient to use the shortcut methods:

- [[yii\helpers\Html::buttonInput()]]
- [[yii\helpers\Html::submitInput()]]
- [[yii\helpers\Html::resetInput()]]
- [[yii\helpers\Html::textInput()]], [[yii\helpers\Html::activeTextInput()]]
- [[yii\helpers\Html::hiddenInput()]], [[yii\helpers\Html::activeHiddenInput()]]
- [[yii\helpers\Html::passwordInput()]] / [[yii\helpers\Html::activePasswordInput()]]
- [[yii\helpers\Html::fileInput()]], [[yii\helpers\Html::activeFileInput()]]
- [[yii\helpers\Html::textarea()]], [[yii\helpers\Html::activeTextarea()]]

Radios and checkboxes are a bit different in terms of method signature:

```php
<?= Html::radio('agree', true, ['label' => 'I agree']) ?>
<?= Html::activeRadio($model, 'agree', ['class' => 'agreement']) ?>

<?= Html::checkbox('agree', true, ['label' => 'I agree']) ?>
<?= Html::activeCheckbox($model, 'agree', ['class' => 'agreement']) ?>
```

Dropdown lists and list boxes can be rendered like the following:

```php
<?= Html::dropDownList('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeDropDownList($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>

<?= Html::listBox('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeListBox($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>
```

The first argument is the name of the input, the second one is the value that's currently selected, and the third one is an array of key-value pairs, where the array key is the list value and the array value is the list label.

If you want multiple choices to be selectable, you can use a checkbox list:

```php
<?= Html::checkboxList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeCheckboxList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```

If not, use radio list:

```php
<?= Html::radioList('roles', [16, 42], ArrayHelper::map($roleModels, 'id', 'name')) ?>
<?= Html::activeRadioList($user, 'role', ArrayHelper::map($roleModels, 'id', 'name')) ?>
```


### Labels and Errors <span id="labels-and-errors"></span>

Same as inputs, there are two methods for generating form labels. Active, which takes data from the model, and non-active, which accepts data directly:

```php
<?= Html::label('User name', 'username', ['class' => 'label username']) ?>
<?= Html::activeLabel($user, 'username', ['class' => 'label username']) ?>
```

In order to display form errors from a model or models as a summary, you could use:

```php
<?= Html::errorSummary($posts, ['class' => 'errors']) ?>
```

To display an individual error:

```php
<?= Html::error($post, 'title', ['class' => 'error']) ?>
```


### Input Names and Values <span id="input-names-and-values"></span>

There are methods to get names, ids and values for input fields based on the model. These are mainly used internally,
but could be handy sometimes:

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

In the above, the first argument is the model, while the second one is the attribute expression. In its simplest form the expression is just an attribute name, but it can be an attribute name prefixed and/or suffixed with array indexes, which is mainly used for tabular input:

- `[0]content` is used in tabular data input to represent the `content` attribute for the first model in tabular input;
- `dates[0]` represents the first array element of the `dates` attribute;
- `[0]dates[0]` represents the first array element of the `dates` attribute for the first model in tabular input.

In order to get the attribute name without suffixes or prefixes, one can use the following:

```php
// dates
echo Html::getAttributeName('dates[0]');
```


## Styles and Scripts <span id="styles-and-scripts"></span>

There are two methods to generate tags wrapping embedded styles and scripts:

```php
<?= Html::style('.danger { color: #f00; }', ['media' => 'print']) ?>

Gives you

<style media="print">.danger { color: #f00; }</style>


<?= Html::script('alert("Hello!");') ?>

Gives you

<script>alert("Hello!");</script>
```

If you want to use an external style in a CSS file:

```php
<?= Html::cssFile('@web/css/ie5.css', ['condition' => 'IE 5']) ?>

generates

<!--[if IE 5]>
    <link href="https://example.com/css/ie5.css" />
<![endif]-->
```

The first argument is the URL. The second one is an array of options. In addition to the regular options, you can specify:

- `condition` to wrap `<link` in conditional comments with the specified condition. Hope you won't need conditional
  comments ever ;)
- `noscript` can be set to `true` to wrap `<link` in a `<noscript>` tag so it will be included only when there's
  either no JavaScript support in the browser or it was disabled by the user.

To link a JavaScript file:

```php
<?= Html::jsFile('@web/js/main.js') ?>
```

Same as with CSS, the first argument specifies the URL of the file to be included. Options can be passed as the second argument.
In the options you can specify `condition` in the same way as in the options for `cssFile`.


## Hyperlinks <span id="hyperlinks"></span>

There's a method to generate hyperlinks conveniently:

```php
<?= Html::a('Profile', ['user/view', 'id' => $id], ['class' => 'profile-link']) ?>
```

The first argument is the title. It's not encoded, so if you're using data entered by the user, you need to encode it with
`Html::encode()`. The second argument is what will be in the `href` attribute of the `<a` tag.
See [Url::to()](helper-url.md) for details on what values it accepts.
The third argument is an array of tag attributes.

If you need to generate `mailto` links, you can use the following code:

```php
<?= Html::mailto('Contact us', 'admin@example.com') ?>
```


## Images <span id="images"></span>

In order to generate an image tag, use the following:

```php
<?= Html::img('@web/images/logo.png', ['alt' => 'My logo']) ?>

generates

<img src="https://example.com/images/logo.png" alt="My logo" />
```

Besides [aliases](concept-aliases.md), the first argument can accept routes, parameters and URLs, in the same way [Url::to()](helper-url.md) does.


## Lists <span id="lists"></span>

Unordered list can be generated like the following:

```php
<?= Html::ul($posts, ['item' => function($item, $index) {
    return Html::tag(
        'li',
        $this->render('post', ['item' => $item]),
        ['class' => 'post']
    );
}]) ?>
```

In order to get ordered list, use `Html::ol()` instead.
