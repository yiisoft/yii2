Migrations de base de données
=============================

Durant la période de développement et de maintenance d'une application s'appuyant sur une base de données, la structure de la base de données évolue tout comme le code source. Par exemple, durant développement une nouvelle table peut devenir nécessaire ; après que l'application est déployée en production, on peut s'apercevoir qu'un index doit être créé pour améliorer la performance des requêtes ; et ainsi de suite. Comme un changement dans la base de données nécessite souvent des changements dans le code, Yii prend en charge une fonctionnalité qu'on appelle *migrations de base de données*. Cette fonctionnalité permet de conserver la trace des changements de la base de données en termes de *migrations de base de données* dont les versions sont contrôlées avec celles du code.

Les étapes suivantes montrent comment des migrations de base de données peuvent être utilisées par une équipe durant la phase de développement :

1. Tim crée une nouvelle migration (p. ex. créer une nouvelle table, changer la définition d'une colonne, etc.).
2. Tim entérine (commit) la nouvelle migration dans le système de contrôle de version (p. ex. Git, Mercurial).
3. Doug met à jour son dépôt depuis le système de contrôle de version et reçoit la nouvelle migration. 
4. Doug applique la migration à sa base de données de développement locale, et ce faisant synchronise sa base de données pour refléter les changements que Tim a faits.

Les étapes suivantes montrent comment déployer une nouvelle version avec les migrations de base de données en production :

1. Scott crée une balise de version pour le dépôt du projet qui contient quelques nouvelles migrations de base de données.
2. Scott met à jour le code source sur le serveur de production à la version balisée. 
3. Scott applique toutes les migrations accumulées à la base de données de production.

Yii fournit un jeu de commandes de migration en ligne de commande qui vous permet de :

* créer de nouvelles migrations;
* appliquer les migrations;
* défaire les migrations;
* ré-appliquer les migrations;
* montrer l'historique de l'état des migrations.

Tous ces outils sont accessibles via la commande `yii migrate`. Dans cette section nous décrivons en détails comment accomplir des tâches variées en utilisant ces outils. Vous pouvez aussi obtenir les conseils d'utilisation de chacun des outils via la commande d'aide `yii help migrate`.

> Astuce : les migrations peuvent non seulement affecter le schéma de base de données mais aussi ajuster les données existantes pour s'adapter au nouveau schéma, créer la hiérarchie RBAC (Role Based Acces Control - Contrôle d'accès basé sur les rôles), ou vider le cache.

> Note : lors de la manipulation de données utilisant une migration, vous pouvez trouver qu'utiliser vos classes  [Active Record](db-active-record.md) 
> pour cela peut s'avérer utile parce qu'une partie de la logique y est déjà mise en œuvre. Soyez cependant conscient que, contrairement
> au code écrit dans les migrations, dont la nature est de rester constant à jamais, la logique d'application est sujette à modification.
> C'est pourquoi, lorsque vous utilisez des classes ActiveRecord dans le code d'une migration, des modifications de la logique de l'ActiveRecord peuvent accidentellement casser
> des migrations existantes. Pour cette raison, le code des migrations devrait être conservé indépendant d'autres logiques d'application telles que celles des classes ActiveRecord.

## Création de migrations <span id="creating-migrations"></span>

Pour créer une nouvelle migration, exécutez la commande suivante : 

```
yii migrate/create <name>
```

L'argument `name` requis donne une brève description de la nouvelle migration. Par exemple, si la création concerne la création d'une nouvelle table nommée *news*, vous pouvez utiliser le nom `create_news_table` et exécuter la commande suivante :

```
yii migrate/create create_news_table
```

> Note: comme l'argument `name` est utilisé comme partie du nom de la classe migration générée, il ne doit contenir que des lettres, des chiffre et/ou des caractères *souligné*. 

