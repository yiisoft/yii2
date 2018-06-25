在客户端扩展 ActiveForm
=====================

[[yii\widgets\ActiveForm]] 小部件附带一组用于客户端验证的 JavaScript 方法。
它的实现非常灵活，可以让你以不同的方式扩展它。
下面我们来看描述。

## ActiveForm 事件

ActiveForm 触发一系列专用事件。使用类似以下的代码，您可以订阅这些代码
事件并处理它们：

```javascript
$('#contact-form').on('beforeSubmit', function (e) {
	if (!confirm("Everything is correct. Submit?")) {
		return false;
	}
	return true;
});
```

在下文中，我们将查看可用的事件。

### `beforeValidate`

`beforeValidate` 是在验证整个表单之前触发的。

事件处理程序的签名应该是：

```javascript
function (event, messages, deferreds)
```

其中

- `event`: 一个 Event 对象。
- `messages`: 一个关联数组，其中键是属性ID，
  值是相应属性的错误消息数组。
- `deferreds`: 一个 Deferred 对象数组。你可以使用 `deferreds.add(callback)`
  来添加一个新的延迟验证。

如果处理程序返回一个布尔型 `false`，它将在此事件后停止进一步的表单验证。
结果 `afterValidate` 事件将不会被触发。

### `afterValidate`

`afterValidate` 是在验证整个表单后触发的。

事件处理程序的签名应该是：

```javascript
function (event, messages, errorAttributes)
```

其中

- `event`: 一个 Event 对象。
- `messages`: 一个关联数组，其中键是属性ID，
  值是相应属性的错误消息数组。
- `errorAttributes`: 一个具有验证错误的属性数组。有关此参数的结构，
  请参阅 `attributeDefaults`。

### `beforeValidateAttribute`

`beforeValidateAttribute` 事件是在验证属性之前触发的。
事件处理程序的签名应该是：

```javascript
function (event, attribute, messages, deferreds)
```
     
其中

- `event`: 一个 Event 对象。
- `attribute`: 要验证的属性。 请参阅这个参数的 `attributeDefaults` 
  结构。
- `messages`: 可以为其添加指定属性的验证错误消息的数组。
- `deferreds`: 一个 Deferred 对象数组。你可以使用 `deferreds.add(callback)`
  来添加一个新的延迟验证。

如果处理程序返回布尔型 `false`，它将停止进一步验证指定的属性。
结果，`afterValidateAttribute` 事件将不会被触发。

### `afterValidateAttribute`

`afterValidateAttribute` 事件在验证整个表单和每个属性后触发的。

事件处理程序的签名应该是：

```javascript
function (event, attribute, messages)
```

其中

- `event`: 一个 Event 对象。
- `attribute`: 该属性会被验证。有关此参数的结构，
  请参阅 `attributeDefaults`。
- `messages`: 可以为其添加指定属性的其他验证错误消息的
  数组。

### `beforeSubmit`

`beforeSubmit` 是在所有验证通过后且提交表单之前触发的。

事件处理程序的签名应该是：

```javascript
function (event)
```

其中 `event` 是一个 Event 对象。

如果处理返回布尔型“false”，它将停止表单提交。

### `ajaxBeforeSend`
         
`ajaxBeforeSend` 事件是在发送用于基于AJAX的验证的AJAX请求之前触发的。

事件处理程序的签名应该是：

```javascript
function (event, jqXHR, settings)
```

其中

- `event`: 一个 Event 对象。
- `jqXHR`: 一个 jqXHR 对象
- `settings`: AJAX 请求的设置

### `ajaxComplete`

`ajaxComplete` 事件是在完成基于 AJAX 验证的 AJAX 请求后触发的。

事件处理程序的签名应该是：

```javascript
function (event, jqXHR, textStatus)
```

where

- `event`: 一个 Event 对象。
- `jqXHR`: 一个 jqXHR 对象
- `textStatus`: 请求的状态 ("success", "notmodified", "error", "timeout",
"abort", or "parsererror")。

## 通过 AJAX 提交表单

虽然可以在客户端或通过 AJAX 请求进行验证，但作为默认的正常请求
表单提交本身已完成。如果你想通过 AJAX 提交表单，你可以
通过以下方式处理表单的 `beforeSubmit` 事件做到这一点：

```javascript
var $form = $('#formId');
$form.on('beforeSubmit', function() {
    var data = $form.serialize();
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: data,
        success: function (data) {
            // 执行成功
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
     });
     return false; // 防止默认提交
});
```

要了解更多关于 jQuery `ajax()` 函数的信息，请参阅 [jQuery 文档](https://api.jquery.com/jQuery.ajax/)。


## 动态添加字段

在现代 Web 应用程序中，您经常需要在向用户显示表单后更改表单。
这可以例如在点击“加号”图标之后添加新的字段。
要为这些字段启用客户端验证，他们必须注册 ActiveForm JavaScript 插件。

您必须自行添加一个字段，然后将其添加到验证列表中：

```javascript
$('#contact-form').yiiActiveForm('add', {
    id: 'address',
    name: 'address',
    container: '.field-address',
    input: '#address',
    error: '.help-block',
    validate:  function (attribute, value, messages, deferred, $form) {
        yii.validation.required(value, messages, {message: "Validation Message Here"});
    }
});
```

要从验证列表中删除一个字段，使它不被验证，您可以执行以下操作：

```javascript
$('#contact-form').yiiActiveForm('remove', 'address');
```
