Wysyłanie poczty
=======

> Note: Ta sekcja jest w trakcie tworzenia.

Yii wspiera tworzenie oraz wysyłanie wiadomości email, jednakże silnik dostarcza jedynie funkcjonalność składania treści oraz prosty interfejs.
Mechanizm wysyłania wiadomości powinien być dostarczony przez rozszerzenie, 
ponieważ projekty mogą wymagać różnych implementacji, przez co mechanizm jest zależny od zewnętrznych usług i bibliotek.

Dla większości przypadków możesz używać oficjalnego rozszerzenia [yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer).


Konfiguracja
-------------

Konfiguracja tego komponentu zależy od rozszerzenia jakie wybrałeś.
Generalnie, konfiguracja Twojego komponentu w aplikacji powinna wyglądać tak:

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
];
```


Podstawowe użycie
-----------
Kiedy komponent 'mailer' zostanie skonfigurowany, możesz użyć następującego kodu do wysłania wiadomości email:

```php
Yii::$app->mailer->compose()
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Temat wiadomości')
    ->setTextBody('Zwykła treść wiadomości')
    ->setHtmlBody('<b>Treść HTML wiadomości</b>')
    ->send();
```

W powyższym przykładzie metoda `compose()` tworzy instancję wiadomości email, która następnie jest wypełniana danymi i wysłana.
Możesz utworzyć tutaj więcej złożonej logiki jeśli jest to potrzebne:

```php
$message = Yii::$app->mailer->compose();
if (Yii::$app->user->isGuest) {
    $message->setFrom('from@domain.com')
} else {
    $message->setFrom(Yii::$app->user->identity->email)
}
$message->setTo(Yii::$app->params['adminEmail'])
    ->setSubject('Temat wiadomości')
    ->setTextBody('Zwykła treść wiadomości')
    ->send();
```

> Note: każde rozszerzenie mailingowe posiada dwie główne klasy: 'Mailer' oraz 'Message'. Klasa 'Mailer' zawsze posiada nazwę klasy 'Message'.
> Nie próbuj instancjować obiektu 'Message' bezpośrednio - zawsze używaj do tego metody `compose()`.

Możesz również wysłać wiele wiadomości na raz:

```php
$messages = [];
foreach ($users as $user) {
    $messages[] = Yii::$app->mailer->compose()
        // ...
        ->setTo($user->email);
}
Yii::$app->mailer->sendMultiple($messages);
```

Niektóre rozszerzenia mailingowe mogą czerpać korzyści z tego sposobu, np. używając pojedyńczych wiadomości sieciowych.


Tworzenie treści maila
----------------------

Yii pozwala na tworzenie treści aktualnej wiadomości email przez specjalne pliki widoków.
Domyślnie, pliki te zlokalizowane są w ścieżce '@app/mail'.

Przykładowy widok pliku treści wiadomości email:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this \yii\web\View instancja komponentu View */
/* @var $message \yii\mail\BaseMessage instancja nowo utworzonej wiadomości email */

?>
<h2>Ta wiadomość pozwala Ci odwiedzić stronę główną naszej witryny przez jedno kliknięcie</h2>
<?= Html::a('Idź do strony głównej', Url::home('http')) ?>
```

W celu wykorzystania tego pliku do utworzenia treści wiadomości, przekaż po prostu nazwę tego widoku do metody `compose()`:

```php
Yii::$app->mailer->compose('home-link') // wynik renderingu widoku staje się treścią wiadomości
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Temat wiadomości')
    ->send();
```

Możesz przekazać dodatkowe parametry do metody `compose()`, które będą dostępne w plikach widoków:

```php
Yii::$app->mailer->compose('greetings', [
    'user' => Yii::$app->user->identity,
    'advertisement' => $adContent,
]);
```

Możesz określić różne pliki do zwykłej treści oraz treści HTML:

```php
Yii::$app->mailer->compose([
    'html' => 'contact-html',
    'text' => 'contact-text',
]);
```

