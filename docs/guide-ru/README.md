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
* [Представления](structure-views.md)
* [Модели](structure-models.md)
* [Фильтры](structure-filters.md)
* [Виджеты](structure-widgets.md)
* [Модули](structure-modules.md)
* [Ресурсы](structure-assets.md)
* [Расширения](structure-extensions.md)


Обработка запросов
------------------

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
* **TBD** [Active Record](db-active-record.md) - Получение объектов AR, работа с ними и определение связей.
* [Миграции](db-migrations.md) - Контроль версий схемы данных при работе в команде.
* **TBD** [Sphinx](db-sphinx.md)
* **TBD** [Redis](db-redis.md)
* **TBD** [MongoDB](db-mongodb.md)
* **TBD** [ElasticSearch](db-elasticsearch.md)


Получение данных от пользователя
--------------------------------

* [Создание форм](input-forms.md)
* [Валидация](input-validation.md)
* [Загрузка файлов](input-file-upload.md)
* [Работа с несколькими моделями](input-multiple-models.md)


Отображение данных
------------------

* [Форматирование данных](output-formatting.md)
* [Постраничная разбивка](output-pagination.md)
* [Сортировка](output-sorting.md)
* [Провайдеры данных](output-data-providers.md)
* [Виджеты для данных](output-data-widgets.md)
* [Темизация](output-theming.md)


Безопасность
------------

* [Аутентификация](security-authentication.md)
* [Авторизация](security-authorization.md)
* [Работа с паролями](security-passwords.md)
* **TBD** [Клиенты авторизации](security-auth-clients.md)
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

* **TBD** [Отладочная панель и отладчик](tool-debugger.md)
* **TBD** [Генерация кода с Gii](tool-gii.md)
* **TBD** [Генератор документации API](tool-api-doc.md)


Тестирование
------------

* [Обзор](test-overview.md)
* [Настройка тестового окружения](test-environment-setup.md)
* [Модульные тесты](test-unit.md)
* [Функциональные тесты](test-functional.md)
* [Приёмочные тесты](test-acceptance.md)
* [Фикстуры](test-fixtures.md)


Расширение Yii
--------------

* **TBD** [Создание расширений](extend-creating-extensions.md)
* **TBD** [Расширение кода фреймворка](extend-customizing-core.md)
* **TBD** [Использование сторонних библиотек](extend-using-libs.md)
* **TBD** [Интеграция Yii в сторонние системы](extend-embedding-in-others.md)
* **TBD** [Одновременное использование Yii 1.1 и 2.0](extend-using-v1-v2.md)
* **TBD** [Использование Composer](extend-using-composer.md)


Специальные темы
----------------

* [Шаблон приложения advanced](tutorial-advanced-app.md)
* **TBD** [Создание приложения с нуля](tutorial-start-from-scratch.md)
* [Консольные команды](tutorial-console.md)
* [Интернационализация](tutorial-i18n.md)
* **TBD** [Отправка почты](tutorial-mailing.md)
* [Оптимизация производительности](tutorial-performance-tuning.md)
* [Окружение виртуального хостинга](tutorial-shared-hosting.md)
* **TBD** [Шаблонизаторы](tutorial-template-engines.md)


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
* **TBD** [Виджеты Bootstrap](widget-bootstrap.md)
* **TBD** [Виджеты Jquery UI](widget-jui.md)


Хелперы
-------

* **TBD** [Обзор](helper-overview.md)
* [ArrayHelper](helper-array.md)
* **TBD** [Html](helper-html.md)
* [Url хелпер](helper-url.md)
* **TBD** [Security](helper-security.md)

