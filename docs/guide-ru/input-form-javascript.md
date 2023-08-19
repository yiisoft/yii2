Расширение ActiveForm на стороне клиента
=======================================

Виджет [[yii\widgets\ActiveForm]] поставляется с набором JavaScript методов, которые используются для проверки на стороне клиента.
Его реализация очень гибкая и позволяет расширять его различными способами.

## ActiveForm события

ActiveForm запускает серию специальных событий. Используя код, подобный следующему, вы можете подписаться на эти события и обрабатывать их:

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

- `event`: объект `Event`
- `messages`: ассоциативный массив с ключами, являющимися идентификаторами атрибутов, и значениями, являющимися массивами сообщений об ошибках для соответствующих атрибутов
- `deferreds`: массив отложенных объектов. Вы можете использовать `deferreds.add(callback)`, чтобы добавить новую отложенную проверку

Если обработчик возвращает логическое `false`, он остановит дальнейшую проверку формы после этого события. И, как результат, событие `afterValidate` не будет запущено.

### `afterValidate`

`afterValidate` событие запускается после проверки всей формы.

Сигнатура обработчика события должна быть:

```javascript
function (event, messages, errorAttributes) {}
```

где

- `event`: объект `Event`
- `messages`: ассоциативный массив с ключами, являющимися идентификаторами атрибутов, и значениями, являющимися массивами сообщений об ошибках для соответствующих атрибутов
- `errorAttributes`: массив атрибутов, которые имеют ошибки проверки. Пожалуйста, обратитесь к `attributeDefaults` для просмотра структуры этого параметра

### `beforeValidateAttribute`

`beforeValidateAttribute` событие инициируется перед проверкой атрибута.

Сигнатура обработчика события должна быть:

```javascript
function (event, attribute, messages, deferreds)
```

где

- `event`: объект `Event`
- `attribute`: атрибут для проверки. Пожалуйста, обратитесь к `attributeDefaults` для просмотра структуры этого параметра
- `messages`: массив, в который вы можете добавить сообщения об ошибках проверки для указанного атрибута
- `deferreds`: массив отложенных объектов. Вы можете использовать `deferreds.add (callback)`, чтобы добавить новую отложенную проверку

Если обработчик возвращает логическое `false`, он остановит дальнейшую проверку указанного атрибута.
И, как результат, событие `afterValidateAttribute` не будет запущено.

### `afterValidateAttribute`

`afterValidateAttribute` событие запускается после проверки всей формы и каждого атрибута.

Сигнатура обработчика события должна быть:

```javascript
function (event, attribute, messages)
```

где

- `event`: объект `Event`
- `attribute`: проверяемый атрибут. Пожалуйста, обратитесь к `attributeDefaults` для просмотра структуры этого параметра
- `messages`: массив, в который вы можете добавить дополнительные сообщения об ошибках валидации для указанного атрибута

### `beforeSubmit`

`beforeSubmit` событие запускается перед отправкой формы после того, как все проверки пройдены.

Сигнатура обработчика события должна быть:

```javascript
function (event)
```

где событие является объектом `Event`.

Если обработчик возвращает логическое значение `false`, он остановит отправку формы.

### `ajaxBeforeSend`
         
`ajaxBeforeSend` событие инициируется перед отправкой AJAX запроса для проверки основанной на AJAX.

Сигнатура обработчика события должна быть:

```javascript
function (event, jqXHR, settings)
```

где

- `event`: объект `Event`
- `jqXHR`: объект `jqXHR`
- `settings`: настройки для AJAX запроса

### `ajaxComplete`

`ajaxComplete` событие запускается после выполнения AJAX запроса для проверки основанной на AJAX.

Сигнатура обработчика события должна быть:

```javascript
function (event, jqXHR, textStatus)
```

где

- `event`: объект `Event`
- `jqXHR`: объект `jqXHR`
- `textStatus`: статус запроса ("success", "notmodified", "error", "timeout", "abort", или "parsererror")

## Отправка формы через AJAX

Хотя проверка может быть выполнена на стороне клиента или с помощью AJAX-запроса, сама отправка формы, по умолчанию, выполняется как обычный запрос.
Если вы хотите, чтобы форма была отправлена через AJAX, вы можете достичь этого, обработав событие `beforeSubmit` формы следующим образом:

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

Для удаления поля из списка проверки, чтобы оно не было проверено, выполните следующие действия:

```javascript
$('#contact-form').yiiActiveForm('remove', 'address');
```