La commande ci-dessus crée une nouvelle classe PHP nommée `m150101_185401_create_news_table.php` dans le dossier `@app/migrations`. Le fichier contient le code suivant qui déclare principalement une classe de migration `m150101_185401_create_news_table` avec le squelette de code suivant :

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {

    }

    public function down()
    {
        echo "m101129_185401_create_news_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
```

Chaque migration de base de données est définie sous forme de classe PHP étendant la classe [[yii\db\Migration]]. Le nom de la classe de migration est généré automatiquement dans le format `m<YYMMDD_HHMMSS>_<Name>`, dans lequel :

* `<YYMMDD_HHMMSS>` fait référence à l'horodate UTC à laquelle la commande de création de la migration a été exécutée.
* `<Name>` est le même que la valeur que vous donnez à l'argument `name` dans la commande.

Dans la classe de migration, vous devez écrire du code dans la méthode `up()` qui effectue les modifications dans la structure de la base de données. Vous désirez peut-être écrire du code dans la méthode `down()` pour défaire les changements apportés par `up()`. La méthode `up()` est invoquée lorsque vous mettez à jour la base de données avec la migration, tandis que la méthode `down()` est invoquée lorsque vous ramenez la base de données à l'état antérieur. Le code qui suit montre comment mettre en œuvre la classe de migration pour créer une table `news` :

```php
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

> Info: toutes les migrations ne sont pas réversibles. Par exemple, si la méthode `up()` supprime une ligne dans une table, il se peut que vous soyez incapable de récupérer cette ligne dans la méthode `down()`. Parfois, vous pouvez simplement être trop paresseux pour implémenter la méthode `down`, parce que défaire une migration de base de données n'est pas chose courante. Dans ce cas, vous devriez retourner `false` dans la méthode `down()` pour indiquer que la migration n'est pas réversible. 

La classe de migration de base [[yii\db\Migration]] expose une connexion à une base de données via la propriété [[yii\db\Migration::db|db]]. Vous pouvez utiliser cette connexion pour manipuler le schéma en utilisant les méthodes décrites dans la sous-section [Travail avec le schéma de base de données](db-dao.md#database-schema).

Plutôt que d'utiliser des types physiques, lors de la création d'une table ou d'une colonne, vous devez utiliser des *types abstraits* afin que vos migrations soient indépendantes d'un système de gestion de base de données en particulier. La classe [[yii\db\Schema]] définit une jeu de constantes pour représenter les types abstraits pris en charge. Ces constantes sont nommées dans le format `TYPE_<Name>`. Par exemple, `TYPE_PK` fait référence au type clé primaire à auto-incrémentation ; `TYPE_STRING` fait référence au type chaîne de caractères. Lorsqu'une migration est appliquée à une base de données particulière, le type abstrait est converti dans le type physique correspondant. Dans le cas de MySQL, `TYPE_PK` est transformé en `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, tandis que `TYPE_STRING` est transformé en `varchar(255)`.

Vous pouvez ajouter des contraintes additionnelles lors de l'utilisation des types abstraits. Dans l'exemple ci-dessus,` NOT NULL` est ajouté à `Schema::TYPE_STRING` pour spécifier que la colonne ne peut être `null` (nulle).

> Info: la mise en correspondance entre les types abstraits et les types physiques est spécifiée par la propriété [[yii\db\QueryBuilder::$typeMap|$typeMap]] dans chacune des classes `QueryBuilder` concrètes.

Depuis la version 2.0.6, vous pouvez utiliser le constructeur de schéma récemment introduit qui procure un moyen plus pratique de définir le schéma d'une colonne. Ainsi, la migration ci-dessus pourrait s'écrire comme ceci :

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

Une liste de toutes les méthodes disponibles pour définir les types de colonne est disponible dans la documentation de l'API de [[yii\db\SchemaBuilderTrait]].


## Génération des migrations <span id="generating-migrations"></span>

Depuis la version 2.0.7, la commande de migration procure un moyen pratique de créer des migrations. 

Si le nom de la migration est d'une forme spéciale, par exemple, `create_xxx_table` ou `drop_xxx_table` alors le fichier de la migration générée contient du code supplémentaire, dans ce cas pour créer/supprimer des tables. Dans ce qui suit, toutes les variantes de cette fonctionnalité sont décrites. 

### Création d'une table

```php
yii migrate/create create_post_table
```

génère

```php
/**
 * prend en charge la création de la table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

Pour créer les champs de table tout de suite, spécifiez les via l'option `--fields`.

```php
yii migrate/create create_post_table --fields="title:string,body:text"
```

génère

```php
/**
 * prend en charge la création de la table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}

```

Vous pouvez spécifier plus de paramètres de champs.

```php
yii migrate/create create_post_table --fields="title:string(12):notNull:unique,body:text"
```

génère

```php
/**
 * prend en charge la création de la table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

> Note: par défaut, une clé primaire nommée `id` est ajoutée automatiquement. Si vous voulez utiliser un autre nom, vous devez le spécifier explicitement comme dans `--fields="name:primaryKey"`.

#### Clés étrangères

Depuis 2.0.8 le générateur prend en charge les clés étrangères en utilisant le mot clé `foreignKey`.

```php
yii migrate/create create_post_table --fields="author_id:integer:notNull:foreignKey(user),category_id:integer:defaultValue(1):foreignKey,title:string,body:text"
```

génère

```php
/**
 * prend en charge la création de la table `post`.
 * possède des clés étrangères vers les tables
 *
 * - `user`
 * - `category`
 */
class m160328_040430_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->defaultValue(1),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);

        // crée un index pour la colonne `author_id`
        $this->createIndex(
            'idx-post-author_id',
            'post',
            'author_id'
        );

        // ajoute une clé étrangère vers la table `user`
        $this->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );

        // crée un index pour la colonne `category_id`
        $this->createIndex(
            'idx-post-category_id',
            'post',
            'category_id'
        );

        // ajoute une clé étrangère vers la table `category`
        $this->addForeignKey(
            'fk-post-category_id',
            'post',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // supprime la clé étrangère vers la table `user`
        $this->dropForeignKey(
            'fk-post-author_id',
            'post'
        );

        // supprime l'index pour la colonne `author_id`
        $this->dropIndex(
            'idx-post-author_id',
            'post'
        );

        // supprime la clé étrangère vers la table `category`
        $this->dropForeignKey(
            'fk-post-category_id',
            'post'
        );

        // supprime l'index pour la colonne `category_id`
        $this->dropIndex(
            'idx-post-category_id',
            'post'
        );

        $this->dropTable('post');
    }
}
```

La position du mot clé `foreignKey` dans la description de la colonne ne change pas le code généré. Ce qui signifie que les expressions :

- `author_id:integer:notNull:foreignKey(user)`
- `author_id:integer:foreignKey(user):notNull`
- `author_id:foreignKey(user):integer:notNull`

génèrent toutes le même code.

Le mot clé `foreignKey` accepte un paramètre entre parenthèses qui est le nom de la table en relation pour la clé étrangère générée. Si aucun paramètre n'est passé, le nom de table est déduit du nom de la colonne. 

Dans l'exemple ci-dessus `author_id:integer:notNull:foreignKey(user)` génère une colonne nommée `author_id` avec une clé étrangère pointant sur la table `user`, tandis que `category_id:integer:defaultValue(1):foreignKey` génère une colonne `category_id` avec une clé étrangère pointant sur la table `category`.

Depuis la version 2.0.11, le mot clé `foreignKey` accepte un second paramètre, séparé par une espace. 
Il accepte le nom de la colonne en relation pour la clé étrangère générée. 
Si aucun second paramètre n'est passé, le nom de la colonne est retrouvé dans le schéma de table.
Si aucun schéma n'existe, la clé primaire n'est pas définie ou est composite, le nom par défaut `id` est utilisé.



### Suppression de tables

```php
yii migrate/create drop_post_table --fields="title:string(12):notNull:unique,body:text"
```

génère

```php
class m150811_220037_drop_post_table extends Migration
{
    public function up()
    {
        $this->dropTable('post');
    }

