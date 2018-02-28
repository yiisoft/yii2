Guide définitif pour Yii 2.0
============================

Ce guide est soumis aux [Conditions de la Documentation de Yii](http://www.yiiframework.com/doc/terms/).

Tous droits réservés.

2014 (c) Yii Software LLC.


Introduction
------------

* [A propos de Yii](intro-yii.md)
* [Mise à jour depuis la version 1.1](intro-upgrade-from-v1.md)


Mise en Route
-------------

* [Installer Yii](start-installation.md)
* [Fonctionnement des applications](start-workflow.md)
* [Hello World](start-hello.md)
* [Travailler avec les formulaires](start-forms.md)
* [Travailler avec les bases de données](start-databases.md)
* [Générer du code avec Gii](start-gii.md)
* [En savoir plus](start-looking-ahead.md)


Structure Application
---------------------

* [Vue d'ensemble](structure-overview.md)
* [Scripts d'entrée](structure-entry-scripts.md)
* [Applications](structure-applications.md)
* [Composants application](structure-application-components.md)
* [Contrôleurs](structure-controllers.md)
* [Modèles](structure-models.md)
* [Vues](structure-views.md)
* **TBD** [Filtres](structure-filters.md)
* **TBD** [Widgets](structure-widgets.md)
* **TBD** [Modules](structure-modules.md)
* [Assets](structure-assets.md)
* **TBD** [Extensions](structure-extensions.md)


Gérer les Requêtes
------------------

* **TBD** [Amorçage (Bootstrapping)](runtime-bootstrapping.md)
* **TBD** [Routes](runtime-routing.md)
* **TBD** [Requêtes](runtime-requests.md)
* **TBD** [Réponses](runtime-responses.md)
* **TBD** [Sessions et Cookies](runtime-sessions-cookies.md)
* [Génération et traitement des URL](runtime-url-handling.md)
* [Gestion des erreurs](runtime-handling-errors.md)
* [Journalisation](runtime-logging.md)


Concepts Clés
-------------

* [Composants](concept-components.md)
* [Propriétés](concept-properties.md)
* [Evénements](concept-events.md)
* [Comportements](concept-behaviors.md)
* [Configurations](concept-configurations.md)
* [Alias](concept-aliases.md)
* [Auto-chargement de classes](concept-autoloading.md)
* [Annuaire de services](concept-service-locator.md)
* [Conteneur d'injection de dépendance](concept-di-container.md)


Travailler avec les Bases de Données
------------------------------------

* [Objet d'accès aux données (DAO)](db-dao.md) - Connexion à une base de données, requêtes basiques, transactions et manipulation de schéma
* [Constructeur de requête](db-query-builder.md) - Interrogation de base de données en utilisant une couche d'abstraction simple
* [Active Record](db-active-record.md) - Active Record ORM, récupération et manipulation d'enregistrements et définition des relations
* [Migrations](db-migrations.md) - Contrôle de version de vos bases de données dans un environnement de développement en équipe
* **TBD** [Sphinx](db-sphinx.md)
* **TBD** [Redis](db-redis.md)
* **TBD** [MongoDB](db-mongodb.md)
* **TBD** [ElasticSearch](db-elastic-search.md)


Getting Data from Users
-----------------------

* [Créer des formulaires](input-forms.md)
* [Valider les entrées](input-validation.md)
* **TBD** [Télécharger des fichiers](input-file-upload.md)
* **TBD** [Récupération de données provenant de plusieurs modèles](input-multiple-models.md)


Afficher les données
--------------------

* **TBD** [Formattage](output-formatting.md)
* **TBD** [Pagination](output-pagination.md)
* **TBD** [Tri](output-sorting.md)
* [Fournisseurs de données](output-data-providers.md)
* [Widgets pour afficher des données](output-data-widgets.md)
* [Thématisation](output-theming.md)


Securité
--------

* [Authentification](security-authentication.md)
* [Autorisation](security-authorization.md)
* [Gestion des mots de passe](security-passwords.md)
* **TBD** [Clients authentification](security-auth-clients.md)
* **TBD** [Meilleures pratiques](security-best-practices.md)


Cache
-----

* [Vue d'ensemble](caching-overview.md)
* [Cache de données](caching-data.md)
* [Cache de fragment](caching-fragment.md)
* [Cache de page](caching-page.md)
* [Cache HTTP](caching-http.md)


Outils de développement
-----------------------

* [Barre de débogage, et débogueur](tool-debugger.md)
* [Générer du code avec Gii](tool-gii.md)
* **TBD** [Générer une documentation API](tool-api-doc.md)


Tests
-----

* [Vue d'ensemble](test-overview.md)
* **TBD** [Tests unitaires](test-unit.md)
* **TBD** [tests fonctionnels](test-functional.md)
* **TBD** [Tests d'acceptation](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Etendre Yii
-----------

* [Créer des extensions](extend-creating-extensions.md)
* [Personnalisation du code du noyau](extend-customizing-core.md)
* [Utiliser des libraires tierces](extend-using-libs.md)
* **TBD** [Utiliser Yii dans d'autres systèmes](extend-embedding-in-others.md)
* **TBD** [Utiliser Yii 1.1 et 2.0 ensemble](extend-using-v1-v2.md)
* [Utiliser Composer](extend-using-composer.md)


Sujets avancés
--------------

* [Modèle application avancée](tutorial-advanced-app.md)
* [Créer une application à partir de zéro](tutorial-start-from-scratch.md)
* [Commandes console](tutorial-console.md)
* [Validateurs de base](tutorial-core-validators.md)
* [Internationalisation](tutorial-i18n.md)
* [Envoyer des courriels](tutorial-mailing.md)
* [Amélioration des performances](tutorial-performance-tuning.md)
* **TBD** [Environnement d'hébergement mutualisé](tutorial-shared-hosting.md)
* [Moteur de gabarit](tutorial-template-engines.md)


Widgets
-------

* GridView: link to demo page
* ListView: link to demo page
* DetailView: link to demo page
* ActiveForm: link to demo page
* Pjax: link to demo page
* Menu: link to demo page
* LinkPager: link to demo page
* LinkSorter: link to demo page
* [Widgets Bootstrap](bootstrap-widgets.md)
* **TBD** [Widgets Jquery UI](jui-widgets.md)


Assistants
----------

* [Vue d'ensemble](helper-overview.md)
* **TBD** [ArrayHelper](helper-array.md)
* **TBD** [Html](helper-html.md)
* **TBD** [Url](helper-url.md)
* **TBD** [Security](helper-security.md)

