Enregistrements des messages
============================

Yii fournit une puissante base structurée d'enregistrement des messages qui est très personnalisable et extensible. En utilisant cette base structurée, vous pouvez facilement enregistrer des types variés de messages, les filtrer et les rassembler dans différentes cibles comme les bases de données et les courriels. 

L'utilisation de la base structurée d'enregistrement des messages de Yii nécessite de suivre les étapes suivantes :
 
* Enregistrer les  [messages](#log-messages) à différents endroits de votre code ;
* Configurer [cibles d'enregistrement](#log-targets) dans la configuration de l'application pour filtrer et exporter les messages enregistrés ; 
* Examiner les messages enregistrés, filtrés et exportés par les différentes cibles (p. ex. [débogueur de Yii](tool-debugger.md)).

Dans cette section, nous décrivons principalement les deux premières étapes. 


## Messages enregistrés <span id="log-messages"></span>

Enregistrer des messages est aussi simple que d'appeler une des méthodes suivantes :

* [[Yii::debug()]]: enregistre un message pour garder une trace de comment un morceau de code fonctionne. Cela est utilisé principalement en développement.
* [[Yii::info()]]: enregistre un message qui contient quelques informations utiles.
* [[Yii::warning()]]: enregistre un message d'avertissement qui indique que quelque chose d'inattendu s'est produit.
* [[Yii::error()]]: enregistre une erreur fatale qui doit être analysée dès que possible. 

Ces méthodes enregistrent les messages à différents niveaux de sévérité et dans différentes catégories. Elles partagent la même signature `function ($message, $category = 'application')`, où `$message` représente le message à enregistrer, tandis que `$category` est la catégorie de ce message. Le code de l'exemple qui suit enregistre un message de trace dans la catégorie `application`:

```php
Yii::debug('start calculating average revenue');
```

> Info: les messages enregistrés peuvent être des chaînes de caractères aussi bien que des données complexes telles que des tableaux ou des objets. Il est de la responsabilité des [cibles d'enregistrement](#log-targets) de traiter correctement ces messages. Par défaut, si un message enregistré n'est pas un chaîne de caractères, il est exporté comme une chaîne de caractères en appelant la méthode [[yii\helpers\VarDumper::export()]].

Pour mieux organiser et filtrer les messages enregistrés, il est recommandé que vous spécifiiez une catégorie appropriée pour chacun des messages. Vous pouvez choisir une schéma de nommage hiérarchisé pour les catégories, ce qui facilitera le filtrage des messages par les [cibles d'enregistrement](#log-targets)  sur la base de ces catégories. Un schéma de nommage simple et efficace est d'utiliser la constante magique `__METHOD__` de PHP dans les noms de catégorie. Par exemple :

```php
Yii::debug('start calculating average revenue', __METHOD__);
```

La constante magique `__METHOD__` est évaluée comme le nom de la méthode (préfixée par le nom pleinement qualifié de la classe), là où la constante apparaît. Par exemple, elle est égale à `'app\controllers\RevenueController::calculate'` si la ligne suivante est utilisée dans cette méthode. 

> Info: les méthodes d'enregistrement décrites plus haut sont en fait des raccourcis pour la méthode  [[yii\log\Logger::log()|log()]] de l'[[yii\log\Logger|objet logger]] qui est un singleton accessible via l'expression `Yii::getLogger()`. Lorsque suffisamment de messages ont été enregistrés, ou quand l'application se termine, l'objet *logger* appelle un [[yii\log\Dispatcher|distributeur de messages]] pour envoyer les messages enregistrés aux [cibles d'enregistrement](#log-targets).


## Cibles d'enregistrement <span id="log-targets"></span>

Une cible d'enregistrement est une instance de la classe [[yii\log\Target]] ou d'une de ses classe filles. Elle filtre les messages enregistrés selon leur degré de sévérité et leur catégorie et les exporte vers un média donné. Par exemple, une [[yii\log\DbTarget|cible base données]] exporte les messages enregistrés et filtrés vers une base de données, tandis qu'une [[yii\log\EmailTarget|cible courriel]] exporte les messages vers l'adresse de courriel spécifiée.

Vous pouvez enregistrer plusieurs cibles d'enregistrement dans votre application en les configurant, via le [composant d'application](structure-application-components.md)`log` dans la configuration de l'application, de la manière suivante :

```php
return [
    // le composant "log" doit être chargé lors de la période d'amorçage
    'bootstrap' => ['log'],
    
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                       'from' => ['log@example.com'],
                       'to' => ['admin@example.com', 'developer@example.com'],
                       'subject' => 'Database errors at example.com',
                    ],
                ],
            ],
        ],
    ],
];
```

> Note: le composant `log` doit être chargé durant le  [processus d'amorçage](runtime-bootstrapping.md) afin qu'il puisse distribuer les messages enregistrés aux cibles rapidement. C'est pourquoi il est listé dans le tableau `bootstrap` comme nous le montrons ci-dessus.

Dans le code précédent, deux cibles d'enregistrement sont enregistrées dan la propriété [[yii\log\Dispatcher::targets]] : 

* la première cible sélectionne les messages d'erreurs et les avertissements et les sauvegarde dans une table de base de données ; 
* la deuxième cible sélectionne les messages d'erreur dont le nom de la catégorie commence par `yii\db\`, et les envoie dans un courriel à la fois à `admin@example.com` et à `developer@example.com`.

Yii est fourni avec les cibles pré-construites suivantes. Reportez-vous à la documentation de l'API pour en savoir plus sur ces classes, en particulier comment les configurer et les utiliser. 

* [[yii\log\DbTarget]]: stocke les messages enregistrés dans une table de base de données.
* [[yii\log\EmailTarget]]: envoie les messages enregistrés vers une adresse de courriel spécifiée préalablement. 
* [[yii\log\FileTarget]]: sauvegarde les messages enregistrés dans des fichiers. 
* [[yii\log\SyslogTarget]]: sauvegarde les messages enregistrés vers *syslog* en appelant la fonction PHP `syslog()`.

Dans la suite de ce document, nous décrivons les fonctionnalités communes à toutes les cibles d'enregistrement. 

  
### Filtrage des messages <span id="message-filtering"></span>

Vous pouvez configurer les propriétés [[yii\log\Target::levels|levels]] et [[yii\log\Target::categories|categories]] de chacune des cibles d'enregistrement pour spécifier les niveaux de sévérité et les catégories que la cible doit traiter. 

La propriété [[yii\log\Target::levels|levels]] accepte un tableau constitué d'une ou plusieurs des valeurs suivantes :

* `error`: correspondant aux messages enregistrés par [[Yii::error()]].
* `warning`: correspondant aux messages enregistrés par [[Yii::warning()]].
* `info`: correspondant aux messages enregistrés par [[Yii::info()]].
* `trace`: correspondant aux messages enregistrés par [[Yii::debug()]].
* `profile`: correspondant aux messages enregistrés par [[Yii::beginProfile()]] et [[Yii::endProfile()]], et qui sera expliqué en détails dans la sous-section [Profilage de la performance](#performance-profiling).

Si vous ne spécifiez pas la propriété [[yii\log\Target::levels|levels]], cela signifie que la cible traitera les messages de *n'importe quel* niveau de sévérité. 

La propriété [[yii\log\Target::categories|categories]] accepte un tableau constitué de noms ou de motifs de noms de catégorie de messages. Une cible ne traite 
que les messages dont la catégorie est trouvée ou correspond aux motifs de ce tableau. Un motif de nom de catégorie est un préfixe de nom de catégorie 
suivi d'une astérisque `*`. Un nom de catégorie correspond à un motif de nom de catégorie s'il commence par le préfixe du motif. 
Par exemple, `yii\db\Command::execute` et `yii\db\Command::query` sont utilisés comme noms de catégorie pour les messages enregistrés dans la classe [[yii\db\Command]]. Ils correspondent tous deux au motif `yii\db\*`.

Si vous ne spécifiez pas la propriété [[yii\log\Target::categories|categories]], cela signifie que le cible traite les messages de *n'importe quelle* catégorie. 

En plus d'inscrire des catégories en liste blanche via la propriété [[yii\log\Target::categories|categories]], vous pouvez également inscrire certaines catégories
en liste noire via la propriété [[yii\log\Target::except|except]]. Si la catégorie d'un message est trouvée ou correspond à un des motifs de cette propriété, ce message n'est PAS traité par la cible. 
 
La configuration suivante de cible spécifie que la cible  traitera les messages d'erreur ou d'avertissement des catégories dont le nom correspond soit à `yii\db\*`, soit `yii\web\HttpException:*`, mais pas `yii\web\HttpException:404`.

```php
[
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning'],
    'categories' => [
        'yii\db\*',
        'yii\web\HttpException:*',
    ],
    'except' => [
        'yii\web\HttpException:404',
    ],
]
```

> Info: lorsqu'une exception HTTP est capturée par le [gestionnaire d'erreur](runtime-handling-errors.md), un message d'erreur est enregistré avec un non de catégorie dont le format est `yii\web\HttpException:ErrorCode`. Par exemple, l'exception [[yii\web\NotFoundHttpException]] provoque un message d'erreur de catégorie `yii\web\HttpException:404`.


### Formatage des messages <span id="message-formatting"></span>

Les cibles d'enregistrement exportent les messages enregistrés et filtrés dans un certain format. Par exemple, si vous installez une cible d'enregistrement de classe [[yii\log\FileTarget]], vous pouvez trouver un message enregistré similaire au suivant dans le fichier `runtime/log/app.log` file:

```
2014-10-04 18:10:15 [::1][][-][trace][yii\base\Module::getModule] Loading module: debug
```

Par défaut, les messages enregistrés sont formatés comme suit par la méthode [[yii\log\Target::formatMessage()]]:

```
Horodate [adresse IP][identifiant utilisateur][identifiant de session][niveau de sévérité][catégorie] Texte du message
```

Vous pouvez personnaliser ce format en configurant la propriété [[yii\log\Target::prefix]] qui accepte une fonction PHP appelable qui retourne un message de préfixe personnalisé. Par exemple, le code suivant configure une cible d'enregistrement pour qu'elle préfixe chaque message enregistré avec l'identifiant de l'utilisateur courant (l'adresse IP et l'identifiant de session étant retirés pour des raisons de protection de la vie privée).

```php
[
    'class' => 'yii\log\FileTarget',
    'prefix' => function ($message) {
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        return "[$userID]";
    }
]
```

En plus des préfixes de messages, les cibles d'enregistrement ajoutent aussi quelques informations de contexte à chaque lot de messages enregistrés. 
Par défaut, les valeurs de ces variables PHP globales sont incluses : `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`, `$_SESSION` et `$_SERVER`. Vous pouvez ajuster ce comportement en configurant la propriété [[yii\log\Target::logVars]] avec les noms des variables globales que vous voulez que la cible d'enregistrement inclue. Par exemple, la cible d'enregistrement suivante spécifie que seules les valeurs de la variable `$_SERVER` seront ajoutées aux messages enregistrés.
```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
]
```

Vous pouvez configurer  `logVars` comme un tableau vide pour désactiver totalement l'inclusion d'informations de contexte. Ou si vous voulez mettre en œuvre votre propre façon de fournir les informations contextuelles, vous pouvez redéfinir la méthode [[yii\log\Target::getContextMessage()]].


### Niveaux de la trace de message <span id="trace-level"></span>

Lors du développement, vous cherchez souvent à voir d'où provient chacun des messages enregistrés. Cela est possible en configurant la propriété [[yii\log\Dispatcher::traceLevel|traceLevel]] du composant`log` de la façon suivante :

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [...],
        ],
    ],
];
```

La configuration de l'application ci-dessus statue que le [[yii\log\Dispatcher::traceLevel|niveau de trace ]] sera  3 si `YII_DEBUG` est activé et 0 si `YII_DEBUG` est désactivé. Cela veut dire que, si `YII_DEBUG` est activé, au plus trois niveaux de la pile des appels seront ajoutés à chaque message enregistré, là où le messages est enregistré ; et, si  `YII_DEBUG`  est désactivé, aucune information de la pile des appels ne sera incluse. 

> Info: obtenir les informations de la pile des appels n'a rien de trivial. En conséquence, vous ne devriez utiliser cette fonctionnalité que durant le développement ou le débogage d'une application. 


### Purge et exportation des messages <span id="flushing-exporting"></span>

Comme nous l'avons dit plus haut, les messages enregistrés sont conservés dans un tableau par l'[[yii\log\Logger|objet *logger*]]. Pour limiter la consommation de mémoire par ce tableau, l'objet *logger* purge les messages enregistrés vers les [cibles d'enregistrement](#log-targets) chaque fois que leur nombre atteint une certaine valeur. Vous pouvez personnaliser ce nombre en configurant la propriété [[yii\log\Dispatcher::flushInterval|flushInterval]] du composant `log` :


```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 100,   // default is 1000
            'targets' => [...],
        ],
    ],
];
```

> Info: la purge des messages intervient aussi lorsque l'application se termine, ce qui garantit que les cibles d'enregistrement reçoivent des messages enregistrés complets. 

Lorsque l'[[yii\log\Logger|objet *logger*]] purge les messages enregistrés vers les [cibles d'enregistrement](#log-targets), ils ne sont pas exportés immédiatement. Au lieu de cela, l'exportation des messages ne se produit que lorsque la cible d'enregistrement a accumulé un certain nombre de messages filtrés. Vous pouvez personnaliser ce nombre en configurant la propriété [[yii\log\Target::exportInterval|exportInterval]] de chacune des [cibles d'enregistrement](#log-targets), comme ceci :

```php
[
    'class' => 'yii\log\FileTarget',
    'exportInterval' => 100,  // default is 1000
]
```

À cause des niveaux de purge et d'exportation, par défaut, lorsque vous appelez `Yii::debug()` ou toute autre méthode d'enregistrement, vous ne voyez PAS immédiatement le message enregistré dans la cible. Cela peut représenter un problème pour pour certaines applications de console qui durent longtemps. Pour faire en sorte que les messages apparaissent immédiatement dans les cibles d'enregistrement, vous devriez définir les propriétés [[yii\log\Dispatcher::flushInterval|flushInterval]] et [[yii\log\Target::exportInterval|exportInterval]] toutes deux à 1, comme montré ci-après :

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                ],
            ],
        ],
    ],
];
```

