Installer Yii
=============

Vous pouvez installer Yii de deux façons, en utilisant le gestionnaire de paquets [Composer](https://getcomposer.org/) ou en téléchargeant une archive.
La première méthode est conseillée, étant donné qu'elle permet d'installer de nouvelles [extensions](extend-creating-extensions.md) ou de mettre Yii à jour en exécutant simplement une commande.

Les installations standard de Yii provoquent le téléchargement et l'installation d'un modèle de projet. Un modèle de projet et un projet Yii fonctionnel qui met en œuvre quelques fonctionnalités de base, telles que la connexion, le formulaire de contact, etc.
Son code est organisé de la façon recommandée. En conséquence, c'est un bon point de départ pour vos propres projets.

Dans cette section et quelques-unes de ses suivantes, nous décrirons comment installer Yii avec le modèle baptisé *Basic Project Template* (modèle de projet de base) et comment mettre en œuvre de nouvelles fonctionnalités sur cette base. Yii vous offre également un autre modèle de projet appelé [Advanced Project Template ](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide) (modèle de projet avancé) qui convient mieux à un environnement de développement en équipe impliquant des tiers multiples. 

> Note: le modèle de projet de base conviendra à 90 pourcent des application Web. Il diffère du modèle de projet avancé essentiellement sur la manière dont le code est organisé. Si vous débutez avec Yii, nous vous conseillons fortement de vous en tenir au modèle de projet de base pour sa simplicité tout en disposant des fonctionnalités suffisantes.


Installer via Composer <span id="installing-via-composer"></span>
----------------------

### Installer Composer

Si vous n'avez pas déjà installé Composer, vous pouvez le faire en suivant les instructions du site [getcomposer.org](https://getcomposer.org/download/). 
Sous Linux et Mac OS X, vous pouvez exécuter les commandes :

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Sous Windows, téléchargez et exécutez [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

En cas de problèmes, consultez la [section Troubleshooting (résolution des problèmes) de la documentation de Composer](https://getcomposer.org/doc/articles/troubleshooting.md),

Si vous débutez avec Composer, nous vous recommandons au minimum la lecture de la section [Basic usage (utilisation de base)](https://getcomposer.org/doc/01-basic-usage.md) de la documentation de Composer

Dans ce guide, toutes les commandes de Composer suppose que vous avez installé Composer [globalement](https://getcomposer.org/doc/00-intro.md#globally) et qu'il est disponible par la commande `composer`. Si, au lieu de cela, vous utilisez `composer.phar` depuis un dossier local, vous devez adapter les exemples fournis en conséquence.

Si Composer était déjà installé auparavant, assurez-vous d'utiliser une version à jour. Vous pouvez mettre Composer à jour avec la commande `composer self-update`.

> Note: durant l'installation de Yii, Composer aura besoin d'obtenir de nombreuses informations de l'API de Github. Le nombre de requêtes dépend du nombre de dépendances de votre application et peut excéder la **Github API rate limit**. Si vous arrivez à cette limite, Composer peut vous demander vos identifiants de connexion pour obtenir un jeton d'accès à l'API de Github. Avec une connexion rapide, vous pouvez atteindre cette limite plus vite que Composer n'est capable de gérer. C'est pourquoi, nous vous recommandons de configurer ce jeton d'accès avant d'installer Yii.
> Reportez-vous à la [documentation de Composer sur les jetons de l'API Github](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens)
> pour savoir comment procéder.

### Installer Yii <span id="installing-from-composer"></span>

Avec Composer installé, vous pouvez installer le modèle de projet Yii en exécutant la commande suivante dans un dossier accessible via le Web :

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

Cette commande installera la dernière version stable du modèle de projet Yii dans le dossier `basic`. Vous êtes libre de choisir un autre dossier si vous le désirez.

> Note: si la commande `composer create-project` échoue, reportez-vous à la section 
> [Troubleshooting (résolution des problèmes) de la documentation de Composer](https://getcomposer.org/doc/articles/troubleshooting.md)
> pour les erreurs communes. Une fois l'erreur corrigée, vous pouvez reprendre l'installation avortée en exécutant `composer update` dans le dossier  `basic` (ou celui que vous aviez choisi).

> Tip: si vous souhaitez installer la dernière version de développement de Yii, vous pouvez utiliser la commande suivante qui ajoutera l'[option stability](https://getcomposer.org/doc/04-schema.md#minimum-stability) :
>
>```bash
>composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>```
>
> Notez que la version de développement de Yii ne doit pas être utilisée en production, vu qu'elle pourrait *casser* votre code existant.


Installer depuis une archive <span id="installing-from-archive-file"></span>
----------------------------

Installer Yii depuis une archive se fait en trois étapes :

1. Télécharger l'archive sur le site [yiiframework.com](https://www.yiiframework.com/download/).
2. Décompresser l'archive dans un dossier accessible via le Web.
3. Modifier le fichier `config/web.php` en entrant une clé secrète pour la configuration de `cookieValidationKey` (cela est fait automatiquement si vous installez Yii avec Composer) :

 ```php
 // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
 'cookieValidationKey' => 'enter your secret key here',
 ```


Autres options d'installation <span id="other-installation-options"></span>
-----------------------------

Les instructions d'installation ci-dessus montrent comment installer Yii, ce qui installe également une application Web de base qui fonctionne *out of the box* (sans configuration supplémentaire). 
Cette approche est un bon point de départ pour les petits projets, en particulier si vous débutez avec Yii. 

Mais il y a d'autres options d'installation disponibles :

* Si vous voulez installer uniquement le framework et que vous souhaitez créer une application à partir de zéro, vous pouvez suivre les instructions dans la partie [Créer votre propre structure d'application](tutorial-start-from-scratch.md).
* Si vous voulez commencer par une application plus sophistiquée, mieux adaptée aux environnements d'équipe de développement, vous pouvez envisager l'installation du [Modèle d'application avancée](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md).

Installer les Assets (ici bibliothèques CSS et JavaScript) <span id="installing-assets"></span>
-----------------

Yii s'appuie sur les paquets [Bower](https://bower.io/) et/ou [NPM](https://www.npmjs.com/) pour l'installation des bibliothèques CSS et JavaScript.

Il utilise Composer pour les obtenir, permettant ainsi aux versions de paquet de PHP et à celles de CSS/JavaScript, d'être résolues en même temps.
Cela peut être obtenue soit en utilisant [asset-packagist.org](https://asset-packagist.org) ou [composer asset plugin](https://github.com/fxpio/composer-asset-plugin).

Reportez-vous à la documentation sur les  [Assets](structure-assets.md) pour plus de détail.

Vous pouvez souhaiter gérer vos « assets », soit via le client natif Bower/NPM, soit via CDN, soit éviter totalement leur installation. 
Afin d'empêcher l'installation des « assets » via Composer, ajoutez les lignes suivantes à votre fichier 'composer.json' :

```json
"replace": {
    "bower-asset/jquery": ">=1.11.0",
    "bower-asset/inputmask": ">=3.2.0",
    "bower-asset/punycode": ">=1.3.0",
    "bower-asset/yii2-pjax": ">=2.0.0"
},
```

> Note: en cas de neutralisation de l'installation des « assets » via Composer, c'est à vous d'en assurer l'installation et de résoudre les problèmes de collision de versions. Attendez-vous à des incohérences possibles parmi les fichiers d'assets issus de vos différentes extensions.

Vérifier l'installation <span id="verifying-installation"></span>
-----------------------

Après l'installation, vous pouvez, soit configurer votre serveur Web (voir section suivante), soit utiliser le [serveur PHP web incorporé](https://www.php.net/manual/fr/features.commandline.webserver.php) en utilisant la commande en console suivante depuis le dossier racine de votre projet :
 
```bash
php yii serve
```

> Note: par défaut le serveur HTTP écoute le port 8080. Néanmoins, si ce port est déjà utilisé ou si vous voulez servir plusieurs applications de cette manière, vous pouvez spécifier le port à utiliser en ajoutant l'argument --port à la commande : 

```bash
php yii serve --port=8888
```
Pour accéder à l'application Yii pointez votre navigateur sur l'URL suivante :

```
http://localhost:8080/
```


![Successful Installation of Yii](images/start-app-installed.png)

Vous devriez voir dans votre navigateur la page ci-dessus. Sinon, merci de vérifier que votre installation remplit bien les pré-requis de Yii. Vous pouvez vérifier cela en utilisant l'une des approches suivantes : 

* Utilisez un navigateur pour accéder à l'URL `http://localhost/basic/requirements.php` 
* Exécutez les commandes suivantes:

  ```bash
  cd basic
  php requirements.php
  ```

Vous devez configurer votre installation de PHP afin qu'elle réponde aux exigences minimales de Yii. Le plus important étant que vous ayez PHP 5.4 ou plus, idéalement PHP 7. Si votre application a besoin d'une base de données, vous devez également installer l'[extension PHP PDO](https://www.php.net/manual/fr/pdo.installation.php) ainsi qu'un pilote correspondant à votre système de base de données (par exemple `pdo_mysql` pour MySQL).


Configuration du serveur Web <span id="configuring-web-servers"></span>
----------------------------

> Note: si vous voulez juste tester Yii sans intention de l'utiliser sur un serveur de production, vous pouvez ignorer ce paragraphe.

L'application installée selon les instructions ci-dessus devrait fonctionner *out of the box* (sans configuration supplémentaire) avec le [serveur HTTP Apache](https://httpd.apache.org/) ou le [serveur HTTP Nginx](https://nginx.org/), sous Windows, Mac OX X, ou Linux avec PHP 5.4 ou plus récent. Yii 2.0 est aussi compatible avec 
[HHVM](https://hhvm.com/) de Facebook. Cependant, il existe des cas marginaux pour lesquels HHVM se comporte différemment du PHP natif; c'est pourquoi vous devez faire plus attention en utilisant HHVM.. 

Sur un serveur de production, vous pouvez configurer votre serveur Web afin que l'application soit accessible via l'URL `https://www.example.com/index.php` au lieu de `https://www.example.com/basic/web/index.php`. Cela implique que le dossier racine de votre serveur Web pointe vers le dossier `basic/web`.
Vous pouvez également cacher `index.php` dans l'URL, comme décrit dans la partie [Génération et traitement des URL](runtime-url-handling.md), vous y apprendrez comment configurer votre serveur Apache ou Nginx pour atteindre ces objectifs.

> Note: en utilisant `basic/web` comme dossier racine, vous empêchez également aux utilisateurs finaux d'accéder à votre code d'application privé et fichiers de données sensibles qui sont stockés dans le dossier `basic`. Refuser l'accès à ces ressources est une amélioration de la sécurité.

> Note: si votre application s'exécute dans un environnement d'hébergement mutualisé où vous n'avez pas la permission de modifier la configuration du serveur Web, vous pouvez ajuster la structure de votre application pour une meilleure sécurité. Reportez-vous à la partie [Environnement d'hébergement mutualisé](tutorial-shared-hosting.md) pour en savoir plus.

> Note: si vous exécutez votre application Yii derrière un mandataire inverse, vous pourriez avoir besoin de configurer les
> [mandataires de confiance et entêtes](runtime-requests.md#trusted-proxies) dans le composant « request ».

### Configuration Apache recommandée <span id="recommended-apache-configuration"></span>

Utilisez la configuration suivante dans le fichier `httpd.conf`, ou dans la configuration de votre hôte virtuel. Notez que vous devez remplacer `path/to/basic/web` par le chemin vers le dossier `basic/web`.

```apache
# Configuration du dossier racine
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # utiliser mod_rewrite pour la prise en charge des URL élégantes ("pretty URL")
    RewriteEngine on

    # Si le dossier ou fichier existe, répondre directement
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Sinon on redirige vers index.php
    RewriteRule . index.php

    # si $showScriptName est à "false" dans UrlManager, ne pas autoriser l'accès aux URL incluant le nom du script
    RewriteRule ^index.php/ - [L,R=404]

    # ...other settings...
</Directory>
```


### Configuration Nginx recommandée <span id="recommended-nginx-configuration"></span>

Pour utiliser Nginx, vous devez avoir installé PHP en utilisant [FPM SAPI](https://www.php.net/manual/fr/install.fpm.php).
Utilisez la configuration Nginx suivante, en remplaçant `path/to/basic/web` par le chemin vers le dossier `basic/web` et `mysite.test` par le nom d'hôte de votre serveur.

```nginx
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Rediriger tout ce qui n'est pas un fichier réel index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # enlevez les commentaires de ces lignes pour évitez que Yii ne gère les requêtes vers des fichiers statiques inexistants
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;


    # refuser l'accès aux fichiers php pour le dossier /assets 
    location ~ ^/assets/.*\.php$ {
        deny all;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Lorsque vous utilisez cette configuration, vous devez aussi mettre l'option `cgi.fix_pathinfo=0` dans le fichier `php.ini` afin d'éviter de nombreux appels système à `stat()`.

Notez également que lors de l'utilisation d'un serveur HTTPS, vous devez ajouter l'option `fastcgi_param HTTPS on;` afin que Yii puisse détecter correctement si une connexion est sécurisée.
