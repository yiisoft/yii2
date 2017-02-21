Travail avec des scripts clients
===================================

> Note: cette section est encore en développement.

### Enregistrement des scripts

Avec l'objet [[yii\web\View]] vous êtes en mesure d'enregistrer des scripts. Il existe deux méthodes dédiées pour cela :
[[yii\web\View::registerJs()|registerJs()]] pour les scripts en ligne et [[yii\web\View::registerJsFile()|registerJsFile()]] pour les scripts externes.


Les scripts en ligne sont utiles pour la configuration et le code généré dynamiquement. La méthode pour les ajouter est la suivante :


```php
$this->registerJs("var options = ".json_encode($options).";", View::POS_END, 'my-options');
```

Le premier argument est le code JS réel à insérer dans la page. Le deuxième argument détermine à quel endroit le script doit être inséré dans la page. Les valeurs possibles sont : 
- [[yii\web\View::POS_HEAD|View::POS_HEAD]] pour le placer dans la section d'entête (`<head></head>`).
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] pour le placer juste après la balise d'ouverture du corps de la page (`<body>`).
- [[yii\web\View::POS_END|View::POS_END]] pour le placer juste avant la balise de fermeture du corps de la page (`</body>`).
- [[yii\web\View::POS_READY|View::POS_READY]] pour l'exéuter sur l'événement  « document `ready` ». Cela enregistre [[yii\web\JqueryAsset|jQuery]] automatiquement.
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] pour l'exécuter sur l'événement « document `load` » . Cela enregistre [[yii\web\JqueryAsset|jQuery]] automatiquement.

Le dernier argument est un identifiant unique du script utilisé pour identifier le bloc de code et remplacer un bloc existant de même identifiant au lieu de simplement l'ajouter. Si vous ne le fournissez pas, le code JS lui-même est utilisé en tant qu'identifiant. 

Un script externe peut être ajouté comme expliqué ci-dessous : 

```php
$this->registerJsFile('http://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::class]]);
```

Les arguments pour  [[yii\web\View::registerJsFile()|registerJsFile()]] sont semblables à ceux utilisés pour [[yii\web\View::registerCssFile()|registerCssFile()]]. Dans l'exemple précédent, nous enregistrons le fichier `main.js` avec une dépendance sur `JqueryAsset`. Cela siginifie que le fichier `main.js` sera ajouté APRÈS `jquery.js`. Sans la spécification de cette dépendance, l'ordre relatif entre `main.js` et `jquery.js` resterait indéfini.

Comme pour [[yii\web\View::registerCssFile()|registerCssFile()]], il est également fortement recommandé que vous utilisiez les [paquets de ressources](structure-assets.md) (asset bundles) pour enregistrer des fichiers JS externes plutôt que d'utiliser [[yii\web\View::registerJsFile()|registerJsFile()]].


### Enregistrement de paquets de ressources

Comme cela a été mentionné plus tôt, il est préférable d'utiliser des paquets de ressources plutôt que d'utiliser CSS et JavaScript directement. Vous pouvez obtenir des détails sur les paquets de ressources dans la section [Ressources](structure-assets.md) de ce guide. Comme lors de l'utilisation des paquets de ressources déjà définis, c'est très simple :

```php
\frontend\assets\AppAsset::register($this);
```


### Enregistrement des CSS

Vous pouvez enregistrer les CSS en utilisant [[yii\web\View::registerCss()|registerCss()]] ou [[yii\web\View::registerCssFile()|registerCssFile()]]. Le premier enregistre un bloc de code CSS tandis que le second enregistre un fichier CSS externe. Par exemple :

```php
$this->registerCss("body { background: #f00; }");
```

Le code ci-dessus provoque l'ajout de ce qui suit à la section « head » :

```html
<style>
body { background: #f00; }
</style>
```

Si vous désirez spécifier des propriétés additionnelles du style balise, passez un tableau des paires nom-valeur en tant que troisième argument. Si vous avez besoin de vous assurer qu'il y a seulement une balise style unique, utilisez un quatrième argument comme cela a été mentionné dans la description des balises méta. 

```php
$this->registerCssFile("http://example.com/css/themes/black-and-white.css", [
    'depends' => [BootstrapAsset::class],
    'media' => 'print',
], 'css-print-theme');
```

Le code ci-dessus provoque l'ajout d'un lien vers un fichier CSS à la section « head » de la page.  

* Le premier argument spécifie le fichier CSS à enregistrer. 
* Le deuxième argument spécifie l'attribut HTML pour la balise `<link>` résultant. L'option `depends` fait l'objet d'une interprétation particulière. Elle spécifie de quel paquet de ressources  ce fichier dépend. Dans ce cas, le paquet de ressources dont le ficher dépend est [[yii\bootstrap\BootstrapAsset|BootstrapAsset]]. Cela veut dire que le fichier CSS sera ajouté *après* les fichiers CSS contenus dans [[yii\bootstrap\BootstrapAsset|BootstrapAsset]].
* Le dernier argument spécifie un identifiant pour ce fichier CSS. S'il n'est pas fourni, l'URL du fichier CSS est utilisée à sa place. 


Il est fortement recommandé que vous utilisiez des [paquets de ressources](structure-assets.md) pour enregistrer des fichers CSS externes plutôt que [[yii\web\View::registerCssFile()|registerCssFile()]]. L'utilisation des paquets de ressources vous permet de combiner et de comprimer plusieurs fichiers CSS, ce qui est souhaitable pour les sites Web à trafic intense.
