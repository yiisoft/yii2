Повний посібник до 2.0
======================

Даний посібник випущено відповідно до [положень про документацію Yii](http://www.yiiframework.com/doc/terms/).

All Rights Reserved.

2014 © Yii Software LLC.


Введення
--------

* [Про Yii](intro-yii.md)
* [Оновлення із версії 1.1](intro-upgrade-from-v1.md)


Перше знайомство
----------------

* [Встановлення Yii](start-installation.md)
* [Запуск додатка](start-workflow.md)
* [Говоримо «Привіт»](start-hello.md)
* [Робота з формами](start-forms.md)
* [Робота з базами даних](start-databases.md)
* [Генерація коду за допомогою Gii](start-gii.md)
* [Наступні кроки](start-looking-ahead.md)


Структура додатка
-----------------

* [Огляд](structure-overview.md)
* [Вхідні скрипти](structure-entry-scripts.md)
* [Додатки](structure-applications.md)
* [Компоненти додатка](structure-application-components.md)
* [Контролери](structure-controllers.md)
* [Моделі](structure-models.md)
* [Представлення](structure-views.md)
* [Модулі](structure-modules.md)
* [Фільтри](structure-filters.md)
* [Віджети](structure-widgets.md)
* [Ресурси](structure-assets.md)
* [Розширення](structure-extensions.md)


Обробка запитів
---------------

* [Огляд](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Маршрутизація та створення URL](runtime-routing.md)
* [Запити](runtime-requests.md)
* [Відповіді](runtime-responses.md)
* [Сесії та кукі](runtime-sessions-cookies.md)
* [Обробка помилок](runtime-handling-errors.md)
* [Логування](runtime-logging.md)


Основні поняття
---------------

* [Компоненти](concept-components.md)
* [Властивості](concept-properties.md)
* [Події](concept-events.md)
* [Поведінки](concept-behaviors.md)
* [Конфігурації](concept-configurations.md)
* [Псевдоніми](concept-aliases.md)
* [Автозавантаження класів](concept-autoloading.md)
* [Service Locator](concept-service-locator.md)
* [Dependency Injection Container](concept-di-container.md)


Робота з базами даних
---------------------

* [Обʼєкти доступу до даних (DAO)](db-dao.md) - Зʼєднання з базою даних, прості запити, транзакції і робота зі схемою
* [Конструктор запитів](db-query-builder.md) - Запити до бази даних через простий шар абстракції
* [Active Record](db-active-record.md) - Отримання обʼєктів AR, робота з ними та визначення звʼязків
* [Міграції](db-migrations.md) - Контроль версій схеми даних при роботі в команді
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide/README.md)
* [Redis](https://github.com/yiisoft/yii2-redis/blob/master/docs/guide/README.md)
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide/README.md)


Отримання даних від користувача
-------------------------------

* [Створення форм](input-forms.md)
* [Валідація вводу](input-validation.md)
* [Завантаження файлів](input-file-uploading.md)
* [Збір табличного вводу](input-tabular-input.md)
* [Робота з декількома моделями](input-multiple-models.md)


Відображення даних
------------------

* [Форматування даних](output-formatting.md)
* [Посторінкове розбиття](output-pagination.md)
* [Сортування](output-sorting.md)
* [Провайдери даних](output-data-providers.md)
* [Віджети даних](output-data-widgets.md)
* [Робота з клієнтськими скриптами](output-client-scripts.md)
* [Темізація](output-theming.md)


Безпека
-------

* [Аутентифікація](security-authentication.md)
* [Авторизація](security-authorization.md)
* [Робота з паролями](security-passwords.md)
* [Клієнти авторизації](https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide/README.md)
* [Кращі практики](security-best-practices.md)


Кешування
---------

* [Огляд](caching-overview.md)
* [Кешування даних](caching-data.md)
* [Кешування фрагментів](caching-fragment.md)
* [Кешування сторінок](caching-page.md)
* [HTTP кешування](caching-http.md)


RESTful веб-сервіси
-------------------

* [Швидкий старт](rest-quick-start.md)
* [Ресурси](rest-resources.md)
* [Контролери](rest-controllers.md)
* [Маршрутизація](rest-routing.md)
* [Форматування відповіді](rest-response-formatting.md)
* [Аутентифікація](rest-authentication.md)
* [Обмеження частоти запитів](rest-rate-limiting.md)
* [Версіонування](rest-versioning.md)
* [Обробка помилок](rest-error-handling.md)


Інструменти розробника
----------------------

* [Відладочна панель та відладчик](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
* [Генерація коду з Gii](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide/README.md)
* **TBD** [Генератор документації API](tool-api-doc.md)


Тестування
----------

* [Огляд](test-overview.md)
* [Налаштування середовища тестування](test-environment-setup.md)
* [Модульні тести](test-unit.md)
* [Функціональні тести](test-functional.md)
* [Приймальні тести](test-acceptance.md)
* [Фікстури](test-fixtures.md)


Спеціальні теми
---------------

* [Розширений шаблон додатка](tutorial-advanced-app.md)
* [Створення додатка з нуля](tutorial-start-from-scratch.md)
* [Консольні команди](tutorial-console.md)
* [Основні валідатори](tutorial-core-validators.md)
* [Інтернаціонализація](tutorial-i18n.md)
* [Робота з поштою](tutorial-mailing.md)
* [Вдосконалення продуктивності](tutorial-performance-tuning.md)
* [Робота на shared хостингу](tutorial-shared-hosting.md)
* [Шаблонізатори](tutorial-template-engines.md)
* [Робота із стороннім кодом](tutorial-yii-integration.md)


Віджети
-------

* GridView: **TBD** link to demo page
* ListView: **TBD** link to demo page
* DetailView: **TBD** link to demo page
* ActiveForm: **TBD** link to demo page
* Pjax: **TBD** link to demo page
* Menu: **TBD** link to demo page
* LinkPager: **TBD** link to demo page
* LinkSorter: **TBD** link to demo page
* [Віджети Bootstrap](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide/README.md)
* [Віджети jQuery UI](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/README.md)


Хелпери
-------

* [Огляд](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)