    public function down()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }
}
```

### Ajout de colonnes

Si le nom de la migration est de la forme `add_xxx_column_to_yyy_table` alors le fichier doit contenir les instructions `addColumn` et `dropColumn` nécessaires.

Pour ajouter une colonne :

```php
yii migrate/create add_position_column_to_post_table --fields="position:integer"
```

génère

```php
class m150811_220037_add_position_column_to_post_table extends Migration
{
    public function up()
    {
        $this->addColumn('post', 'position', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('post', 'position');
    }
}
```
Vous pouvez spécifier de multiples colonnes comme suit :

```
yii migrate/create add_xxx_column_yyy_column_to_zzz_table --fields="xxx:integer,yyy:text"
```

### Supprimer une colonne

Si le nom de la migration est de la forme `drop_xxx_column_from_yyy_table` alors le fichier doit contenir les instructions `addColumn` et `dropColumn` néessaires.

```php
yii migrate/create drop_position_column_from_post_table --fields="position:integer"
```

génère

```php
class m150811_220037_drop_position_column_from_post_table extends Migration
{
    public function up()
    {
        $this->dropColumn('post', 'position');
    }

    public function down()
    {
        $this->addColumn('post', 'position', $this->integer());
    }
}
```

### Ajout d'une table de jointure

Si le nom de la migration est de la forme `create_junction_table_for_xxx_and_yyy_tables` ou `create_junction_xxx_and_yyy_tables`, alors le code nécessaire à la création de la table de jointure est généré.

```php
yii migrate/create create_junction_table_for_post_and_tag_tables --fields="created_at:dateTime"
```

génère

```php
/**
 * prend en charge la création de la table `post_tag`.
 * possède des clés étrangères vers les tables:
 *
 * - `post`
 * - `tag`
 */
class m160328_041642_create_junction_table_for_post_and_tag_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post_tag', [
            'post_id' => $this->integer(),
            'tag_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'PRIMARY KEY(post_id, tag_id)',
        ]);

        // crée un index pour la colonne`post_id`
        $this->createIndex(
            'idx-post_tag-post_id',
            'post_tag',
            'post_id'
        );

        // ajoute un clé étrangère vers la table `post`
        $this->addForeignKey(
            'fk-post_tag-post_id',
            'post_tag',
            'post_id',
            'post',
            'id',
            'CASCADE'
        );

        // crée un index pour la colonne `tag_id`
        $this->createIndex(
            'idx-post_tag-tag_id',
            'post_tag',
            'tag_id'
        );

        // ajoute une clé étrangère vers la table `tag`
        $this->addForeignKey(
            'fk-post_tag-tag_id',
            'post_tag',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // supprime la clé étrangère vers la table `post`
        $this->dropForeignKey(
            'fk-post_tag-post_id',
            'post_tag'
        );

        // supprime l'index pour la colonne `post_id`
        $this->dropIndex(
            'idx-post_tag-post_id',
            'post_tag'
        );

        // supprime la clé étrangère vers la table `tag`
        $this->dropForeignKey(
            'fk-post_tag-tag_id',
            'post_tag'
        );

        // supprime l'index pour la column `tag_id`
        $this->dropIndex(
            'idx-post_tag-tag_id',
            'post_tag'
        );

        $this->dropTable('post_tag');
    }
}
```

Depuis la version 2.0.1, les noms de colonne des clés étrangères pour les tables de jonction sont recherchées dans le schéma de table.
Dans le cas où la table n'est pas définie dans le schéma, ou quand la clé primaire n'est pas définie ou est composite, le nom par défaut `id` est utilisé.

### Migrations transactionnelles <span id="transactional-migrations"></span>

En effectuant des migration de base de données complexes, il est important de garantir que chacune des migrations soit réussisse, soit échoue dans son ensemble, de manière à ce que la base de données reste cohérente et intègre. Pour atteindre ce but, il est recommandé que vous englobiez les opérations de base de données de chacune des migrations dans une [transaction](db-dao.md#performing-transactions).

Une manière encore plus aisée pour mettre en œuvre des migrations transactionnelles est de placer le code de migration dans les méthodes `safeUp()` et `safeDown()`. Ces deux méthodes diffèrent de `up()` et `down()` par le fait qu'elles sont implicitement englobées dans une transaction. En conséquence, si n'importe quelle opération de ces méthodes échoue, toutes les opérations antérieures à elle sont automatiquement défaites. 

Dans l'exemple suivant, en plus de créer la table `news`, nous insérons une ligne initiale dans cette table. 

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);

        $this->insert('news', [
            'title' => 'test 1',
            'content' => 'content 1',
        ]);
    }

    public function safeDown()
    {
        $this->delete('news', ['id' => 1]);
        $this->dropTable('news');
    }
}
```

