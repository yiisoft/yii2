Przewodnik po Yii 2.0
===============================

Ten poradnik udostępniony jest na [Warunkach dokumentacji Yii](http://www.yiiframework.com/doc/terms/).

Wszelkie prawa zastrzeżone.

2014 (c) Yii Software LLC.


Wstęp
------------

* [O Yii](intro-yii.md)
* [Aktualizacja z wersji 1.1](intro-upgrade-from-v1.md)


Pierwsze kroki
---------------

* [Instalacja Yii](start-installation.md)
* [Uruchamianie aplikacji](start-workflow.md)
* [Witaj świecie](start-hello.md)
* [Praca z formularzami](start-forms.md)
* [Praca z bazami danych](start-databases.md)
* [Generowanie kodu za pomocą Gii](start-gii.md)
* [Dalsze kroki](start-looking-ahead.md)


Struktura aplikacji
---------------------

* [Przegląd](structure-overview.md)
* [Entry Scripts](structure-entry-scripts.md)
* [Aplikacje](structure-applications.md)
* [Komponenty aplikacji](structure-application-components.md)
* [Kontrolery](structure-controllers.md)
* [Modele](structure-models.md)
* [Widoki](structure-views.md)
* [Moduły](structure-modules.md)
* [Filtry](structure-filters.md)
* [Widżety](structure-widgets.md)
* [Zasoby(Assets)](structure-assets.md)
* [Rozszerzenia](structure-extensions.md)


Handling Requests
-----------------

* **TBD** [Bootstrapping](runtime-bootstrapping.md)
* **TBD** [Routing](runtime-routing.md)
* **TBD** [Request](runtime-requests.md)
* **TBD** [Response](runtime-responses.md)
* **TBD** [Sesje i ciastka(cookies)](runtime-sessions-cookies.md)
* [Parsowanie i generowanie adresów URL](runtime-url-handling.md)
* [Obsługa błędów](runtime-handling-errors.md)
* [Zapis logów](runtime-logging.md)


Kluczowe koncepcje
------------

* [Komponenty](concept-components.md)
* [Właściwości](concept-properties.md)
* [Zdarzenia(Events)](concept-events.md)
* [Zachowania(Behaviors)](concept-behaviors.md)
* [Konfiguracje](concept-configurations.md)
* [Aliasy](concept-aliases.md)
* [Autoładowanie klas](concept-autoloading.md)
* [Lokator usług](concept-service-locator.md)
* [Kontener wstrzykiwania zależoności(DI Container)](concept-di-container.md)


Praca z bazami danych
----------------------

* [Obiekt dostępu bazy danych(DAO)](db-dao.md): Łączenie z bazą, podstawowe zapytania, transakcje i manipulacja schematem.
* [Budowniczy zapytań](db-query-builder.md): Zapytania do bazy danych z użyciem warstwy abstrakcyjnej.
* [Rekord aktywny](db-active-record.md): ORM Rekordu aktywnego, otrzymywanie i manipulacja rekordami oraz definiowanie relacji.
* [Migracje](db-migrations.md): Użycie systemu kontroli wersji na twoich bazach danych podczas tworzenia aplikacji w grupie.
* **TBD** [Sphinx](db-sphinx.md)
* **TBD** [Redis](db-redis.md)
* **TBD** [MongoDB](db-mongodb.md)
* **TBD** [ElasticSearch](db-elastic-search.md)


Otrzymywanie danych od użytkowników
-----------------------

* [Tworzenie formularzy](input-forms.md)
* [Walidacja danych wejściowych](input-validation.md)
* **TBD** [Wysyłanie plików](input-file-upload.md)
* **TBD** [Otrzymywanie danych z wielu modeli](input-multiple-models.md)


Wyświetlanie danych
---------------

* **TBD** [Formatowanie danych](output-formatting.md)
* **TBD** [Stronicowanie](output-pagination.md)
* **TBD** [Sortowanie](output-sorting.md)
* [Dostawcy danych](output-data-providers.md)
* [Widżety danych](output-data-widgets.md)
* [Working with Client Scripts](output-client-scripts.md)
* [Tematy](output-theming.md)


Bezpieczeństwo
--------

* [Uwierzytelnianie](security-authentication.md)
* [Autoryzacja](security-authorization.md)
* [Praca z hasłami](security-passwords.md)
* **TBD** [Auth Clients](security-auth-clients.md)
* **TBD** [Najlepsze praktyki](security-best-practices.md)


Cache'owanie
-------

* [Przegląd](caching-overview.md)
* [Cache'owanie danych](caching-data.md)
* [Cache'owanie fragmentów](caching-fragment.md)
* [Cache'owanie stron](caching-page.md)
* [Cache'owanie HTTP](caching-http.md)


Webserwisy z wykorzystaniem REST
--------------------

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
-----------------

* [Pasek debugowania i debuger](tool-debugger.md)
* [Generowanie kody przy użyciu Gii](tool-gii.md)
* **TBD** [Generowanie dokumentacji API](tool-api-doc.md)


Testowanie
-------

* [Przegląd](test-overview.md)
* [Ustawienia środowiska testowego](test-endvironment-setup.md)
* [Testy jednostkowe](test-unit.md)
* [Testy funkcjonalnościowe](test-functional.md)
* [Testy akceptacyjne](test-acceptance.md)
* [Fixtures](test-fixtures.md)


Tematy specjalne
--------------

* [Szablon zaawansowanej aplikacji](tutorial-advanced-app.md)
* [Tworzenie aplikacji od podstaw](tutorial-start-from-scratch.md)
* [Komendy konsolowe](tutorial-console.md)
* [Podstawowe walidatory](tutorial-core-validators.md)
* [Internacjonalizacja](tutorial-i18n.md)
* [Mailing](tutorial-mailing.md)
* [Poprawianie wydajności](tutorial-performance-tuning.md)
* **TBD** [Shared Hosting Environment](tutorial-shared-hosting.md)
* [Silniki szablonów](tutorial-template-engines.md)
* [Praca z kodem zewnętrznym](tutorial-yii-integration.md)


Widżety
-------

* GridView: link to demo page
* ListView: link to demo page
* DetailView: link to demo page
* ActiveForm: link to demo page
* Pjax: link to demo page
* Menu: link to demo page
* LinkPager: link to demo page
* LinkSorter: link to demo page
* [Bootstrap Widgets](widget-bootstrap.md)
* [Jquery UI Widgets](widget-jui.md)


Klasy pomocnicze
-------

* [Przegląd](helper-overview.md)
* **TBD** [ArrayHelper](helper-array.md)
* **TBD** [Html](helper-html.md)
* **TBD** [Url](helper-url.md)
* **TBD** [Security](helper-security.md)

