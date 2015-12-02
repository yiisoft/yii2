Bootstrapping
=============

Bootstrapping to proces przygotowania środowiska działania aplikacji przed jej uruchomieniem, w celu przyjęcia i przetworzenia przychodzącego żądania. 
Mechanizm ten zachodzi w dwóch miejscach: [skrypcie wejściowym](structure-entry-scripts.md) i w samej [aplikacji](structure-applications.md).

[Skrypt wejściowy](structure-entry-scripts.md) zajmuje się rejestracją autoloaderów klas dla poszczególnych bibliotek, wliczając w to autoloader Composera 
poprzez jego plik `autoload.php` i autoloader Yii poprzez plik klasy `Yii`. Następnie skrypt wejściowy ładuje [konfigurację](concept-configurations.md) 
i tworzy instancję [aplikacji](structure-applications.md).

W konstruktorze aplikacji zachodzi następujący proces bootstrappingu:

1. Wywołanie metody [[yii\base\Application::preInit()|preInit()]], która konfiguruje niektóre z właściwości aplikacji o wysokim priorytecie, 
   takich jak [[yii\base\Application::basePath|basePath]].
2. Rejestracja [[yii\base\Application::errorHandler|obsługi błędów]].
3. Inicjalizacja właściwości aplikacji za pomocą utworzonej konfiguracji.
4. Wywołanie metody [[yii\base\Application::init()|init()]], która uruchamia proces bootstrappingu komponentów za pomocą metody 
   [[yii\base\Application::bootstrap()|bootstrap()]].
   - Załadowanie pliku manifestu dla rozszerzeń `vendor/yiisoft/extensions.php`.
   - Utworzenie i uruchomienie [komponentów bootstrapowych](structure-extensions.md#bootstrapping-classes) zadeklarowanych przez rozszerzenia.
   - Utworzenie i uruchomienie [komponentów aplikacji](structure-application-components.md) i/lub [modułów](structure-modules.md), zadeklarowanych 
     we [właściwości bootstrapowej](structure-applications.md#bootstrap) aplikacji.

Ponieważ proces bootstrappingu jest wykonywany przed obsługą *każdego* żądania, niezwykle istotnym jest, aby był lekki i zoptymalizowany tak bardzo, jak to tylko możliwe.

Należy unikać rejestrowania zbyt dużej ilości komponentów bootstrappingowych. Wymagane jest to tylko w przypadku, gdy taki komponent powinien uczestniczyć 
w całym cyklu życia obsługi przychodzącego żądania. Dla przykładu, jeśli moduł wymaga zarejestrowania dodatkowych zasad przetwarzania adresów URL, powinien być wymieniony 
we [właściwości bootstrapowej](structure-applications.md#bootstrap), aby nowe zasady mogły być wykorzystane do przetworzenia żądania.

W środowisku produkcyjnym zaleca się zastosowanie pamięci podręcznej kodu, takiej jak [PHP OPcache] lub [APC], aby zminimalizować czas konieczny do załadowania i przetworzenia 
plików PHP.

[PHP OPcache]: http://php.net/manual/en/intro.opcache.php
[APC]: http://php.net/manual/en/book.apc.php

Niektóre duże aplikacje posiadają bardzo skomplikowaną [konfigurację](concept-configurations.md), składającą się z wielu mniejszych plików konfiguracyjnych. 
W takim przypadku zalecane jest zapisanie w pamięci całej wynikowej tablicy konfiguracji i załadowanie jej stamtąd bezpośrednio przed utworzeniem instancji aplikacji 
w skrypcie wejściowym.