Installer Yii
=============

You pouvez installer Yii de deux façons, en utilisant [Composer](http://getcomposer.org/) ou en téléchargeant une archive.
La première méthode est conseillée, étant donné qu'elle permet d'installer de nouvelles [extensions](extend-creating-extensions.md) ou de mettre à jour Yii en éxécutant simplement une commande.

> Remarque : Contrairement à Yii 1, les installations standards de Yii 2 auront pour résultat le téléchargement et l'installation du framework, ainsi que d'un squelette d'application.


Installer via Composer <a name="installing-via-composer"></a>
----------------------

Si vous n'avez pas déjà installé Composer, vous pouvez le faire en suivant les instructions sur le site [getcomposer.org](https://getcomposer.org/download/). 
Sous Linux et Mac OS X, vous pouvez éxécuter les commandes :

    curl -s http://getcomposer.org/installer | php
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


Installer depuis une archive <a name="installing-from-archive-file"></a>
----------------------------

Installer Yii depuis une archive se fait en deux étapes :

1. Téléchargez l'archive sur le site [yiiframework.com](http://www.yiiframework.com/download/yii2-basic).
2. Décompressez l'archive dans un dossier accessible via le Web.


Autres options d'installation <a name="other-installation-options"></a>
-----------------------------

Les instructions d'installation ci-dessus montrent comment installer Yii, ce qui installe également une application Web de base qui fonctionne *out of the box*. 
Cette approche est un bon point de départ pour les petits projets, ou pour quand vous commencer à apprendre Yii. 

Mais il ya d'autres options d'installation disponibles : 

* Si vous ne voulez installer que le framework et que vous souhaitez créer une application à partir de zéro,
  vous pouvez suivre les instructions dans la partie [Construire une application à partir de zéro](tutorial-start-from-scratch.md).
* Si vous voulez commencer par une application plus sophistiquée, mieux adaptée aux environnements d'équipe de développement,
  vous pouvez envisager l'installation du [Modèle d'application avancée](tutorial-advanced-app.md).
