Rozszerzanie ActiveForm po stronie klienta
==========================================

Widżet [[yii\widgets\ActiveForm]] posiada szereg wbudowanych metod JavaScript, służących do walidacji po stronie klienta.
Ich implementacja jest bardzo elastyczna i pozwala na rozszerzanie ich na wiele sposobów.

## Zdarzenia ActiveForm

ActiveForm wyzwala serie dedykowanych zdarzeń. Używając poniższego kodu, można przechwycić te zdarzenia i je obsłużyć:

```javascript
$('#contact-form').on('beforeSubmit', function (e) {
	if (!confirm("Wszystko jest w porządku. Wysłać formularz?")) {
		return false;
	}
	return true;
});
```

Poniżej znajdziesz opis dostępnych zdarzeń.

### `beforeValidate`

`beforeValidate` jest wyzwalane przed walidacją całego formularza.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event, messages, deferreds)
```

gdzie

- `event`: obiekt Event.
- `messages`: asocjacyjna tablica, gdzie kluczami są ID atrybutów, a wartościami tablice opisów błędów dla tych atrybutów.
- `deferreds`: tablica obiektów kolejkujących. Możesz użyć `deferreds.add(callback)`, aby dodać nową walidację do kolejki.

Jeśli metoda obsługująca zwróci boolean `false`, zatrzyma dalszą walidację formularza. W takim wypadku zdarzenie 
`afterValidate` nie będzie już wyzwalane.

### `afterValidate`

`afterValidate` jest wyzwalane po walidacji całego formularza.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event, messages, errorAttributes)
```

gdzie

- `event`: obiekt Event.
- `messages`: asocjacyjna tablica, gdzie kluczami są ID atrybutów, a wartościami tablice opisów błędów dla tych atrybutów.
- `errorAttributes`: tablica atrybutów z błędami walidacji. Sprawdź konstrukcję `attributeDefaults`, aby dowiedzieć się więcej o strukturze tego parametru.

### `beforeValidateAttribute`

`beforeValidateAttribute` jest wyzwalane przed walidacją atrybutu.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event, attribute, messages, deferreds)
```
     
gdzie

- `event`: obiekt Event.
- `attribute`: atrybut poddawany walidacji. Sprawdź konstrukcję `attributeDefaults`, aby dowiedzieć się więcej o strukturze tego parametru.
- `messages`: tablica, do której możesz dodać opisy błędów walidacji dla wybranego atrybutu.
- `deferreds`: tablica obiektów kolejki. Możesz użyć `deferreds.add(callback)`, aby dodać nową walidację do kolejki.

Jeśli metoda obsługująca zwróci boolean `false`, zatrzyma dalszą walidację wybranego atrybutu. W takim wypadku zdarzenie 
`afterValidateAttribute` nie będzie już wyzwalane.

### `afterValidateAttribute`

`afterValidateAttribute` jest wyzwalane po walidacji całego formularza i każdego atrybutu.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event, attribute, messages)
```

gdzie

- `event`: obiekt Event.
- `attribute`: atrybut poddawany walidacji. Sprawdź konstrukcję `attributeDefaults`, aby dowiedzieć się więcej o strukturze tego parametru.
- `messages`: tablica, do której możesz dodać opisy błędów walidacji dla wybranego atrybutu.

### `beforeSubmit`

`beforeSubmit` jest wyzwalane przed wysłaniem formularza, po pomyślnej walidacji.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event)
```

gdzie event jest obiektem Event.

Jeśli metoda obsługująca zwróci boolean `false`, zatrzyma wysyłanie formularza.

### `ajaxBeforeSend`
         
`ajaxBeforeSend` jest wyzwalane przed wysłaniem żądania AJAX w przypadku walidacji AJAX-owej.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event, jqXHR, settings)
```

gdzie

- `event`: obiekt Event.
- `jqXHR`: obiekt jqXHR.
- `settings`: konfiguracja żądania AJAX.

### `ajaxComplete`

`ajaxComplete` jest wyzwalane po ukończeniu żądania AJAX w przypadku walidacji AJAX-owej.

Sygnatura metody obsługującej to zdarzenie powinna wyglądać następująco:

```javascript
function (event, jqXHR, textStatus)
```

gdzie

- `event`: obiekt Event.
- `jqXHR`: obiekt jqXHR.
- `textStatus`: status żądania ("success", "notmodified", "error", "timeout", "abort" lub "parsererror").

## Wysyłanie formularza za pomocą AJAX

Walidacja może być przeprowadzona po stronie klienta lub za pomocą AJAX-a, ale wysyłanie formularza jest domyślnie przeprowadzane 
za pomocą zwyczajnego żądania. Jeśli chcesz przesłać formularz za pomocą AJAX, możesz to zrobić obsługując zdarzenie `beforeSubmit` 
formularza w następujący sposób:

```javascript
var $form = $('#formId');
$form.on('beforeSubmit', function() {
    var data = $form.serialize();
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: data,
        success: function (data) {
            // Implementacja pomyślnego statusu
        },
        error: function(jqXHR, errMsg) {
            alert(errMsg);
        }
     });
     return false; // powstrzymuje przed domyślnym sposobem wysłania
});
```

Aby dowiedzieć się więcej o funkcji jQuery `ajax()`, zapoznaj się z [dokumentacją jQuery](https://api.jquery.com/jQuery.ajax/).


## Dynamiczne dodawanie pól

We współczesnych aplikacjach webowych często koniecznie jest modyfikowanie formularza już po tym, jak został zaprezentowany użytkownikowi.
Dla przykładu może to być dodawanie nowego pola po kliknięciu w ikonę "z plusem".
Aby uruchomić walidację takich pól, należy je zarejestrować za pomocą JavaScriptowego pluginu ActiveForm.

Po dodaniu pola do formularza, należy dołączyć je również do listy walidacji:

```javascript
$('#contact-form').yiiActiveForm('add', {
    id: 'address',
    name: 'address',
    container: '.field-address',
    input: '#address',
    error: '.help-block',
    validate:  function (attribute, value, messages, deferred, $form) {
        yii.validation.required(value, messages, {message: "Informacja dotycząca walidacji tutaj"});
    }
});
```

Aby usunąć pole z listy walidacji (aby nie było już sprawdzane), możesz wykonać następujący kod:

```javascript
$('#contact-form').yiiActiveForm('remove', 'address');
```
