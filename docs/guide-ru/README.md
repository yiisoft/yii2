Полное руководство по Yii 2.0
=============================

Данное руководство выпущено в соответствии с [положениями о документации Yii](http://www.yiiframework.com/doc/terms/).

All Rights Reserved.

2014 © Yii Software LLC.


Введение
--------

* [О Yii](intro-yii.md)
* [Обновление с версии 1.1](intro-upgrade-from-v1.md)


Первое знакомство
-----------------

* [Установка Yii](start-installation.md)
* [Запуск приложения](start-workflow.md)
* [Говорим «привет»](start-hello.md)
* [Работа с формами](start-forms.md)
* [Работа с базами данных](start-databases.md)
* [Генерация кода при помощи Gii](start-gii.md)
* [Что дальше?](start-looking-ahead.md)


Структура приложения
--------------------

* [Обзор](structure-overview.md)
* [Входные скрипты](structure-entry-scripts.md)
* [Приложения](structure-applications.md)
* [Компоненты приложения](structure-application-components.md)
* [Контроллеры](structure-controllers.md)
* [Модели](structure-models.md)
* [Представления](structure-views.md)
<<<<<<< HEAD
<<<<<<< HEAD
* **TBD** [Модели](structure-models.md)
* [Фильтры](structure-filters.md)
* [Виджеты](structure-widgets.md)
* [Модули](structure-modules.md)
* **TBD** [Ресурсы](structure-assets.md)
=======
* [Модели](structure-models.md)
* [Фильтры](structure-filters.md)
* [Виджеты](structure-widgets.md)
* [Модули](structure-modules.md)
* [Ресурсы](structure-assets.md)
>>>>>>> yiichina/master
=======
* [Модули](structure-modules.md)
* [Фильтры](structure-filters.md)
* [Виджеты](structure-widgets.md)
* [Ресурсы](structure-assets.md)
>>>>>>> master
* [Расширения](structure-extensions.md)


Обработка запросов
------------------

* [Обзор](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Разбор и генерация URL](runtime-routing.md)
* [Запросы](runtime-requests.md)
* [Ответы](runtime-responses.md)
<<<<<<< HEAD
<<<<<<< HEAD
* **TBD** [Сессии и куки](runtime-sessions-cookies.md)
=======
* [Сессии и куки](runtime-sessions-cookies.md)
>>>>>>> yiichina/master
* [Разбор и генерация URL](runtime-routing.md)
=======
* [Сессии и куки](runtime-sessions-cookies.md)
>>>>>>> master
* [Обработка ошибок](runtime-handling-errors.md)
* [Логирование](runtime-logging.md)


Основные понятия
----------------

* [Компоненты](concept-components.md)
* [Свойства](concept-properties.md)
* [События](concept-events.md)
* [Поведения](concept-behaviors.md)
* [Конфигурации](concept-configurations.md)
* [Псевдонимы](concept-aliases.md)
* [Автозагрузка классов](concept-autoloading.md)
* [Service Locator](concept-service-locator.md)
* [Dependency Injection Container](concept-di-container.md)


Работа с базами данных
----------------------

<<<<<<< HEAD
* **TBD** [Объекты доступа к данным (DAO)](db-dao.md) - Соединение с базой данных, простые запросы, транзакции и работа со схемой.
* **TBD** [Построитель запросов](db-query-builder.md) - Запросы к базе данных через простой слой абстракции.
* **TBD** [Active Record](db-active-record.md) - Получение объектов AR, работа с ними и определение связей.
<<<<<<< HEAD
* **TBD** [Миграции](db-migrations.md) - Контроль версий схемы данных при работе в команде.
=======
* [Миграции](db-migrations.md) - Контроль версий схемы данных при работе в команде.
>>>>>>> yiichina/master
* **TBD** [Sphinx](db-sphinx.md)
* **TBD** [Redis](db-redis.md)
* **TBD** [MongoDB](db-mongodb.md)
* **TBD** [ElasticSearch](db-elasticsearch.md)
=======
* [Объекты доступа к данным (DAO)](db-dao.md) - Соединение с базой данных, простые запросы, транзакции и работа со схемой.
* [Построитель запросов](db-query-builder.md) - Запросы к базе данных через простой слой абстракции.
* [Active Record](db-active-record.md) - Получение объектов AR, работа с ними и определение связей.
* [Миграции](db-migrations.md) - Контроль версий схемы данных при работе в команде.
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide/README.md)
* [Redis](https://github.com/yiisoft/yii2-redis/blob/master/docs/guide/README.md)
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide/README.md)
>>>>>>> master


Получение данных от пользователя
--------------------------------

