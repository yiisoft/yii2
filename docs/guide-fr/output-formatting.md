Formatage des données
=====================

Pour afficher des données dans un format plus facile à lire par les utilisateurs, vous pouvez les formater en utilisant le [composant d'application](structure-application-components.md) `formatter`. Par défaut, le formateur est mis en œuvre par [[yii\i18n\Formatter]] qui fournit un jeu de méthodes pour formater des données telles que des dates, des temps, des nombres, des monnaies et autres données couramment utilisées. Vous pouvez utiliser le formateur de la manière indiquée ci-dessous :

```php
$formatter = \Yii::$app->formatter;

// affiche : January 1, 2014
echo $formatter->asDate('2014-01-01', 'long');
 
// affiche : 12.50%
echo $formatter->asPercent(0.125, 2);
 
// affiche : <a href="mailto:cebe@example.com">cebe@example.com</a>
echo $formatter->asEmail('cebe@example.com'); 

// affiche : Yes
echo $formatter->asBoolean(true); 
// il prend aussi en charge l'affichage de valeurs nulles :

// affiche : (Not set)
echo $formatter->asDate(null); 
```

Comme vous pouvez le voir, ces trois méthodes sont nommées selon le format suivant `asXyz()`, où `Xyz` représente un format pris en charge. En alternative, vous pouvez formater les données en utilisant la méthode générique [[yii\i18n\Formatter::format()|format()]], qui vous permet de contrôler le format désiré par programmation et qui est communément utilisé par les composants graphiques tels que [[yii\grid\GridView]] et [[yii\widgets\DetailView]]. Par exemple :

```php
// affiche : January 1, 2014
echo Yii::$app->formatter->format('2014-01-01', 'date'); 

// vous pouvez aussi utiliser un tableau pour spécifier les paramètres de votre méthode de formatage :
// `2` est la valeur du paramètre `$decimals` (nombre de décimales) pour la méthode asPercent().
// affiche : 12.50%
echo Yii::$app->formatter->format(0.125, ['percent', 2]); 
```

> Note: le composant de formatage est conçu pour formater des valeurs à présenter à l'utilisateur. Si vous voulez convertir des entrées utilisateur en un format lisible par la machine, ou simplement formater une date dans un format lisible par la machine, le formateur n'est pas l'outil adapté à cela. Pour convertir une entrée utilisateur pour une date et un temps, vous pouvez utiliser [[yii\validators\DateValidator]] et [[yii\validators\NumberValidator]] respectivement. Pour une simple conversion entre les formats lisibles par la machine de date et de temps, la fonction PHP [date()](https://www.php.net/manual/fr/function.date.php) suffit.

## Configuration du formateur <span id="configuring-formatter"></span>

Vous pouvez configurer les règles de formatage en configurant le composant `formatter` dans la [configuration de l'application](concept-configurations.md#application-configurations). Par exemple :

```php
return [
    'components' => [
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
       ],
    ],
];
```

Reportez-vous à la classe [[yii\i18n\Formatter]] pour connaître les propriétés qui peuvent être configurées.


## Formatage de valeurs de dates et de temps <span id="date-and-time"></span>

Le formateur prend en charge les formats de sortie suivants en relation avec les dates et les temps : 

- [[yii\i18n\Formatter::asDate()|date]]: la valeur est formatée sous la forme d'une date, p. ex. `January 01, 2014`.
- [[yii\i18n\Formatter::asTime()|time]]: la valeur est formatée sous la forme d'un temps, p. ex. `14:23`.
- [[yii\i18n\Formatter::asDatetime()|datetime]]: la valeur est formatée sous la forme d'une date et d'un temps, p. ex. `January 01, 2014 14:23`.
- [[yii\i18n\Formatter::asTimestamp()|timestamp]]: la valeur est formatée sous la forme d'un [horodatage unix ](https://fr.wikipedia.org/wiki/Heure_Unix), p. ex. `1412609982`.
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]]: la valeur est formatée sous la forme d'un intervalle de temps entre un temps et le temps actuel dans une forme lisible par l'homme, p.ex. `1 hour ago`.
- [[yii\i18n\Formatter::asDuration()|duration]]: la valeur est formatée comme une durée dans un format lisible par l'homme, p. ex. `1 day, 2 minutes`.

Les formats par défaut pour les dates et les temps utilisés pour les méthodes [[yii\i18n\Formatter::asDate()|date]], [[yii\i18n\Formatter::asTime()|time]],
et [[yii\i18n\Formatter::asDatetime()|datetime]] peuvent être configurés globalement en configurant [[yii\i18n\Formatter::dateFormat|dateFormat]], [[yii\i18n\Formatter::timeFormat|timeFormat]], et [[yii\i18n\Formatter::datetimeFormat|datetimeFormat]].

