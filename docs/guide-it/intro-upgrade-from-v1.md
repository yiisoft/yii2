Aggiornare dalla versione 1.1
=============================

Ci sono molte differenze tra la versione 1.1 e la 2.0 di Yii, dato che il framework è stato completamente riscritto.
Di conseguenza l'aggiornamento dalla versione 1.1 non è così semplice come passare da una versione minore all'altra. In questa guida
troverai le differenze principali tra le due versioni.

Se non hai mai usato Yii 1.1 puoi ignorare questa sezione e passare direttamente a "[Come inizare](start-installation.md)".

Considera che Yii 2.0 introduce più funzionalità di quelle descritte in questo riepilogo. Ti consigliamo di leggere tutta la 
guida definitiva per apprenderle tutte. C'è la possibilità che alcune funzionalità che prima dovevi sviluppare da solo sono state
implementate nel codice principale.


Installazione
-------------

Yii 2.0 usa [Composer](https://getcomposer.org/), lo standard di fatto per la gestione dei pacchetti PHP. L'installazione del
framework di base, così come delle estensioni, sono gestite da Composer. Per favore leggi la guida [Installare Yii](start-installation.md)
per comprendere come installare Yii 2.0. Se vuoi creare una nuova estensione, o trasformarne una sviluppata per 1.1 a 2.0, fai riferimento
alla sezione [Creazione estensioni](structure-extensions.md#creating-extensions).


Richieste PHP
-------------

Yii 2.0 richiede PHP 5.4 o superiore, il che è un passaggio notevole rispetto alla richiesta di PHP 5.2 di Yii 1.1.
Di conseguenza ci sono diverse differenze a livello di linguaggio a cui devi fare attenzione.
Di seguito un riepilogo delle principali differenze relative a PHP:

- [Namespace](https://www.php.net/manual/en/language.namespaces.php).
- [Funzioni anonime](https://www.php.net/manual/en/functions.anonymous.php).
- La sintassi breve per gli array `[...elementi...]` è utilizzabile invece di `array(...elementi...)`.
- Le tag brevi per le echo `<?=` sono utilizzabili nei file delle viste. Il loro utilizzo è sicuro da PHP 5.4.
- [Interfacce e classi SPL](https://www.php.net/manual/en/book.spl.php).
- [Late Static Bindings](https://www.php.net/manual/en/language.oop5.late-static-bindings.php).
- [Data e ora](https://www.php.net/manual/en/book.datetime.php).
- [Trait](https://www.php.net/manual/en/language.oop5.traits.php).
- [intl](https://www.php.net/manual/en/book.intl.php). Yii 2.0 utilizza l'estensione PHP `intl` per le funzionalità di 
  internazionalizzazione.


Namespace
---------

Il cambiamento più evidente in Yii 2.0 è l'uso dei namespace. Praticamente tutte le classi del codice 
principale sono sotto namespace, ad esempio `yii\web\Request`. Il prefisso "C" non è più utilizzato nei nomi delle classi.
Lo schema dei nomi segue la struttura delle directory. Per esempio `yii\web\Request` indica che il file corrispondente per quella
classe si trova in `web/Request.php` nella directory principale del framework Yii.

(Puoi utilizzare qualunque classe del core di Yii senza dover includere il file relativo, grazie al loader delle classi di Yii.)


Componenti ed oggetti
---------------------

Yii 2 divide la classe `CComponent` della versione 1.1 in due classi: [[yii\base\BaseObject]] and [[yii\base\Component]].
La classe [[yii\base\BaseObject|BaseObject]] è una classe leggera da usare come base, che consente la definizione di 
[proprietà dell'oggetto](concept-properties.md) tramite *geters* e *setters*. La classe [[yii\base\Component|Component]] estende
[[yii\base\BaseObject|BaseObject]] e supporta [eventi](concept-events.md) e [behavior](concept-behaviors.md).

Se la tua classe non ha necessità di usare eventi o behavior conviene usare [[yii\base\BaseObject|BaseObject]] come classe base.
Di solito viene impiegata per classi che rappresentano strutture di dati semplici.


Configurazione oggetti
----------------------

La classe [[yii\base\BaseObject|BaseObject]] introduce un metodo uniforme per la configurazione degli oggetti. 
Ogni classe figlia di [[yii\base\BaseObject|BaseObject]] dovrebbe dichiarare il suo costruttore (se necessario) in questo modo, così da essere 
configurato correttamente:

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... inizializzazione prima della configurazione

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... inizializzazione dopo la configurazione
    }
}
```

Nell'esempio sopra, l'ultimo parametro del costruttore riceve l'array di configurazione che contiene coppie di nome-valore per
inizializzare le proprietà alla fine del costruttore.
Puoi sovrascrivere il metodo [[yii\base\BaseObject::init()|init()]] per eseguire operazioni dopo che la configurazione è stata applicata.

Seguendo questa convenzione potrai creare e configurare nuovi oggetti usando un array di configurazione:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'proprieta1' => 'abc',
    'proprieta2' => 'cde',
], [$param1, $param2]);
```

Maggiori dettagli sulla configurazione si trovano nella sezione [Configurazione oggetti](concept-configurations.md).


Eventi
------

In Yii 1 gli eventi venivano creati definendo un metodo `on`-qualcosa (ad es. `onBeforeSave`). In Yii 2 ora puoi usare un qualunque
nome per l'evento. Puoi scatenare un evento chiamando il metodo [[yii\base\Component::trigger()|trigger()]]:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

Per collegare un metodo ad un evento usa il metodo [[yii\base\Component::on()|on()]]:

```php
$component->on($eventName, $handler);
// Per scollegare il metodo dall'evento, usa:
// $component->off($eventName, $handler);
```

Ci sono molti miglioramenti sulle funzionalità degli eventi. Per maggiori dettagli fai riferimento alla sezione
[Eventi](concept-events.md).


Alias percorsi
--------------

Yii 2.0 espande l'utilizzo degli alias di percorso (Path alias, in inglese) a file e directory sia locali che remoti (URL). Yii 2.0
richiede ora che un percorso alias inizi con il carattere `@`, per differenziarli da normali percorsi o URL.
Per esempio, l'alias `@yii` si riferisce alla directory di installazione di Yii. Gli alias di percorso sono supportati nella maggior
parte del codice base di Yii. Per esempio, [[yii\caching\FileCache::cachePath]] può ricevere sia un alias che un percorso normale ad 
una directory.

Un alias di percorso è strettamente legato al namespace della classe. Si saccomanda di definire un alias per ogni namespace root, 
consentendo così di usare le funzioni di autoload di Yii senza configurazioni aggiuntive. Per esempio, visto che `@yii` si riferisce
alla directory di instllazione di Yii, una classe come `yii\web\Request` può essere caricata automaticamente. Se usi una libreria di terze
parti, come ad esempio il framework Zend, puoi definire un alias `@Zend` che si riferisce alla sua directory di installazione. Fatto
questo, Yii sarà in grado di caricare automaticamente qualunque classe della libreria Zend.

Maggiori informazioni sugli alias di percorso nella sezione [Aliase](concept-aliases.md).


Viste
-----

Il cambiamento più evidente riguardante le viste è che in Yii 2 la variabile speciale `$this` in una vista non si riferisce più
al controller o al widget corrente. Invece `$this` si riferisce ora all'oggetto *view*, un nuovo concetto introdotto nella versione 2.0.
L'oggetto *view* è di tipo [[yii\web\View]], che rappresenta la parte della vista nel modello MVC. Per accedere al controller o al 
widget dalla vista, puoi usare `$this->context`.

Per effettuare il render di una vista parzioale all'interno di un'altra vista devi usare `$this->render()`, non `$this->renderPartial()`. 
La chiamata a `render` deve essere ora esplicitamente mandata in output (tramite `echo`), dato che ora il metodo `render()` restituisce
il risultato dell'elaborazione della vista piuttosto che visualizzarlo. Per esempio:

```php
echo $this->render('_item', ['item' => $item]);
```

Oltre ad usare PHP come linguaggio principale di template, Yii 2.0 supporta ufficialmente anche altri due motori di template:
Smarty e Twig. Il motore Prado non è più supportato.
Per usare questi engine devi configurare il componente `view` impostando la proprietà [[yii\base\View::$renderers|View::$renderers]]. 
Fai riferimento alla sezione [Template Engine](tutorial-template-engines.md) per maggiori dettagli.


Modelli
-------

Yii 2.0 usa [[yii\base\Model]] come modello base, simile a `CModel` di 1.1.
La classe `CFormModel` è stata rimossa. In Yii 2 invece devi estendere [[yii\base\Model]] per creare un modello da impiegare in un form.

Yii 2.0 introduce il nuovo metodo [[yii\base\Model::scenarios()|scenarios()]] per dichiarare gli scenari supportati, e per indicare
in quale scenario devono essere validati gli attributi, se devono essere considerati *safe* o no, e così via. PEr esempio:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

Nell'esempio sopra sono stati definiti due scenari: `backend` e `frontend`. Per lo scenario `backend` sono considerati sicuri (`safe`)
entrambi gli attributi `email` e `role`, e possono essere assegnati massivamente. Per lo scenario `frontend` l'`email` può essere
assegnata in sicurezza mentre il `role` no. Entrambi i campi dovrebbero essere validati usando regole opportune.

Viene ancora usato il metodo [[yii\base\Model::rules()|rules()]] per definire le regole di validazione. Nota che in conseguenza 
dell'introduzione del metodo [[yii\base\Model::scenarios()|scenarios()]] non esiste più la validazione `unsafe`.

Nella maggior parte dei casi non avrai la necessità di sovrascrivere [[yii\base\Model::scenarios()|scenarios()]] se il metodo
[[yii\base\Model::rules()|rules()]] specifica già tutti gli scenari esistenti, e se non hai necessità di dichiarare attributi `unsafe`.

Per apprendere più dettagli in merito ai modelli, fare riferimento alla sezione [Modelli](structure-models.md).


Controller
----------

Yii 2.0 use [[yii\web\Controller]] come classe base per i controller, che è simile a `CController` di Yii 1.1.
[[yii\base\Action]] è la classe base per le classi di azioni.

L'impatto più ovvio di questi cambiamenti nel tuo codice è che l'azione di un controller deve tornare il contenuto da visualizzare, invece
di emetterlo direttamente:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Fai riferimento alla sezione [Controller](structure-controllers.md) per maggiori dettagli in merito.


Widget
------

Yii 2.0 use [[yii\base\Widget]] come classe base per i widget, simile a `CWidget` di Yii 1.1.

Per ottenere un supporto migliore al framework usando le IDE, Yii 2.0 introduce una nuova sintassi per l'utilizzo dei widget. Sono stati
introdotti i metodi statici [[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]], e [[yii\base\Widget::widget()|widget()]]
da usare così:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Nota che devi emettere a video ("echo") il risultato per visualizzarlo
echo Menu::widget(['items' => $items]);

// Passaggio di un array per inizializzare le proprietà dell'oggetto
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... campi di input del form ...
ActiveForm::end();
```

Fai riferimento alla sezione [Widget](structure-widgets.md) per maggiori dettagli.


Temi
----

I temi sono completamente diversi nella versione 2.0. Ora sono basati su un meccanismo di mappatura dei percorsi, in modo da 
creare una corrispondenza tra il percorso di un file vista sorgente e il percorso di un file di vista del tema. Per esempio se la mappa
è `['/web/views' => '/web/themes/basic']`, la versione personalizzata del tema del file 
`/web/views/site/index.php` sarà `/web/themes/basic/site/index.php`. Per questo motivo ora i temi possono essere applicati a qualunque
file di vista, anche per una vista elaborata al di fuori del contesto di un controller o di un widget.

Inoltre non c'è più il componente`CThemeManager`. Esiste invece una proprietà configurabile `theme` del componente `view`.

Fai rfierimento alla sezione [Temi](output-theming.md) per maggiori dettagli.


Applicazioni da console
-----------------------

Le applicazioni da console (linea di comando) sono ora organizzate come controller, come le applicazioni web. I controller devono quindi
estendere [[yii\console\Controller]], simile alla classe `CConsoleCommand` della versione 1.1.

Per eseguire un comando da terminale usare `yii <route>`, dove `<route>` rappresenta la rotta di un controller
(es. `sitemap/index`). I parametri anonimi aggiuntivi vengono passati come parametri al relativo metodo dell'azione nel controller, mentre
i parametri specifici (con nome) vengono processati secondo le specifiche di [[yii\console\Controller::options()]].

Yii 2.0 supporta la generazione automatica dell'help dei comandi prelevando le informazioni dai blocchi di commento.

Fai riferimento alla sezione [Console Commands](tutorial-console.md) per ulteriori dettagli.


I18N
----

Yii 2.0 ha rimosso la formattazione interna di date e numeri in favore del [modulo PECL di PHP](https://pecl.php.net/package/intl).

La traduzione dei messaggi viene effettuata dal componente `i18n`.
Questo componente gestisce una serie di sorgenti di messaggi, il che ti consente di usare diverse sorgenti di messaggio basate sulle
categorie.

Fai riferimento alla sezione [Internazionalizzazione](tutorial-i18n.md) per maggiori dettagli.


Filtri azioni
-------------

I filtri sulle azioni vengono ora implementati tramite i *behavior*. Per definire un nuovo filtro personalizzato devi estendere da 
[[yii\base\ActionFilter]]. Per usare un filtro collega la relativa classe ai *behavior* del controller. Per esempio, per usare 
il filtro [[yii\filters\AccessControl]] dovrai avere questo codice nel controller:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Fai riferimento alla sezione [Filtri](structure-filters.md) per maggiori dettagli.


Asset
-----

Yii 2.0 introduce un nuovo concetto chiamato *asset bundle* che rimpiazza il concetto dei pacchetti di script di Yii 1.1.

Un *asset bundle* è una collezione di file di asset (ad es. file Javascript, CSS, immagini...) all'interno di una directory.
Ogni *asset bundle* è rappresentato da una classe che estende [[yii\web\AssetBundle]].
Registrando un *asset bundle* tramite il metodo [[yii\web\AssetBundle::register()]], renderai disponibile gli asset di quel pachetto
disponibili via web. Diversamente da Yii 1.1 la pagina che registra il pacchetto conterrà automaticamente le referenze ai file Javascript
e CSS specificati al suo interno.

Fai riferimento alla sezione [Gestione asset](structure-assets.md) per maggiori informazioni.


Helper
------

Yii 2.0 introduce molte classi statiche di uso comune, tra cui:

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]

Fai riferimento alla sezione [Panoramica sugli Helper](helper-overview.md) per maggiori dettagli.

Form
----

Yii 2.0 introduce il concetto di *campo* per la costruzione dei form usando [[yii\widgets\ActiveForm]]. Un campo è un
contentitore costituito da un'etichetta, un input, un messaggio di errore e/o un testo di suggerimento.
Un campo è rappresentato come un oggetto [[yii\widgets\ActiveField|ActiveField]].
Usando i campi potrai creare un form in un modo molto più pulito che in precedenza:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Fai riferimento alla sezione [Creazione form](input-forms.md) per maggiori dettagli.


Query Builder
-------------

In 1.1 la costruzione di query era dispersa in diverse classi, inclusa `CDbcommand`, 
`CDbCriteria`, e `CDbCommandBuilder`. Yii 2.0 gestisce le query mediante un oggetto [[yii\db\Query|Query]] 
che può essere trasformato in un comando SQL con l'aiuto di [[yii\db\QueryBuilder|QueryBuilder]] dietro le quinte.
Per esempio:

```php
$query = new \yii\db\Query();
$query->select('id, nome')
      ->from('user')
      ->limit(10);

$comando = $query->createCommand();
$sql = $command->sql;
$righe = $command->queryAll();
```

Ma la cosa migliore di tutte è che gli stessi metodi di costruzione delle query possono essere usati con oggetti di
tipo [Active Record](db-active-record.md).

Fai riferimento alla sezione [Query Builder](db-query-builder.md) per maggiori dettagli.


Active Record
-------------

Yii 2.0 introduce molti cambiamenti agli [Active Record](db-active-record.md). I due più evidenti riguardano la costruzione delle
query e la gestione delle relazioni.

La classe `CDbCriteria` della versione 1.1 è stata rimpiazzata da [[yii\db\ActiveQuery]]. Questa classe estende [[yii\db\Query]], e
ne eredita quindi tutti i metodi di costruzione delle query. Per iniziare la costruzione di una query devi chiamare 
[[yii\db\ActiveRecord::find()]]:

```php
// Per ottenere tutti i clienti *attivi* e ordinarli per ID:
$clienti = Clienti::find()
    ->where(['stato' => $attivo])
    ->orderBy('id')
    ->all();
```

Per dichiarare una relazione devi semplicemente definire una *getter* che ritorna un oggetto [[yii\db\ActiveQuery|ActiveQuery]].
Il nome della proprietà definito dalla *getter* rappresenta il nome della relazione. Ad esempio il codice qui di seguito dichiara
una relazione `ordini` (in 1.1 avresti dovuto farlo nel metodo `relations()`):

```php
class Cliente extends \yii\db\ActiveRecord
{
    public function getOrdini()
    {
        return $this->hasMany('Ordine', ['cliente_id' => 'id']);
    }
}
```

Ora puoi usare `$cliente->ordini` per accedere agli ordini del cliente nella tabella collegata. Puoi usare anche questo codice
per effettuare una query relazionale al volo con una condizione di ricerca personalizzata:

```php
$ordini = $cliente->getOrdini()->andWhere('stato=1')->all();
```

Quando si usa il caricamento immediato di una relazione, Yii 2.0 si comporta diversamente rispetto alla versione precedente. In
particolare, Yii 1.1 creava una query con JOIN con sia il record primario che la relazione. In Yii 2.0 vengono invece eseguite due
query SQL distinte, senza JOIN: la prima carica le righe della tabella primaria e la seconda recupera le righe della tabella in relazione
basandosi sulle chiavi ottenute dalla prima.

Invece di tornare oggetti [[yii\db\ActiveRecord|ActiveRecord]], puoi sfruttare il metodo [[yii\db\ActiveQuery::asArray()|asArray()]]
in caso di query che tornano un cospicuo numero di risultati. In questo modo i risultati saranno in formato di array, il che consente
di risparmiare l'utilizzo di CPU e memoria in caso di grandi volumi di record. Per esempio:

```php
$clienti = Cliente::find()->asArray()->all();
```

Un'altra differenza è che non puoi più definire valori predefiniti per gli attributi tramite proprietà pubbliche.
Se ti servono li puoi impostare nel metodo `init` della tua classe ActiveRecord.

```php
public function init()
{
    parent::init();
    $this->stato = self::STATO_NUOVO;
}
```

Nella versione precedente c'erano problemi nell'override del costruttore di un ActiveRecord. Questi problemi sono stati risolti 
in questa versione. Tieni presente che se devi aggiungere parametri al costruttore devi probabilmente sovrascrivere 
[[yii\db\ActiveRecord::instantiate()]].

Ci sono molti altri cambiamenti e miglioramenti sugli Active Record. Fai riferimento alla sezione
[Active Record](db-active-record.md) per maggiori dettagli.  


Behavior di Active Record 
-------------------------

Nella 2.0 è stata rimossa la classe base `CActiveRecordBehavior`. Per creare un nuovo behavior devi estendere direttamente
`yii\base\Behavior`. Se la classe deve gestire degli eventi dell'*owner*, devi sovrascrivere il metodo `events()` come qui di seguito:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MioBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```


Utenti e IdentityInterface
--------------------------

La classe `CWebUser` di Yii 1.1 è stata rimpiazzata da [[yii\web\User]], e non esiste più la 
`CUserIdentity`. In Yii 2.0 devi implementare [[yii\web\IdentityInterface]] che risulterà molto più immediata da usare.
Il template dell'applicazione avanzata fornisce un esempio di implementazione di quella libreria.

Fai riferimento alle sezioni [Autenticazione](security-authentication.md), [Autorizzazione](security-authorization.md) e 
[Template applicazione avanzata](tutorial-advanced-app.md) per maggiori informazioni.


Gestione degli URL
------------------

La gestione degli URL è molto simile a quella implementata in Yii 1.1. Uno dei miglioramenti più rilevanti è che ora sono supportati
i parametri. Per esempio, una regola dichiarata come qui di seguito prenderà sia `post/popolari` che `post/1/popolari`. Nella 1.1
ci sarebbero volute due regole per lo stesso risultato.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Fai riferimento alla sezione [Url manager](runtime-url-handling.md) per ulteriori dettagli.

Usare Yii 1.1 e 2.x insieme
---------------------------

Se hai del vecchio codice scritto per Yii 1.1 che vuoi usare insieme a Yii 2.0, fai riferimento alla sezione
[Usare Yii 1.1 e 2.0 insieme](tutorial-yii-integration.md).

