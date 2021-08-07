Przewodnik po Yii 2.0
=====================

Ten poradnik udostępniony jest na [Warunkach dokumentacji Yii](https://www.yiiframework.com/doc/terms/).

Wszelkie prawa zastrzeżone.

2014 (c) Yii Software LLC.


Wstęp
-----

* [O Yii](intro-yii.md)
* [Aktualizacja z wersji 1.1](intro-upgrade-from-v1.md)


Pierwsze kroki
--------------

* [Co musisz wiedzieć](start-prerequisites.md)
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
* [Zasoby (Assets)](structure-assets.md)
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
* [Zdarzenia (Events)](concept-events.md)
* [Zachowania (Behaviors)](concept-behaviors.md)
* [Konfiguracje](concept-configurations.md)
* [Aliasy](concept-aliases.md)
* [Autoładowanie klas](concept-autoloading.md)
* [Lokator usług (Service Locator)](concept-service-locator.md)
* [Kontener wstrzykiwania zależności (DI Container)](concept-di-container.md)


Praca z bazami danych
---------------------

* [Obiekty dostępu do danych (DAO)](db-dao.md): Łączenie z bazą, podstawowe zapytania, transakcje i manipulacja schematem.
* [Konstruktor kwerend](db-query-builder.md): Zapytania do bazy danych z użyciem warstwy abstrakcyjnej.
* [Active Record](db-active-record.md): Active Record ORM, otrzymywanie i manipulacja rekordami oraz definiowanie relacji.
* [Migracje](db-migrations.md): Użycie systemu kontroli wersji bazy danych do pracy z wieloma środowiskami.
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


Odbieranie danych od użytkowników
---------------------------------

* [Tworzenie formularzy](input-forms.md)
* [Walidacja danych wejściowych](input-validation.md)
* [Wysyłanie plików](input-file-upload.md)
* [Odczytywanie tablicowych danych wejściowych](input-tabular-input.md)
* [Pobieranie danych dla wielu modeli](input-multiple-models.md)
* [Rozszerzanie ActiveForm po stronie klienta](input-form-javascript.md)


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
* [Klienty autoryzacji](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
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

* [Pasek debugowania i debuger](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Generowanie kodu przy użyciu Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [Generowanie dokumentacji API](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


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

* [Szablon zaawansowanej aplikacji](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [Tworzenie aplikacji od podstaw](tutorial-start-from-scratch.md)
* [Komendy konsolowe](tutorial-console.md)
* [Wbudowane walidatory](tutorial-core-validators.md)
* [Docker](tutorial-docker.md)
* [Internacjonalizacja](tutorial-i18n.md)
* [Wysyłanie poczty](tutorial-mailing.md)
* [Poprawianie wydajności](tutorial-performance-tuning.md)
* [Współdzielone środowisko hostingowe](tutorial-shared-hosting.md)
* [Silniki szablonów](tutorial-template-engines.md)
* [Praca z kodem zewnętrznym](tutorial-yii-integration.md)
* [Używanie Yii jako mikroframeworka](tutorial-yii-as-micro-framework.md)


Widżety
-------

* [GridView](https://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](https://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](https://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](https://www.yiiframework.com/doc-2.0/guide-input-forms.html#activerecord-based-forms-activeform)
* [Pjax](https://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](https://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](https://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](https://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Widżety Bootstrapowe](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap/doc/guide)
* [Widżety jQuery UI](https://www.yiiframework.com/extension/yiisoft/yii2-jui/doc/guide)


Klasy pomocnicze
----------------

* [Przegląd](helper-overview.md)
* [ArrayHelper](helper-array.md)
* [Html](helper-html.md)
* [Json](helper-json.md)
* [Url](helper-url.md)


Uwagi do polskiego tłumaczenia przewodnika
------------------------------------------

Niektóre z użytych w tym przewodniku programistycznych nazw zostały celowo spolszczone, w przypadku, gdy 
w literaturze popularnej nie występują ich polskie odpowiedniki. Mam nadzieję, że czytelnik wybaczy okazjonalne 
"settery", "gettery" i "traity", które umieszczamy tutaj licząc na powszechne zrozumienie tych terminów w polskiej 
społeczności programistycznej. Jednocześnie spolszczenia/tłumaczenia niektórych terminów, jak "Fixtures", odmawiamy na razie 
całkowicie, licząc na to, że język polski w końcu nadgoni lub wchłonie, w ten, czy inny sposób, techniczne nowości.
