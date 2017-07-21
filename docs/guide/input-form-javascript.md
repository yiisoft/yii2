Extending ActiveForm on the Client Side
=======================================

The [[yii\widgets\ActiveForm]] widget comes with a set of JavaScript methods that are used for client validation.
Its implementation is very flexible and allows you to extend it in different ways.
In the following these are described.

## ActiveForm events

ActiveForm triggers a series of dedicated events. Using the code like the following you can subscribe to these
events and handle them:

```javascript
$('#contact-form').on('beforeSubmit', function (e) {
	if (!confirm("Everything is correct. Submit?")) {
		return false;
	}
	return true;
});
```

In the following we'll review events available.

### `beforeValidate`

`beforeValidate` is triggered before validating the whole form.

The signature of the event handler should be:

```javascript
function (event, messages, deferreds)
```

where

- `event`: an Event object.
- `messages`: an associative array with keys being attribute IDs and values being error message arrays
   for the corresponding attributes.
- `deferreds`: an array of Deferred objects. You can use `deferreds.add(callback)` to add a new
   deferred validation.

If the handler returns a boolean `false`, it will stop further form validation after this event. And as
a result, `afterValidate` event will not be triggered.

### `afterValidate`

`afterValidate` event is triggered after validating the whole form.

The signature of the event handler should be:

```javascript
function (event, messages, errorAttributes)
```

where

- `event`: an Event object.
- `messages`: an associative array with keys being attribute IDs and values being error message arrays
   for the corresponding attributes.
- `errorAttributes`: an array of attributes that have validation errors. Please refer to
  `attributeDefaults` for the structure of this parameter.

### `beforeValidateAttribute`

`beforeValidateAttribute` event is triggered before validating an attribute.
The signature of the event handler should be:

```javascript
function (event, attribute, messages, deferreds)
```
     
where

- `event`: an Event object.
- `attribute`: the attribute to be validated. Please refer to `attributeDefaults` for the structure
   of this parameter.
- `messages`: an array to which you can add validation error messages for the specified attribute.
- `deferreds`: an array of Deferred objects. You can use `deferreds.add(callback)` to add
   a new deferred validation.

If the handler returns a boolean `false`, it will stop further validation of the specified attribute.
And as a result, `afterValidateAttribute` event will not be triggered.

### `afterValidateAttribute`

`afterValidateAttribute` event is triggered after validating the whole form and each attribute.

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

To learn more about the jQuery `ajax()` function, please refer to the [jQuery documentation](https://api.jquery.com/jQuery.ajax/).


## Adding fields dynamically

In modern web applications you often have the need of changing a form after it has been displayed to the user.
This can for example be the addition of new fields after click on a "plus"-icon.
To enable client validation for these fields, they have to be registered with the ActiveForm JavaScript plugin.

You have to add a field itself and then add it to validation list:

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

To remove a field from validation list so it's not validated you can do the following:

```javascript
$('#contact-form').yiiActiveForm('remove', 'address');
```
