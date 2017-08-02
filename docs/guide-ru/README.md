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
* [Модули](structure-modules.md)
* [Фильтры](structure-filters.md)
* [Виджеты](structure-widgets.md)
* [Ресурсы](structure-assets.md)
* [Расширения](structure-extensions.md)


Обработка запросов
------------------

* [Обзор](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Разбор и генерация URL](runtime-routing.md)
* [Запросы](runtime-requests.md)
* [Ответы](runtime-responses.md)
* [Сессии и куки](runtime-sessions-cookies.md)
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

* [Объекты доступа к данным (DAO)](db-dao.md) - Соединение с базой данных, простые запросы, транзакции и работа со схемой.
* [Построитель запросов](db-query-builder.md) - Запросы к базе данных через простой слой абстракции.
* [Active Record](db-active-record.md) - Получение объектов AR, работа с ними и определение связей.
* [Миграции](db-migrations.md) - Контроль версий схемы данных при работе в команде.
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide-ru/README.md)
* [Redis](https://github.com/yiisoft/yii2-redis/blob/master/docs/guide/README.md)
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide-ru/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide/README.md)


Получение данных от пользователя
--------------------------------

* [Создание форм](input-forms.md)
* [Валидация](input-validation.md)
* [Загрузка файлов](input-file-upload.md)
* [Табличный ввод](input-tabular-input.md)
* [Работа с несколькими моделями](input-multiple-models.md)


Отображение данных
------------------

* [Форматирование данных](output-formatting.md)
* [Постраничная разбивка](output-pagination.md)
* [Сортировка](output-sorting.md)
* [Провайдеры данных](output-data-providers.md)
* [Виджеты для данных](output-data-widgets.md)
* Работа с клиентскими скриптами
* [Темизация](output-theming.md)


Безопасность
------------

* [Обзор](security-overview.md)
* [Аутентификация](security-authentication.md)
* [Авторизация](security-authorization.md)
* [Работа с паролями](security-passwords.md)
* [Криптография](security-cryptography.md)
* [Клиенты авторизации](https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide-ru/README.md)
* [Лучшие практики](security-best-practices.md)


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
* [Генератор документации API](https://github.com/yiisoft/yii2-apidoc)


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

* [GridView](http://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](http://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](http://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](http://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](http://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](http://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](http://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](http://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Виджеты Bootstrap](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide-ru/README.md)
* [Виджеты Jquery UI](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/README.md)


Хелперы
-------

* [Обзор](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url хелпер](helper-url.md)
