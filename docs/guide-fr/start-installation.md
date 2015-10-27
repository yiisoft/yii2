Installer Yii
=============

Vous pouvez installer Yii de deux façons, en utilisant [Composer](https://getcomposer.org/) ou en téléchargeant une archive.
La première méthode est conseillée, étant donné qu'elle permet d'installer de nouvelles [extensions](extend-creating-extensions.md) ou de mettre à jour Yii en éxécutant simplement une commande.

> Remarque : Contrairement à Yii 1, les installations standards de Yii 2 auront pour résultat le téléchargement et l'installation du framework, ainsi que d'un squelette d'application.


Installer via Composer <span id="installing-via-composer"></span>
----------------------

Si vous n'avez pas déjà installé Composer, vous pouvez le faire en suivant les instructions sur le site [getcomposer.org](https://getcomposer.org/download/). 
Sous Linux et Mac OS X, vous pouvez éxécuter les commandes :

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

Sous Windows, téléchargez et éxécutez [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Merci de consulter la [Documentation de Composer](https://getcomposer.org/doc/) en cas de problèmes, ou si vous souhaitez en savoir d'avantage sur l'utilisation de Composer.

Avec Composer installé, vous pouvez installer Yii en éxécutant la commande suivante dans un dossier accessible via le Web :

    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

Cette commande installera Yii dans le dossier `basic`.

> Astuce : Si vous souhaitez installer la dernière version de développement de Yii, vous pouvez utiliser la commande suivante qui ajoutera l'[option stability](https://getcomposer.org/doc/04-schema.md#minimum-stability) :
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Notez que la version de développement de Yii ne doit pas être utilisée en production, vu qu'elle pourrait *casser* votre code existant.


Installer depuis une archive <span id="installing-from-archive-file"></span>
----------------------------

Installer Yii depuis une archive se fait en deux étapes :

1. Téléchargez l'archive sur le site [yiiframework.com](http://www.yiiframework.com/download/yii2-basic).
2. Décompressez l'archive dans un dossier accessible via le Web.


Autres options d'installation <span id="other-installation-options"></span>
-----------------------------

Les instructions d'installation ci-dessus montrent comment installer Yii, ce qui installe également une application Web de base qui fonctionne *out of the box*. 
Cette approche est un bon point de départ pour les petits projets, ou pour quand vous commencer à apprendre Yii. 

Mais il y a d'autres options d'installation disponibles :

* Si vous voulez installer uniquement le framework et que vous souhaitez créer une application à partir de zéro, vous pouvez suivre les instructions dans la partie [Construire une application à partir de zéro](tutorial-start-from-scratch.md).
* Si vous voulez commencer par une application plus sophistiquée, mieux adaptée aux environnements d'équipe de développement, vous pouvez envisager l'installation du [Modèle d'application avancée](tutorial-advanced-app.md).


Vérifier l'installation <span id="verifying-installation"></span>
-----------------------

Après l'installation, vous pouvez utiliser votre navigateur pour accéder à l'application Yii avec l'URL suivante :

```
http://localhost/basic/web/index.php
```

Cette URL suppose que vous avez installé Yii dans un dossier nommé `basic`, directement dans le dossier racine du serveur Web, et que le serveur Web est en cours d'exécution sur votre machine locale (`localhost`). Vous devrez peut-être ajuster  cette URL à votre environnement d'installation.

![Successful Installation of Yii](images/start-app-installed.png)

Vous devriez voir dans votre navigateur la page ci-dessus. Sinon, merci de vérifier que votre installation remplit bien les pré-requis de Yii. Vous pouvez vérifier cela en utilisant l'une des approches suivantes : 

* Utilisez un navigateur pour accéder à l'URL `http://localhost/basic/requirements.php` 
* Exécutez les commandes suivantes:

  ```
  cd basic
  php requirements.php
  ```

Vous devez configurer votre installation de PHP afin qu'elle réponde aux exigences minimales de Yii. Le plus important étant que vous ayez PHP 5.4 ou plus. Si votre application a besoin d'une base de données, vous devez également installer l'[extension PHP PDO](http://www.php.net/manual/en/pdo.installation.php) ainsi qu'un pilote correspondant à votre système de base de données (par exemple `pdo_mysql` pour MySQL).


Configuration du serveur Web <span id="configuring-web-servers"></span>
----------------------------

> Remarque : Si vous voulez juste tester Yii sans intention de l'utiliser sur un serveur de production, vous pouvez ignorer ce paragraphe.

L'application installée selon les instructions ci-dessus devrait fonctionner *out of the box* avec le [serveur HTTP Apache](http://httpd.apache.org/) ou le [serveur HTTP Nginx](http://nginx.org/), sous Windows, Mac OX X, ou linux.

Sur un serveur de production, vous pouvez configurer votre serveur Web afin que l'application soit accessible via l'URL `http://www.example.com/index.php` au lieu de `http://www.example.com/basic/web/index.php`. Cela implique que le dossier racine de votre serveur Web pointe vers le dossier `basic/web`.
Vous pouvez également cacher `index.php` dans l'URL, comme décrit dans la partie [Génération et traitement des URL](runtime-url-handling.md), vous y apprendrez comment configurer votre serveur Apache ou Nginx pour atteindre ces objectifs.

> Remarque : En utilisant `basic/web` comme dossier racine, vous empêchez également aux utilisateurs finaux d'accéder à votre code d'application privé et fichiers de données sensibles qui sont stockés dans le dossier `basic`. Refuser l'accès à ces ressources est une amélioration de la sécurité.

> Remarque: Si votre application s'exécute dans un environnement d'hébergement mutualisé où vous n'avez pas la permission de modifier la configuration du serveur Web, vous pouvez ajuster la structure de votre application pour une meilleure sécurité. Merci de lire la partie [Environnement d'hébergement mutualisé](tutorial-shared-hosting.md) pour en savoir plus.


### Configuration Apache recommandée <span id="recommended-apache-configuration"></span>

Utilisez la configuration suivante dans `httpd.conf`, ou dans la configuration de votre hôte virtuel. Notez que vous devez remplacer `path/to/basic/web` par le chemin vers le dossier `basic/web`.

```
# Configuration du dossier racine
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # Si le dossier ou fichier existe, répondre directement
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Sinon on redirige vers index.php
    RewriteRule . index.php

    # ...other settings...
</Directory>
```


### Configuration Nginx recommandée <span id="recommended-nginx-configuration"></span>

Pour utiliser Nginx, vous devez avoir installé PHP en utilisant [FPM SAPI](http://php.net/install.fpm).
Utilisez la configuration Nginx suivante, en remplaçant `path/to/basic/web` par le chemin vers le dossier `basic/web` et `mysite.local` par le nom d'hôte de votre serveur.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## port pour ipv4
    #listen [::]:80 default_server ipv6only=on; ## port pour ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log main;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Test fichier/dossier, sinon redirection vers index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # décommentez ces lignes pour évitez que Yii ne gère les requêtes vers des fichiers statiques inexistants
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

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