> Note: la purge et l'exportation fréquentes de vos messages dégradent la performance de votre application. 


### Activation, désactivation des cibles d'enregistrement <span id="toggling-log-targets"></span>

Vous pouvez activer ou désactiver une cible d'enregistrement en configurant sa propriété [[yii\log\Target::enabled|enabled]]. Vous pouvez le faire via la configuration de la cible d'enregistrement ou en utilisant l'instruction suivante dans votre code PHP :

```php
Yii::$app->log->targets['file']->enabled = false;
```

Le code ci-dessus, nécessite que nommiez une cible `file`, comme montré ci-dessous en utilisant des clés sous forme de chaînes de caractères  dans le tableau `targets` :

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                ],
                'db' => [
                    'class' => 'yii\log\DbTarget',
                ],
            ],
        ],
    ],
];
```


### Création d'une cible d'enregistrement <span id="new-targets"></span>

La création d'une classe de cible d'enregistrement est très simple. Vous devez essentiellement implémenter [[yii\log\Target::export()]] en envoyant le contenu du tableau des [[yii\log\Target::messages]] vers un média désigné. Vous pouvez appeler la méthode [[yii\log\Target::formatMessage()]] pour formater chacun des messages. Pour plus de détails, reportez-vous à n'importe quelle classe de cible de messages incluse dans la version de Yii. 


## Profilage de la performance <span id="performance-profiling"></span>

Le profilage de la performance est un type particulier d'enregistrement de messages qui est utilisé pour mesurer le temps d'exécution de certains blocs de code et pour déterminer les goulots d'étranglement. Par exemple, la classe [[yii\db\Command]] utilise le profilage de performance pour connaître le temps d'exécution de chacune des requêtes de base de données. 

Pour utiliser le profilage de la  performance, commencez par identifier les blocs de code qui ont besoin d'être profilés. Puis, entourez-les de la manière suivante :

```php
\Yii::beginProfile('myBenchmark');

...le bloc de code à profiler...

\Yii::endProfile('myBenchmark');
```

où `myBenchmark` représente un jeton unique identifiant un bloc de code. Plus tard, lorsque vous examinez le résultat du profilage, vous pouvez utiliser ce jeton pour localiser le temps d'exécution du bloc correspondant. 

Il est important de vous assurer que les paires  `beginProfile` et `endProfile` sont correctement imbriquées.
Par exemple,

```php
\Yii::beginProfile('block1');

    // du code à profiler

    \Yii::beginProfile('block2');
        // un autre bloc de code à profiler
    \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

Si vous omettez `\Yii::endProfile('block1')` ou inversez l'ordre de `\Yii::endProfile('block1')` et de `\Yii::endProfile('block2')`, le profilage de performance ne fonctionnera pas. 

Pour chaque bloc de code profilé, un message est enregistré avec le niveau de sévérité `profile`. Vous pouvez configurer une [cible d'enregistrement](#log-targets) pour collecter de tels messages et les exporter. L'outil [*Yii debugger*](tool-debugger.md) comporte un panneau d'affichage pré-construit des résultats de profilage.
