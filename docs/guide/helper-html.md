Html helper
===========

Every web application generates lots of HTML markup. If markup is static, it could be done efficiently by
[mixing PHP and HTML in a single file](http://php.net/manual/en/language.basic-syntax.phpmode.php) but when it is
generated dynamically it is starting to get tricky to handle it without extra help. Yii provides such help in a form
of Html helper which provides a set of static methods for handling commonly used HTML tags, their options and content.

> Note: If your markup is nearly static it's better to use HTML directly. There's no need to wrap absolutely everything
  with Html helper calls.


## Basics <span id="basics"></span>

Since building dynamic HTML by string concatenation is getting messy very fast, Yii provides a set of methods to
manipulate tag options and build tags based on these options.


### Generating Tags <span id="generating-tags"></span>

The code generating a tag looks like the following:

```php
<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
```

The first argument is tag name. Second one is content to be enclosed between the start and end tags. Note that we are
using `Html::encode`. That's because content isn't encoded automatically to allow using HTML when needed. Third one is an
array of HTML options or, in other words, tag attributes. In this array key is the name of the attribute such as `class`,
`href` or `target` and a value is its value.

The code above will generate the following HTML:

```html
<p class="username">samdark</p>
```

In case you need just start tag or just closing tag you can use `Html::beginTag()` and `Html::endTag()` methods.

Options are used in many methods of Html helper and various widgets. In all these cases there is some extra handling to
know about:

- If a value is null, the corresponding attribute will not be rendered.
- Attributes whose values are of boolean type will be treated as
  [boolean attributes](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
- The values of attributes will be HTML-encoded using [[yii\helpers\Html::encode()|Html::encode()]].
- If the value of an attribute is an array, it will be handled as follows:
 
   * If the attribute is a data attribute as listed in [[yii\helpers\Html::$dataAttributes]], such as `data` or `ng`,
     a list of attributes will be rendered, one for each element in the value array. For example,
     `'data' => ['id' => 1, 'name' => 'yii']` generates `data-id="1" data-name="yii"`; and 
     `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` generates
     `data-params='{"id":1,"name":"yii"}' data-status="ok"`. Note that in the latter example, JSON format is used
     to render a sub-array.
   * If the attribute is NOT a data attribute, the value will be JSON-encoded. For example,
     `['params' => ['id' => 1, 'name' => 'yii']` generates `params='{"id":1,"name":"yii"}'`.


### Forming CSS Classes and Styles <span id="forming-css"></span>

When building options for HTML tag we're often starting with defaults which we need to modify. In order to add or
remove CSS class you can use the following:

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

You may specify multiple CSS classes using the array style as well:

```php
$options = ['class' => ['btn', 'btn-default']];

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

While adding or removing classes you may use the array format as well:

```php
$options = ['class' => 'btn'];

if ($type === 'success') {
    Html::addCssClass($options, ['btn-success', 'btn-lg']);
}

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-success btn-lg">Save</div>'
```

`Html::addCssClass()` prevents duplicating classes, so you don't need to worry that the same class may appear twice:

```php
$options = ['class' => 'btn btn-default'];

Html::addCssClass($options, 'btn-default'); // class 'btn-default' is already present

echo Html::tag('div', 'Save', $options);
// renders '<div class="btn btn-default">Save</div>'
```

If the CSS class option is specified via the array format, you may use a named key to mark the logical purpose of the class.
In this case, a class with the same key in the array format will be ignored in `Html::addCssClass()`:

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

CSS styles can be setup in similar way using `style` attribute:

```php
$options = ['style' => ['width' => '100px', 'height' => '100px']];

// gives style="width: 100px; height: 200px; position: absolute;"
Html::addCssStyle($options, 'height: 200px; position: absolute;');

// gives style="position: absolute;"
Html::removeCssStyle($options, ['width', 'height']);
```

When using [[yii\helpers\Html::addCssStyle()|addCssStyle()]] you can specify either an array of key-value pairs
corresponding to CSS property names and values or a string such as `width: 100px; height: 200px;`. These formats
could be converted there and forth by using [[yii\helpers\Html::cssStyleFromArray()|cssStyleFromArray()]] and
[[yii\helpers\Html::cssStyleToArray()|cssStyleToArray()]]. The [[yii\helpers\Html::removeCssStyle()|removeCssStyle()]]
method accepts an array of properties to remove. If it's going to be a single property it could be specified as string.


### Encoding and Decoding Content <span id="encoding-and-decoding-content"></span>

In order for content to be displayed properly and securely in HTML special characters in the content should be encoded.
In PHP it's done with [htmlspecialchars](http://www.php.net/manual/en/function.htmlspecialchars.php) and
[htmlspecialchars_decode](http://www.php.net/manual/en/function.htmlspecialchars-decode.php). The issue with using
these methods directly is that you have to specify encoding and extra flags all the time. Since flags are the same
all the time and encoding should match the one of the application in order to prevent security issues, Yii provides two
compact and simple to use methods:

```php
$userName = Html::encode($user->name);
echo $userName;

$decodedUserName = Html::decode($userName);
```


## Forms <span id="forms"></span>

Dealing with forms markup is quite repetitive and error prone. Because of that there is a group of methods to help
dealing with them.

> Note: consider using [[yii\widgets\ActiveForm|ActiveForm]] in case you deal with models and need validation.


### Creating Forms <span id="creating-forms"></span>

Form could be opened with [[yii\helpers\Html::beginForm()|beginForm()]] method like the following:

```php
<?= Html::beginForm(['order/update', 'id' => $id], 'post', ['enctype' => 'multipart/form-data']) ?>
```

First argument is the URL form will be submitted to. It could be specified in form of Yii route and parameters accepted
by [[yii\helpers\Url::to()|Url::to()]]. Second one is method to use. `post` is default. Third one is array of options
for the form tag. In this case we're changing the way of encoding form data in POST request to `multipart/form-data`.
It is required in order to upload files.

Closing form tag is simple:

```php
<?= Html::endForm() ?>
```


### Buttons <span id="buttons"></span>

In order to generate buttons you can use the following code:

```php
<?= Html::button('Press me!', ['class' => 'teaser']) ?>
<?= Html::submitButton('Submit', ['class' => 'submit']) ?>
<?= Html::resetButton('Reset', ['class' => 'reset']) ?>
```

First argument for all three methods is button title and the second one is options. Title isn't encoded so if you're
getting data from end user, encode it with [[yii\helpers\Html::encode()|Html::encode()]].


### Input Fields <span id="input-fields"></span>

There are two groups on input methods. The ones starting with `active` and called active inputs and the ones not starting
with it. Active inputs are taking data from model and attribute specified while in case of regular input data is specified
directly.

The most generic methods are:

```php
type, input name, input value, options
<?= Html::input('text', 'username', $user->name, ['class' => $username]) ?>

type, model, model attribute name, options
<?= Html::activeInput('text', $user, 'name', ['class' => $username]) ?>
```

If you know input type in advance it's more convenient to use shortcut methods:

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
<?= Html::radio('agree', true, ['label' => 'I agree']);
<?= Html::activeRadio($model, 'agree', ['class' => 'agreement'])

<?= Html::checkbox('agree', true, ['label' => 'I agree']);
<?= Html::activeCheckbox($model, 'agree', ['class' => 'agreement'])
```

Dropdown list and list box could be rendered like the following:

```php
<?= Html::dropDownList('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeDropDownList($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>

<?= Html::listBox('list', $currentUserId, ArrayHelper::map($userModels, 'id', 'name')) ?>
<?= Html::activeListBox($users, 'id', ArrayHelper::map($userModels, 'id', 'name')) ?>
```

First argument is the name of the input, second is the value that's currently selected and third is key-value pairs where
array key is list value and array value is list label.

If you want multiple choices to be selectable, checkbox list is a good match:

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

Same as inputs there are two methods for generating form labels. Active that's taking data from the model and non-active
that accepts data directly:

```php
<?= Html::label('User name', 'username', ['class' => 'label username']) ?>
<?= Html::activeLabel($user, 'username', ['class' => 'label username'])
```

In order to display form errors from a model or models as a summary you could use:

```php
<?= Html::errorSummary($posts, ['class' => 'errors']) ?>
```

To display individual error:

```php
<?= Html::error($post, 'title', ['class' => 'error']) ?>
```


### Input Names and Values <span id="input-names-and-values"></span>

There are methods to get names, ids and values for input fields based on the model. These are mainly used internally
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

In the above first argument is the model while the second one is attribute expression. In its simplest form it's
attribute name but it could be an attribute name prefixed and/or suffixed with array indexes which are mainly used
for tabular input:

- `[0]content` is used in tabular data input to represent the "content" attribute for the first model in tabular input;
- `dates[0]` represents the first array element of the "dates" attribute;
- `[0]dates[0]` represents the first array element of the "dates" attribute for the first model in tabular input.

In order to get attribute name without suffixes or prefixes one can use the following:

```php
// dates
echo Html::getAttributeName('dates[0]');
```


## Styles and Scripts <span id="styles-and-scripts"></span>

There two methods to generate tags wrapping embedded styles and scripts:

```php
<?= Html::style('.danger { color: #f00; }') ?>

Gives you

<style>.danger { color: #f00; }</style>


<?= Html::script('alert("Hello!");', ['defer' => true]);

Gives you

<script defer>alert("Hello!");</script>
```

If you want to link external style from CSS file:

```php
<?= Html::cssFile('@web/css/ie5.css', ['condition' => 'IE 5']) ?>

generates

<!--[if IE 5]>
    <link href="http://example.com/css/ie5.css" />
<![endif]-->
```

First argument is the URL. Second is an array of options. Additionally to regular options you could specify:

- `condition` to wrap `<link` with conditional comments with condition specified. Hope you won't need conditional
  comments ever ;)
- `noscript` could be set to `true` to wrap `<link` with `<noscript>` tag so it will be included only when there's
  either no JavaScript support in the browser or it was disabled by the user.

To link JavaScript file:

```php
<?= Html::jsFile('@web/js/main.js') ?>
```

Same as with CSS first argument specifies link to the file to be included. Options could be passed as the second argument.
In options you can specify `condition` in the same way as in options for `cssFile`.


## Hyperlinks <span id="hyperlinks"></span>

There's a method to generate hyperlink conveniently:

```php
<?= Html::a('Profile', ['user/view', 'id' => $id], ['class' => 'profile-link']) ?>
```

The first argument is the title. It's not encoded so if you're using data got from user you need to encode it with
`Html::encode()`. Second argument is what will be in `href` of `<a` tag. See [Url::to()](helper-url.md) for details on
what values it accepts. Third argument is array of tag properties.

In you need to generate `mailto` link you can use the following code:

```php
<?= Html::mailto('Contact us', 'admin@example.com') ?>
```


## Images <span id="images"></span>

In order to generate image tag use the following:

```php
<?= Html::img('@web/images/logo.png', ['alt' => 'My logo']) ?>

generates

<img src="http://example.com/images/logo.png" alt="My logo" />
```

Aside [aliases](concept-aliases.md) the first argument can accept routes, parameters and URLs. Same way as
[Url::to()](helper-url.md) does.


## Lists <span id="lists"></span>

Unordered list could be generated like the following:

```php
<?= Html::ul($posts, ['item' => function($item, $index) {
    return Html::tag(
        'li',
        $this->render('post', ['item' => $item]),
        ['class' => 'post']
    );
}]) ?>
```

In order to get ordered list use `Html::ol()` instead.