Jeśli określisz nazwę widoku jako ciąg skalarny, to wynik jego renderowania zostanie użyty jako ciało HTML wiadomości,
podczas gdy przy użyciu zwykłego teksu zostanie ono utworzone przez usunięcie wszystkich encji HTML z tego widoku. 

Wynik renderowania widoku może zostać opakowany w szablon. Szablon możesz ustawić przez właściwość [[yii\mail\BaseMailer::htmlLayout|htmlLayout]] lub 
[[yii\mail\BaseMailer::textLayout|textLayout]].
Zadziała to w identyczny sposób co w standardowej aplikacji web.
Szalony mogą zostać użyte do ustawienia styli CSS, lub innej wspólnej treści:

```php
<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <style type="text/css">
        .heading {...}
        .list {...}
        .footer {...}
    </style>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <div class="footer">Z pozdrowieniami, zespół <?= Yii::$app->name ?></div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```


Załączniki do wiadomości
---------------

Możesz dodać załączniki do wiadomości przez użycie metod `attach()` oraz `attachContent()`:

```php
$message = Yii::$app->mailer->compose();

// Załącz plik z lokalnego systemu plików:
$message->attach('/path/to/source/file.pdf');

// Utwórz załącznik w locie:
$message->attachContent('Attachment content', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
```


Osadzanie obrazków
----------------

W treści wiadomości możesz osadzać również obrazki przy użyciu metody `embed()`. Metoda ta zwraca ID załącznika,
który powinien zostać później użyty w tagu 'img'.
Użycie tej metody jest proste podczas tworzenia treści wiadomości z pliku widoku:

```php
Yii::$app->mailer->compose('embed-email', ['imageFileName' => '/path/to/image.jpg'])
    // ...
    ->send();
```

Następnie, w pliku widoku możesz użyć następującego kodu:

```php
<img src="<?= $message->embed($imageFileName); ?>">
```


Testowanie i debugowanie
---------------------

Deweloperzy często muszą sprawdzić, czy emaile zostały wysłane przez aplikację lub jaka była ich treść.
Możesz tego dokonać w łatwy sposób, używając dostarczonej przez Yii funkcjonalności, którą aktywujesz przez parametr [[yii\mail\BaseMailer::useFileTransport|useFileTransport]].
Jeśli zostanie aktywowana, każda wiadomość email będzie zapisywana do lokalnych plików zamiast zostać wysłana. Wszystkie pliki będą zapisane w ścieżce podanej w 
[[yii\mail\BaseMailer::fileTransportPath|fileTransportPath]], która domyślnie ustawiona jest na '@runtime/mail'.

> Note: możesz albo zapisywać wiadomości do plików, albo wysyłać je do odbiorców, nie można wykonać tych dwóch czynności na raz.

Plik z wiadomością email może zostać otwarty przez standardowy edytor tekstu, dzięki czemu będziesz mógł przeglądać nagłówki oraz treść wiadomości.

> Note: plik wiadomości jest tworzony przy użyciu metody [[yii\mail\MessageInterface::toString()|toString()]], więc jest zależny od aktualnie używanego rozszerzenia mailingowego w 
> Twojej aplikacji.

Tworzenie własnego rozwiązania mailingowego
-------------------------------

Aby utworzyć swoje własne rozwiązanie mailingowe, musisz utworzyć dwie klasy: 'Mailer' oraz 'Message'.
Możesz rozszerzyć klasy [[yii\mail\BaseMailer|BaseMailer]] i [[yii\mail\BaseMessage|BaseMessage]] jako bazowe klasy do tego rozwiązania.
Zawierają one podstawową logikę mechanizmu mailingu, który został opisany w tej sekcji. 
Oczywiście ich użycie nie jest obowiązkowe, wystarczy zaimplementowanie interfejsów [[yii\mail\MailerInterface|MailerInterface]] oraz [[yii\mail\MessageInterface|MessageInterface]].
Następnie musisz zaimplementować wszystkie abstrakcyjne metody do swoich klas.