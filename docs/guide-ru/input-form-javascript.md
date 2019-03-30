Расширение ActiveForm на стороне клиента
=======================================

Виджет [[yii\widgets\ActiveForm]] поставляется с набором JavaScript методов, которые используются для проверки на стороне клиента.
Его реализация очень гибкая и позволяет расширять его различными способами.
Ниже они описаны.

## ActiveForm события

ActiveForm запускает серию специальных событий. Используя код, подобный следующему, вы можете подписаться на эти
события и обрабатывать их:

```javascript
$('#contact-form').on('beforeSubmit', function (e) {
	if (!confirm("Everything is correct. Submit?")) {
		return false;
	}
	return true;
});
```

Ниже мы рассмотрим доступные события.

### `beforeValidate`

`beforeValidate` запускается перед проверкой всей формы.

Сигнатура обработчика событий должна быть:

```javascript
function (event, messages, deferreds) {}
```

где

- `event`: объект события.
- `messages`: ассоциативный массив с ключами, являющимися идентификаторами атрибутов, и значениями, являющимися массивами сообщений об ошибках для соответствующих атрибутов.
- `deferreds`: массив отложенных объектов. Вы можете использовать `deferreds.add(callback)`, чтобы добавить новую отложенную проверку.

Если обработчик возвращает логическое `false`, он остановит дальнейшую проверку формы после этого события. И, как результат, событие `afterValidate` не будет запущено.

### `afterValidate`

`afterValidate` событие запускается после проверки всей формы.

Сигнатура обработчика события должна быть:

```javascript
function (event, messages, errorAttributes) {}
```

где

- `event`: объект события.
- `messages`: ассоциативный массив с ключами, являющимися идентификаторами атрибутов, и значениями, являющимися массивами сообщений об ошибках для соответствующих атрибутов.
- `errorAttributes`: массив атрибутов, которые имеют ошибки проверки. Пожалуйста, обратитесь к `attributeDefaults` для просмотра структуры этого параметра.

### `beforeValidateAttribute`

`beforeValidateAttribute` событие инициируется перед проверкой атрибута.

Подпись обработчика события должна быть:

```javascript
function (event, attribute, messages, deferreds){}
```

где

- `event`: объект события.
- `attribute`: атрибут для проверки. Пожалуйста, обратитесь к `attributeDefaults` для просмотра структуры
- `messages`: массив, в который вы можете добавить сообщения об ошибках проверки для указанного атрибута.
- `deferreds`: массив отложенных объектов. Вы можете использовать `deferreds.add (callback)`, чтобы добавить новую отложенную проверку.

Если обработчик возвращает логическое `false`, он остановит дальнейшую проверку указанного атрибута.
И, как результат, событие `afterValidateAttribute` не будет запущено.

### `afterValidateAttribute`

`afterValidateAttribute` событие запускается после проверки всей формы и каждого атрибута.

The signature of the event handler should be:

```javascript
function (event, attribute, messages)
```

where

- `event`: an Event object.
- `attribute`: the attribute being validated. Please refer to `attributeDefaults` for the structure
   of this parameter.
- `messages`: an array to which you can add additional validation error messages for the specified
   attribute.

### `beforeSubmit`

`beforeSubmit` event is triggered before submitting the form after all validations have passed.

The signature of the event handler should be:

```javascript
function (event)
```

where event is an Event object.

If the handler returns a boolean `false`, it will stop form submission.

### `ajaxBeforeSend`
         
`ajaxBeforeSend` event is triggered before sending an AJAX request for AJAX-based validation.

The signature of the event handler should be:

```javascript
function (event, jqXHR, settings)
```

where

- `event`: an Event object.
- `jqXHR`: a jqXHR object
- `settings`: the settings for the AJAX request

### `ajaxComplete`

`ajaxComplete` event is triggered after completing an AJAX request for AJAX-based validation.

The signature of the event handler should be:

```javascript
function (event, jqXHR, textStatus)
```

where

- `event`: an Event object.
- `jqXHR`: a jqXHR object
- `textStatus`: the status of the request ("success", "notmodified", "error", "timeout",
"abort", or "parsererror").

## Submitting the form via AJAX

While validation can be made on client side or via AJAX request, the form submission itself is done
as a normal request by default. If you want the form to be submitted via AJAX, you can achieve this
by handling the `beforeSubmit` event of the form in the following way:

```javascript
var $form = $('#formId');
$form.on('beforeSubmit', function() {
    var data = $form.serialize();
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: data,
        success: function (data) {
            // Implement successful
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
     });
     return false; // prevent default submit
});
```
Чтобы узнать больше о jQuery функции `ajax()`, обратитесь к документации [jQuery documentation](https://api.jquery.com/jQuery.ajax/).


## Добавления полей динамически

В современных веб-приложениях часто возникает необходимость изменения формы после ее отображения пользователю.
Это может быть, например, добавление новых полей после нажатия на значок "плюс".
Чтобы включить проверку, на стороне клиента, для этих полей, они должны быть зарегистрированы с помощью JavaScript плагина Active Form.

Вы должны добавить само поле, а затем добавить его в список проверки:

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

Для даления поля из списка проверки, чтобы оно не было проверено, выполните следующие действия:

```javascript
$('#contact-form').yiiActiveForm('remove', 'address');
```