* [Создание форм](input-forms.md)
* [Валидация](input-validation.md)
<<<<<<< HEAD
<<<<<<< HEAD
* **TBD** [Загрузка файлов](input-file-upload.md)
=======
* [Загрузка файлов](input-file-upload.md)
>>>>>>> yiichina/master
* **TBD** [Работа с несколькими моделями](input-multiple-models.md)
=======
* [Загрузка файлов](input-file-upload.md)
* [Табличный ввод](input-tabular-input.md)
* [Работа с несколькими моделями](input-multiple-models.md)
>>>>>>> master


Отображение данных
------------------

<<<<<<< HEAD
<<<<<<< HEAD
* **TBD** [Форматирование данных](output-formatter.md)
*  [Постраничная разбивка](output-pagination.md)
* [Сортировка](output-sorting.md)
* **TBD** [Провайдеры данных](output-data-providers.md)
* **TBD** [Виджеты для данных](output-data-widgets.md)
* **TBD** [Темизация](output-theming.md)
=======
* [Форматирование данных](output-formatting.md)
* [Постраничная разбивка](output-pagination.md)
* [Сортировка](output-sorting.md)
* **TBD** [Провайдеры данных](output-data-providers.md)
* **TBD** [Виджеты для данных](output-data-widgets.md)
* [Темизация](output-theming.md)
>>>>>>> yiichina/master
=======
* [Форматирование данных](output-formatting.md)
* [Постраничная разбивка](output-pagination.md)
* [Сортировка](output-sorting.md)
* [Провайдеры данных](output-data-providers.md)
* [Виджеты для данных](output-data-widgets.md)
* [Работа с клиентскими скриптами](output-client-scripts.md)
* [Темизация](output-theming.md)
>>>>>>> master


Безопасность
------------

<<<<<<< HEAD
<<<<<<< HEAD
* **TBD** [Аутентификация](security-authentication.md)
=======
* [Аутентификация](security-authentication.md)
>>>>>>> yiichina/master
* **TBD** [Авторизация](security-authorization.md)
* **TBD** [Работа с паролями](security-passwords.md)
* **TBD** [Клиенты авторизации](security-auth-clients.md)
* **TBD** [Лучшие практики](security-best-practices.md)
=======
* [Аутентификация](security-authentication.md)
* [Авторизация](security-authorization.md)
* [Работа с паролями](security-passwords.md)
* [Клиенты авторизации](https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide-ru/README.md)
* [Лучшие практики](security-best-practices.md)
>>>>>>> master


Кеширование
-----------

* [Обзор](caching-overview.md)
* [Кэширование данных](caching-data.md)
* [Кэширование фрагментов](caching-fragment.md)
* [Кэширование страниц](caching-page.md)
* [HTTP кэширование](caching-http.md)


Веб-сервисы REST
----------------

* [Быстрый старт](rest-quick-start.md)
* [Ресурсы](rest-resources.md)
* [Контроллеры](rest-controllers.md)
* [Роутинг](rest-routing.md)
* [Форматирование ответа](rest-response-formatting.md)
* [Аутентификация](rest-authentication.md)
* [Ограничение частоты запросов](rest-rate-limiting.md)
* [Версионирование](rest-versioning.md)
* [Обработка ошибок](rest-error-handling.md)


Инструменты разработчика
------------------------

* [Отладочная панель и отладчик](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
* [Генерация кода с Gii](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide/README.md)
* **TBD** [Генератор документации API](https://github.com/yiisoft/yii2-apidoc)


Тестирование
------------

* [Обзор](test-overview.md)
* [Настройка тестового окружения](test-environment-setup.md)
* [Модульные тесты](test-unit.md)
* [Функциональные тесты](test-functional.md)
* [Приёмочные тесты](test-acceptance.md)
* [Фикстуры](test-fixtures.md)


Специальные темы
----------------


* [Шаблон приложения advanced](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md)
* [Создание приложения с нуля](tutorial-start-from-scratch.md)
* [Консольные команды](tutorial-console.md)
* [Встроенные валидаторы](tutorial-core-validators.md)
* [Интернационализация](tutorial-i18n.md)
* [Отправка почты](tutorial-mailing.md)
* [Оптимизация производительности](tutorial-performance-tuning.md)
* [Окружение виртуального хостинга](tutorial-shared-hosting.md)
* [Шаблонизаторы](tutorial-template-engines.md)
* [Работа со сторонним кодом](tutorial-yii-integration.md)


Виджеты
-------

* GridView: link to demo page
* ListView: link to demo page
* DetailView: link to demo page
* ActiveForm: link to demo page
* Pjax: link to demo page
* Menu: link to demo page
* LinkPager: link to demo page
* LinkSorter: link to demo page
* [Виджеты Bootstrap](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide/README.md)
* [Виджеты Jquery UI](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/README.md)


Хелперы
-------

* [Обзор](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url хелпер](helper-url.md)
