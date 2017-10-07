Przewodnik po Yii 2.0
=====================

Ten poradnik udostępniony jest na [Warunkach dokumentacji Yii](http://www.yiiframework.com/doc/terms/).

Wszelkie prawa zastrzeżone.

2014 (c) Yii Software LLC.


Wstęp
-----

* [O Yii](intro-yii.md)
* [Aktualizacja z wersji 1.1](intro-upgrade-from-v1.md)


Pierwsze kroki
--------------

* [Instalacja Yii](start-installation.md)
* [Uruchamianie aplikacji](start-workflow.md)
* [Witaj świecie](start-hello.md)
* [Praca z formularzami](start-forms.md)
* [Praca z bazami danych](start-databases.md)
* [Generowanie kodu za pomocą Gii](start-gii.md)
* [Dalsze kroki](start-looking-ahead.md)


Struktura aplikacji
-------------------

* [Przegląd](structure-overview.md)
* [Skrypty wejściowe](structure-entry-scripts.md)
* [Aplikacje](structure-applications.md)
* [Komponenty aplikacji](structure-application-components.md)
* [Kontrolery](structure-controllers.md)
* [Modele](structure-models.md)
* [Widoki](structure-views.md)
* [Moduły](structure-modules.md)
* [Filtry](structure-filters.md)
* [Widżety](structure-widgets.md)
* [Assety (Assets)](structure-assets.md)
* [Rozszerzenia](structure-extensions.md)


Obsługa żądań
-------------

* [Przegląd](runtime-overview.md)
* [Bootstrapping](runtime-bootstrapping.md)
* [Parsowanie i generowanie adresów URL](runtime-routing.md)
* [Żądania](runtime-requests.md)
* [Odpowiedzi](runtime-responses.md)
* [Sesje i ciasteczka](runtime-sessions-cookies.md)
* [Obsługa błędów](runtime-handling-errors.md)
* [Logowanie](runtime-logging.md)


Kluczowe koncepcje
------------------

* [Komponenty](concept-components.md)
* [Właściwości](concept-properties.md)
* [Eventy](concept-events.md)
* [Behaviory](concept-behaviors.md)
* [Konfiguracje](concept-configurations.md)
* [Aliasy](concept-aliases.md)
* [Autoładowanie klas](concept-autoloading.md)
* [Lokator usług](concept-service-locator.md)
* [Kontener wstrzykiwania zależności (DI Container)](concept-di-container.md)


Praca z bazami danych
---------------------

* [Obiekty dostępu do danych (DAO)](db-dao.md): Łączenie z bazą, podstawowe zapytania, transakcje i manipulacja schematem.
* [Konstruktor kwerend](db-query-builder.md): Zapytania do bazy danych z użyciem warstwy abstrakcyjnej.
* [Active Record](db-active-record.md): Active Record ORM, otrzymywanie i manipulacja rekordami oraz definiowanie relacji.
* [Migracje](db-migrations.md): Użycie systemu kontroli wersji bazy danych do pracy z wieloma środowiskami.
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide/README.md)
* [Redis](https://github.com/yiisoft/yii2-redis/blob/master/docs/guide/README.md)
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide/README.md)


Odbieranie danych od użytkowników
---------------------------------

* [Tworzenie formularzy](input-forms.md)
* [Walidacja danych wejściowych](input-validation.md)
* [Wysyłanie plików](input-file-upload.md)
* [Odczytywanie tablicowych danych wejściowych](input-tabular-input.md)
* [Pobieranie danych dla wielu modeli](input-multiple-models.md)


Wyświetlanie danych
-------------------

* [Formatowanie danych](output-formatting.md)
* [Stronicowanie](output-pagination.md)
* [Sortowanie](output-sorting.md)
* [Dostawcy danych](output-data-providers.md)
* [Widżety danych](output-data-widgets.md)
* [Praca ze skryptami](output-client-scripts.md)
* [Skórki i motywy (Theming)](output-theming.md)


Bezpieczeństwo
--------------

* [Omówienie](security-overview.md)
* [Uwierzytelnianie](security-authentication.md)
* [Autoryzacja](security-authorization.md)
* [Praca z hasłami](security-passwords.md)
* [Kryptografia](security-cryptography.md)
* [Klienty autoryzacji](security-auth-clients.md)
* [Bezpieczeństwo w praktyce](security-best-practices.md)


Pamięć podręczna
----------------

* [Przegląd](caching-overview.md)
* [Pamięć podręczna danych](caching-data.md)
* [Pamięć podręczna fragmentów](caching-fragment.md)
* [Pamięć podręczna stron](caching-page.md)
* [Pamięć podręczna HTTP](caching-http.md)


Webserwisy z wykorzystaniem REST
--------------------------------

* [Szybki start](rest-quick-start.md)
* [Zasoby](rest-resources.md)
* [Kontrolery](rest-controllers.md)
* [Routing](rest-routing.md)
* [Formatowanie odpowiedzi](rest-response-formatting.md)
* [Uwierzytelnianie](rest-authentication.md)
* [Limit użycia](rest-rate-limiting.md)
* [Wersjonowanie](rest-versioning.md)
* [Obsługa błędów](rest-error-handling.md)


Narzędzia wspomagające tworzenie aplikacji
------------------------------------------

* [Pasek debugowania i debuger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
* [Generowanie kodu przy użyciu Gii](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide/README.md)
* [Generowanie dokumentacji API](https://github.com/yiisoft/yii2-apidoc)


Testowanie
----------

* [Przegląd](test-overview.md)
* [Konfiguracja środowiska testowego](test-environment-setup.md)
* [Testy jednostkowe](test-unit.md)
* [Testy funkcjonalnościowe](test-functional.md)
* [Testy akceptacyjne](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Tematy specjalne
----------------

* [Szablon zaawansowanej aplikacji](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md)
* [Tworzenie aplikacji od podstaw](tutorial-start-from-scratch.md)
* [Komendy konsolowe](tutorial-console.md)
* [Podstawowe walidatory](tutorial-core-validators.md)
* [Internacjonalizacja](tutorial-i18n.md)
* [Wysyłanie poczty](tutorial-mailing.md)
* [Poprawianie wydajności](tutorial-performance-tuning.md)
* [Współdzielone środowisko hostingowe](tutorial-shared-hosting.md)
* [Silniki szablonów](tutorial-template-engines.md)
* [Praca z kodem zewnętrznym](tutorial-yii-integration.md)


Widżety
-------

* [GridView](http://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](http://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](http://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](http://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](http://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](http://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](http://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](http://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Widżety Bootstrapowe](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide/README.md)
* [Widżety jQuery UI](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/README.md)


Klasy pomocnicze
----------------

* [Przegląd](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Url](helper-url.md)


Uwagi do polskiego tłumaczenia przewodnika
------------------------------------------

Niektóre z użytych w tym przewodniku programistycznych nazw zostały celowo spolszczone, w przypadku, gdy 
w literaturze popularnej nie występują ich polskie odpowiedniki. Mam nadzieję, że czytelnik wybaczy okazjonalne 
"settery", "gettery" i "traity", które umieszczamy tutaj licząc na powszechne zrozumienie tych terminów w polskiej 
społeczności programistycznej. Jednocześnie spolszczenia/tłumaczenia niektórych terminów, jak "Fixtures", odmawiamy na razie 
całkowicie, licząc na to, że język polski w końcu nadgoni lub wchłonie, w ten, czy inny sposób, techniczne nowości.