Notez que, généralement, si vous effectuez de multiples opérations de base de données dans `safeUp()`, vous devriez les défaire dans `safeDown()`. Dans l'exemple ci-dessus, dans `safeUp()`, nous créons d'abord la table puis nous insérons une ligne, tandis que, dans `safeDown`, nous commençons par supprimer la ligne, puis nous supprimons la table. 

> Note: tous les systèmes de gestion de bases de données NE prennent PAS en charge les transactions. De plus, quelques requêtes de base de données ne peuvent être placées dans une transaction. Pour quelques exemples, reportez-vous à [entérinement implicite](https://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html). Si c'est le cas, vous devez simplement mettre en œuvre `up()` et`down()`, à la place.


### Méthodes d'accès aux bases de données <span id="db-accessing-methods"></span>

La classe de base de migration [[yii\db\Migration]] fournit un jeu de méthodes pour vous permettre d'accéder aux bases de données et de les manipuler. Vous vous apercevrez que ces méthodes sont nommées de façon similaires aux [méthodes d'objets d'accès aux données](db-dao.md) fournies par la classe [[yii\db\Command]]. Par exemple, la méthode [[yii\db\Migration::createTable()]] vous permet de créer une nouvelle table, tout comme [[yii\db\Command::createTable()]].

L'avantage d'utiliser les méthodes fournies par [[yii\db\Migration]] est que vous n'avez pas besoin de créer explicitement des instances de [[yii\db\Command]] et que l'exécution de chacune des méthodes affiche automatiquement des messages utiles vous indiquant que les opérations de base de données sont effectuées et combien de temps ces opérations ont pris. 

Ci-dessous, nous présentons la liste de toutes les méthodes d'accès aux bases de données : 

* [[yii\db\Migration::execute()|execute()]]: exécute une instruction SQL
* [[yii\db\Migration::insert()|insert()]]: insère une unique ligne
* [[yii\db\Migration::batchInsert()|batchInsert()]]: insère de multiples lignes
* [[yii\db\Migration::update()|update()]]: met à jour des lignes
* [[yii\db\Migration::delete()|delete()]]: supprime des lignes
* [[yii\db\Migration::createTable()|createTable()]]: crée une table
* [[yii\db\Migration::renameTable()|renameTable()]]: renomme une table
* [[yii\db\Migration::dropTable()|dropTable()]]: supprime une table
* [[yii\db\Migration::truncateTable()|truncateTable()]]: supprime toutes les lignes d'une table
* [[yii\db\Migration::addColumn()|addColumn()]]: ajoute une colonne
* [[yii\db\Migration::renameColumn()|renameColumn()]]: renomme une colonne
* [[yii\db\Migration::dropColumn()|dropColumn()]]: supprime une colonne
* [[yii\db\Migration::alterColumn()|alterColumn()]]: modifie une colonne
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: ajoute une clé primaire
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: supprime une clé primaire
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: ajoute une clé étrangère
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: supprime une clé étrangère
* [[yii\db\Migration::createIndex()|createIndex()]]: crée un index
* [[yii\db\Migration::dropIndex()|dropIndex()]]: supprime un index
* [[yii\db\Migration::addCommentOnColumn()|addCommentOnColumn()]]: ajoute un commentaire à une colonne
* [[yii\db\Migration::dropCommentFromColumn()|dropCommentFromColumn()]]: supprime un commentaire d'une colonne
* [[yii\db\Migration::addCommentOnTable()|addCommentOnTable()]]: ajoute un commentaire à une table
* [[yii\db\Migration::dropCommentFromTable()|dropCommentFromTable()]]: supprime un commentaire d'une table

> Info: [[yii\db\Migration]] 
> ne fournit pas une méthode de requête de base de données. C'est parce que, normalement, vous n'avez pas besoin d'afficher de messages supplémentaire à propos de l'extraction de données dans une base de données. C'est aussi parce que vous pouvez utiliser le puissant [constructeur de requêtes](db-query-builder.md) pour construire et exécuter des requêtes complexes. 
> L'utilisation du constructeur de requêtes dans une migration ressemble à ceci :
>
> ```php
> // update status field for all users
> foreach((new Query)->from('users')->each() as $user) {
>     $this->update('users', ['status' => 1], ['id' => $user['id']]);
> }
> ```

## Application des migrations <span id="applying-migrations"></span>

Pour mettre une base de données à jour à sa dernière structure, vous devez appliquer toutes les nouvelles migrations disponibles en utilisant la commande suivante :

```
yii migrate
```

Cette commande liste toutes les migrations qui n'ont pas encore été appliquées. Si vous confirmez que vous voulez appliquer ces migrations, cela provoque l'exécution des méthodes `up()` ou `safeUp()` de chacune des nouvelles migrations, l'une après l'autre, dans l'ordre de leur horodate. Si l'une de ces migrations échoue, la commande se termine sans appliquer les migrations qui restent. 

> Astuce : dans le cas où votre serveur ne vous offre pas de ligne de commande, vous pouvez essayer [Web shell](https://github.com/samdark/yii2-webshell).

Pour chaque migration qui n'a pas été appliqué avec succès, la commande insère une ligne dans une table de base de données nommée `migration` pour enregistrer les applications réussies de la migration. Cela permet à l'outil de migration d'identifier les migrations qui ont été appliquées et celles qui ne l'ont pas été. 

> Info: l'outil de migration crée automatiquement la table `migration` dans la base de données spécifiée par l'option [[yii\console\controllers\MigrateController::db|db]] de la commande. Par défaut, la base de données est spécifiée dans le [composant d'application](structure-application-components.md) `db`.

Parfois, vous désirez peut-être appliquer une ou quelques migrations plutôt que toutes les migrations disponibles. Vous pouvez le faire en spécifiant le nombre de migrations que vous voulez appliquer en exécutant la commande. Par exemple, la commande suivante essaye d'appliquer les trois prochaines migrations disponibles :

```
yii migrate 3
```

Vous pouvez également spécifier explicitement une migration particulière à laquelle la base de données doit être amenée en utilisant la commande `migrate/to` dans l'un des formats suivants :

```
yii migrate/to 150101_185401                      # utiliser l'horodatage pour spécifier la migration
yii migrate/to "2015-01-01 18:54:01"              # utilise une chaîne de caractères qui peut être analysée par strtotime()
yii migrate/to m150101_185401_create_news_table   # utilise le nom complet 
yii migrate/to 1392853618                         # utilise un horodatage UNIX
```

S'il existe des migrations non appliquée antérieures à celle spécifiée, elles sont toutes appliquées avant que la migration spécifiée ne le soit.

Si la migration spécifiée a déjà été appliquée auparavant, toutes les migrations postérieures qui ont été appliquées sont défaites. 


## Défaire des migrations <span id="reverting-migrations"></span>

Pour défaire une ou plusieurs migrations que ont été appliquées auparavant, vous pouvez exécuter la commande suivante : 

```
yii migrate/down     # défait la migration appliquée le plus récemment
yii migrate/down 3   # défait les 3 migrations appliquées le plus récemment 

```

> Note: toutes les migrations ne sont PAS réversibles. Essayer de défaire de telles migrations provoque une erreur et arrête tout le processus de retour à l'état initial.


## Refaire des migrations <span id="redoing-migrations"></span>

Refaire (ré-appliquer) des migrations signifie d'abord défaire les migrations spécifiées puis les appliquer à nouveau. Cela peut être fait comme suit :

```
yii migrate/redo        # refait la dernière migration appliquée 
yii migrate/redo 3      # refait les 3 dernière migrations appliquées

```

> Note: si une  migration n'est pas réversible, vous ne serez pas en mesure de la refaire.

## Rafraîchir des Migrations <span id="refreshing-migrations"></span>

Deepuis la version 2.0.13, vous pouvez supprimer toutes les tables et clés étrangères de la base de données et ré-appliquer toutes les migrations depuis le début. 
```
yii migrate/fresh       # Tronçonne la base de données et applique toutes les migrations depuis le début

```

## Lister des migrations <span id="listing-migrations"></span>

Pour lister quelles migrations ont été appliquées et quelles migrations ne l'ont pas été, vous pouvez utiliser les commandes suivantes : 

```
yii migrate/history     # montre les 10 dernières migrations appliquées
yii migrate/history 5   # montre les 5 dernières migrations appliquées
yii migrate/history all # montre toutes les migrations appliquées

yii migrate/new         # montre les 10 premières nouvelles migrations 
yii migrate/new 5       # montre les 5 premières nouvelles migrations
yii migrate/new all     # montre toutes les nouvelles migrations
```


## Modification de l'historique des migrations <span id="modifying-migration-history"></span>

Au lieu d'appliquer ou défaire réellement des migrations, parfois, vous voulez peut-être simplement marquer que votre base de données a été portée à une certaine migration. Cela arrive souvent lorsque vous changer manuellement la base de données pour l'amener à un état particulier et que vous ne voulez pas que la migration correspondant à ce changement soit appliquée de nouveau par la suite. Vous pouvez faire cela avec la commande suivante :
```
yii migrate/mark 150101_185401                      # utilise un horodatage pour spécifier la migration 
yii migrate/mark "2015-01-01 18:54:01"              # utilise une chaîne de caractères qui peut être analysée par strtotime()
yii migrate/mark m150101_185401_create_news_table   # utilise le nom complet
yii migrate/mark 1392853618                         # utilise un horodatage UNIX
```

La commande modifie la table `migration` en ajoutant ou en supprimant certaines lignes pour indiquer que la base de données s'est vue appliquer toutes les migrations jusqu'à celle spécifiée. Aucune migration n'est appliquée ou défaite par cette commande. 

## Personnalisation des migrations <span id="customizing-migrations"></span>

Il y a plusieurs manières de personnaliser la commande de migration.


### Utilisation des options de ligne de commande <span id="using-command-line-options"></span>

La commande de migration possède quelques options en ligne de commande qui peuvent être utilisées pour personnaliser son comportement :

* `interactive`: boolean (valeur par défaut `true`), spécifie si la migration doit être effectuées en mode interactif. Lorsque cette option est `true`, l'utilisateur reçoit un message avant que la commande n'effectue certaines actions. Vous désirez peut-être définir cette valeur à `false` si la commande s'exécute en arrière plan. 

* `migrationPath`: string (valeur par défaut `@app/migrations`), spécifie le dossier qui stocke tous les fichiers de classe de  migration. Cela peut être spécifié soit comme un chemin de dossier, soit comme un [alias](concept-aliases.md) de chemin. Notez que le dossier doit exister sinon la commande déclenche une erreur.

* `migrationTable`: string (valeur par défaut `migration`), spécifie le nom de la table de base de données pour stocker l'historique de migration. La table est créée automatiquement par la commande si elle n'existe pas encore. Vous pouvez aussi la créer à la main en utilisant la structure `version varchar(255) primary key, apply_time integer`.

* `db`: string (valeur par défaut `db`), spécifie l'identifiant du [composant d'application](structure-application-components.md) base de données. Il représente la base de données à laquelle les migrations sont appliquées avec cette commande. 

* `templateFile`: string (valeur par défaut `@yii/views/migration.php`), spécifie le chemin vers le fichier modèle qui est utilisé pour générer le squelette des fichiers de classe de migration. Cela peut être spécifié soit sous forme de chemin de fichier, soit sous forme d'[alias](concept-aliases.md) de chemin. Le fichier modèle est un script PHP dans lequel vous pouvez utiliser une variable prédéfinie nommée `$className` pour obtenir le nom de la classe de migration. 

* `generatorTemplateFiles`: array (valeur par défaut `[
        'create_table' => '@yii/views/createTableMigration.php',
        'drop_table' => '@yii/views/dropTableMigration.php',
        'add_column' => '@yii/views/addColumnMigration.php',
        'drop_column' => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php'
  ]`), spécifie les fichiers modèles pour générer le code de migration. Voir "[Génération des migrations](#generating-migrations)" pour plus de détails.

* `fields`: array (tableau) de chaîne de caractères de définition de colonnes utilisées pour créer le code de migration. Valeur par défaut `[]`. Le format de chacune des définitions est `COLUMN_NAME:COLUMN_TYPE:COLUMN_DECORATOR`. Par exemple, `--fields=name:string(12):notNull` produit une colonne chaîne de caractères de taille 12 qui n'est pas `null` (nulle).

L'exemple suivant montre comment vous pouvez utiliser ces options. 

Par exemple, si vous voulez appliquer des migrations à un module `forum` dont les fichiers de migration sont situés dans le dossier `migrations` du module, vous pouvez utiliser la commande suivante :

```
# Appliquer les migrations d'un module forum sans interactivité
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### Configuration globale des commandes <span id="configuring-command-globally"></span>

Au lieu de répéter les mêmes valeurs d'option à chaque fois que vous exécutez une commande de migration, vous pouvez la configurer une fois pour toute dans la configuration de l'application comme c'est montré ci-après : 

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationTable' => 'backend_migration',
        ],
    ],
];
```

Avec la configuration ci-dessus, à chaque fois que vous exécutez la commande de migration, la table `backend_migration` est utilisée pour enregistrer l'historique de migration. Vous n'avez plus besoin de le spécifier via l'option en ligne de commande `migrationTable`.

### Migrations avec espaces de noms <span id="namespaced-migrations"></span>

Depuis la version 2.0.10, vous pouvez utiliser les espaces de noms pour les classes de migration. Vous pouvez spécifier la liste des espaces de noms des migrations via 
[[yii\console\controllers\MigrateController::migrationNamespaces|migrationNamespaces]]. L'utilisation des espaces de noms pour les classes de migration vous permet l'utilisation de plusieurs emplacement pour les sources des migrations. Par exemple :

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => null, // désactive les migration sans espace de noms si app\migrations est listé ci-dessous
            'migrationNamespaces' => [
                'app\migrations', // Migration ordinaires pour l'ensemble de l'application
                'module\migrations', // Migrations pour le module de projet spécifique
                'some\extension\migrations', // Migrations pour l'extension spécifique 
            ],
        ],
    ],
];
```

> Note : les migrations appliquées appartenant à des espaces de noms différent créent un historique de migration **unique**, p. ex. vous pouvez être incapable d'appliquer ou d'inverser des migrations d'un espace de noms particulier seulement.

Lors des opérations sur les migrations avec espaces de noms : la création, l'inversion, etc. vous devez spécifier l'espace de nom complet avant le nom de la migration.
Notez que le caractère barre oblique inversée (`\`) est en général considéré comme un caractère spécial dans l'interprète de commandes, c'est pourquoi vous devez l'échapper correctement pour éviter des erreurs d'interprète de commandes ou des comportements incorrects. Par exemple :

```
yii migrate/create 'app\\migrations\\createUserTable'
```

> Note : les migrations spécifiées via [[yii\console\controllers\MigrateController::migrationPath|migrationPath]] 
ne peuvent pas contenir un espace de noms, les migrations avec espaces de noms peuvent être appliquée via la propriété [[yii\console\controllers\MigrateController::migrationNamespaces]].

Depuis la version 2.0.12, la propriété [[yii\console\controllers\MigrateController::migrationPath|migrationPath]] 
accepte également un tableau pour spécifier de multiples dossiers contenant des migrations sans espaces de noms.
Cela a été ajouté principalement pour être utilisé dans des projets existants qui utilisent des migrations provenant de différents emplacements. Ces migrations viennent principalement de
sources externes, comme les extensions à Yii développées par d'autres développeurs,
qui ne peuvent être facilement modifiées pour utiliser les espaces de noms lors du démarrage avec la nouvelle approche.


### Migrations séparées <span id="separated-migrations"></span>

Parfois, l'utilisation d'un historique unique de migration pour toutes les migrations du projet n'est pas souhaité. Par exemple : vous pouvez installer une extension 'blog', qui contient des fonctionnalités complètement séparées et contient ses propres migrations, qui ne devraient pas affecter celles dédiées aux fonctionnalités principales du projet.
Si vous voulez qui plusieurs migrations soient appliquées et complétement tracées séparément l'une de l'autre, vous pouvez configurer de multiples commandes de migration qui utilisent des espaces de noms différents et des tables d'historique de migration différentes :


```php
return [
    'controllerMap' => [
        // Common migrations for the whole application
        'migrate-app' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => ['app\migrations'],
            'migrationTable' => 'migration_app',
            'migrationPath' => null,
        ],
        // Migrations for the specific project's module
        'migrate-module' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => ['module\migrations'],
            'migrationTable' => 'migration_module',
            'migrationPath' => null,
        ],
        // Migrations for the specific extension
        'migrate-rbac' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@yii/rbac/migrations',
            'migrationTable' => 'migration_rbac',
        ],
    ],
];
```

Notez que pour synchroniser la base de données vous devez maintenant exécuter plusieurs commandes au lieu d'une seule : 

```
yii migrate-app
yii migrate-module
yii migrate-rbac
```
## Migration de multiples base de données <span id="migrating-multiple-databases"></span>

Par défaut, les migrations sont appliquées à la même base de données spécifiée par le [composant d'application](structure-application-components.md) `db`. Si vous voulez que celles-ci soient appliquées à des bases de données différentes, vous pouvez spécifier l'option en ligne de commande `db` comme indiqué ci-dessous :

```
yii migrate --db=db2
```

La commande ci-dessus applique les migration à la base de données `db2`.

Parfois, il est possible que vous vouliez appliquer *quelques unes* des migrations à une base de données, et *quelques autres* à une autre base de données. Pour y parvenir, lorsque vous implémentez une classe de migration, vous devez spécifier explicitement l'identifiant du composant base de données que la migration doit utiliser, comme ceci : 

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function init()
    {
        $this->db = 'db2';
        parent::init();
    }
}
```

La migration ci-dessus est appliquée à `db2`, même si vous spécifiez une autre base via l'option en ligne de commande `db`. Notez que l'historique de migration est toujours enregistré dans la base de données spécifiée par l'option en ligne de commande `db`.

Si vous avez de multiples migrations qui utilisent la même base de données, il est recommandé que vous créiez une classe de migration de base avec le code `init()` ci-dessus. Ensuite, chaque classe de migration peut étendre cette classe de base.

> Astuce : en plus de définir la propriété [[yii\db\Migration::db|db]], vous pouvez aussi opérer sur différentes bases de données en créant de nouvelles connexions à ces bases de données dans vos classes de migration. Ensuite,vous utilisez les [méthodes des objets d'accès aux bases de données](db-dao.md) avec ces connexions pour manipuler différentes bases de données.

Une autre stratégie que vous pouvez adopter pour appliquer des migrations à de multiples bases de données est de tenir ces migrations de différentes bases de données dans des chemins différents. Ensuite vous pouvez appliquer les migrations à ces bases de données dans des commandes séparées comme ceci :

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

La première commande applique les migrations dans `@app/migrations/db1` à la base de données `db1`, la seconde commande applique les migrations dans `@app/migrations/db2` à `db2`, et ainsi de suite.