Vous pouvez spécifier les formats de date et de temps en utilisant la [syntaxe ICU](https://unicode-org.github.io/icu/userguide/format_parse/datetime/). Vous pouvez aussi utiliser la [syntaxe date() de PHP](https://www.php.net/manual/fr/function.date.php) avec le préfixe `php:` pour la différentier de la syntaxe ICU. Par exemple :

```php
// format ICU
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06

// format date() de PHP
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

Lorsque vous travaillez avec des applications qui requièrent une prise en charge de plusieurs langues, vous devez souvent spécifier différents formats de dates et de temps pour différentes locales. Pour simplifier cette tâche, vous pouvez utiliser les raccourcis de formats (p. ex. `long`, `short`), à la place. Le formateur transforme un raccourci de formats en un format approprié en prenant en compte la [[yii\i18n\Formatter::locale|locale]] courante. Les raccourcis de formats suivants sont pris en charge (les exemples supposent que `en_GB` est la locale courante) :

- `short`: affiche `06/10/2014` pour une date et `15:58` pour un temps;
- `medium`: affiche `6 Oct 2014` et `15:58:42`;
- `long`: affiche `6 October 2014` et `15:58:42 GMT`;
- `full`: affiche `Monday, 6 October 2014` et `15:58:42 GMT`.

Depuis la version 2.0.7, il est aussi possible de formater les dates dans différents systèmes calendaires. Reportez-vous à la documentation de l'API pour la propriété [[yii\i18n\Formatter::$calendar|$calendar]] des formateurs pour savoir comment définir un autre système calendaire. 

### Fuseaux horaires <span id="time-zones"></span>

Lors du formatage des dates et des temps, Yii les convertit dans le [[yii\i18n\Formatter::timeZone|fuseau horaire]] cible. La valeur à formater est supposée être donnée en UTC, sauf si un fuseau horaire est explicitement défini ou si vous avez configuré [[yii\i18n\Formatter::defaultTimeZone]].

Dans les exemples qui suivent, nous supposons que la cible [[yii\i18n\Formatter::timeZone|fuseau horaire]] est définie à `Europe/Berlin`. 

```php
// formatage d'un horodatage UNIX comme un temps
echo Yii::$app->formatter->asTime(1412599260); // 14:41:00

// formatage d'une chaîne de caractère date-temps (en UTC) comme un temps 
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 14:41:00

// formatage d'une chaîne de caractères date-temps (en CEST) comme un temps
echo Yii::$app->formatter->asTime('2014-10-06 14:41:00 CEST'); // 14:41:00
```

> Note: comme les fuseaux horaires sont assujettis à des règles fixées par les gouvernements du monde entier, et que ces règles peuvent varier fréquemment, il est vraisemblable que vous n'ayez pas la dernière information dans la base de données des fuseaux horaires installée sur votre système. Vous pouvez vous reporter au [manuel d'ICU](https://unicode-org.github.io/icu/userguide/datetime/timezone/#updating-the-time-zone-data) pour des informations sur la manière de mettre cette base de données à jour. Reportez-vous aussi au tutoriel [Configurer votre environnement PHP pour l'internationalisation](tutorial-i18n.md#setup-environment).


## Formatage des nombres <span id="numbers"></span>

Pour les nombres, le formateur prend en charge les formats de sortie suivants :

- [[yii\i18n\Formatter::asInteger()|integer]]: la valeur est formatée comme un entier, p. ex. `42`.
- [[yii\i18n\Formatter::asDecimal()|decimal]]: la valeur est formatée comme un nombre décimal en portant attention aux décimales et aux séparateurs de milliers, p. ex. `2,542.123` ou `2.542,123`.
- [[yii\i18n\Formatter::asPercent()|percent]]: la valeur est formatée comme un pourcentage p. ex. `42%`.
- [[yii\i18n\Formatter::asScientific()|scientific]]: la valeur est formatée comme un nombre dans le format scientifique p. ex. `4.2E4`.
- [[yii\i18n\Formatter::asCurrency()|currency]]: la valeur est formatée comme une valeur monétaire, p. ex. `£420.00`. Notez que pour que cette fonction fonctionne correctement, la locale doit inclure la partie correspondant au pays p. ex. `en_GB` ou `en_US` parce que la partie langue seulement reste ambigüe dans ce cas. 
- [[yii\i18n\Formatter::asSize()|size]]: la valeur, qui est un nombre d'octets est formatée sous une forme lisible par l'homme, p. ex. `410 kibibytes`.
- [[yii\i18n\Formatter::asShortSize()|shortSize]]: est la version courte de [[yii\i18n\Formatter::asSize()|size]], e.g. `410 KiB`.

Le format pour un nombre peut être ajusté en utilisant [[yii\i18n\Formatter::decimalSeparator|decimalSeparator (séparateur de décimales)]] et
[[yii\i18n\Formatter::thousandSeparator|thousandSeparator (séparateur de milliers) ]], qui prennent tous les deux les valeurs par défaut déterminées par la [[yii\i18n\Formatter::locale|locale]] courante.

Pour une configuration plus avancée, [[yii\i18n\Formatter::numberFormatterOptions]] et [[yii\i18n\Formatter::numberFormatterTextOptions]] peuvent être utilisés pour configurer la classe [NumberFormater (formateur de nombres)](https://www.php.net/manual/fr/class.numberformatter.php) utilisée en interne pour implémenter le formateur. Par exemple, pour ajuster la valeur minimum et maximum des chiffres fractionnaires, vous pouvez configurer la propriété [[yii\i18n\Formatter::numberFormatterOptions]] comme ceci :

```php
'numberFormatterOptions' => [
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 2,
]
```


## Autres formats <span id="other"></span>

En plus des formats de date, temps et nombre, Yii prend aussi en charge les autres formats communément utilisés, y compris :

- [[yii\i18n\Formatter::asRaw()|raw]]: la valeur est affichée telle quelle, il s'agit d'un pseudo-formateur qui n'a pas d'effet, à l'exception des valeurs `null` qui sont affichées en utilisant la propriété [[nullDisplay]].
- [[yii\i18n\Formatter::asText()|text]]: la valeur est encodée HTML. C'est le format par défaut utilisé par les [données des colonnes du widget GridView](output-data-widgets.md#data-column).
- [[yii\i18n\Formatter::asNtext()|ntext]]: la valeur est formatée comme un texte simple encodé HTML avec conversion des retours à la ligne en balise break.
- [[yii\i18n\Formatter::asParagraphs()|paragraphs]]: la valeur est formatée comme un paragraphe de texte encodé HTML à l'intérieur d'une balise `<p>`.
- [[yii\i18n\Formatter::asHtml()|html]]: la valeur est purifiée en utilisant [[HtmlPurifier]] pour éviter les attaques XSS. Vous pouvez passer les options additionnelles telles que `['html', ['Attr.AllowedFrameTargets' => ['_blank']]]`.
- [[yii\i18n\Formatter::asEmail()|email]]: la valeur est encodé comme un lien `mailto`.
- [[yii\i18n\Formatter::asImage()|image]]: la valeur est formatée comme une balise image.
- [[yii\i18n\Formatter::asUrl()|url]]: la valeur est formatée comme un hyperlien.
- [[yii\i18n\Formatter::asBoolean()|boolean]]: la valeur est formatée comme une valeur booléenne. Par défaut `true` est rendu par `Yes` et `false` par `No`, traduit dans la langue courante de l'application. Vous pouvez ajuster cela en configurant la propriété [[yii\i18n\Formatter::booleanFormat]].


## Valeurs nulles (null) <span id="null-values"></span>

Les valeurs *null* sont formatées spécialement. Au lieu d'afficher une chaîne de caractères vide, le formateur la convertit en une chaîne de caractères prédéfinie dont la valeur par défaut est `(not set)` traduite dans la langue courante de l'application. Vous pouvez configurer la propriété [[yii\i18n\Formatter::nullDisplay|nullDisplay]] pour personnaliser cette chaîne de caractères.


## Localisation des formats de données <span id="localizing-data-format"></span>

Comme nous l'avons mentionné précédemment, le formateur utilise la [[yii\i18n\Formatter::locale|locale]] courante pour déterminer comment formater une valeur qui soit convenable dans la cible pays/région. Par exemple, la même valeur de date est formatée différemment pour différentes locales :

```php
Yii::$app->formatter->locale = 'en-US';
echo Yii::$app->formatter->asDate('2014-01-01'); // affiche : January 1, 2014

Yii::$app->formatter->locale = 'de-DE';
echo Yii::$app->formatter->asDate('2014-01-01'); // affiche : 1. Januar 2014

Yii::$app->formatter->locale = 'ru-RU';
echo Yii::$app->formatter->asDate('2014-01-01'); // affiche : 1 января 2014 г.
```

Par défaut, la [[yii\i18n\Formatter::locale|locale]] est déterminée par la valeur de [[yii\base\Application::language]]. Vous pouvez la redéfinir en définissant la propriété [[yii\i18n\Formatter::locale]] explicitement.

> Note: le formateur de Yii a besoin de l'[extension intl de PHP](https://www.php.net/manual/fr/book.intl.php) pour prendre en charge la localisation des formats de données. Parce que différentes versions de la bibliothèque ICU compilées par PHP produisent des résultats de formatage différents, il est recommandé que vous utilisiez la même version de la bibliothèque ICU pour tous vos environnements. Pour plus de détails, reportez-vous au tutoriel [Configuration de votre environnement PHP pour l'internationalisation](tutorial-i18n.md#setup-environment).
>
> Si l'extension intl extension n'est pas installée, les données ne sont pas localisées. 
>
> Notez que pour les valeurs de dates qui sont antérieures à l'année 1901, ou postérieures à 2038, la localisation n'est pas faite sur les systèmes 32 bits, même si l'extension intl est installée. Cela est dû au fait que, dans ce cas, ICU utilise des horodatages UNIX 32 bits pour les valeurs de date. 
