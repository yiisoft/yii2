Przygotowanie środowiska testowego
==================================

> Uwaga: Ta sekcja jest w trakcie tworzenia.

Yii 2 jest oficjalnie zintegrowany z [`Codeception`](https://github.com/Codeception/Codeception) - frameworkiem testowym, pozwalającym 
na utworzenie testów następujących typów:

- [Testy jednostkowe](test-unit.md) - sprawdzające czy pojedyncza jednostka kodu działa poprawnie;
- [Testy funkcjonalne](test-functional.md) - weryfikujące scenariusze działań z perspektywy użytkownika poprzez emulację przeglądarki;
- [Testy akceptacyjne](test-acceptance.md) - weryfikujące scenariusze działań z perspektywy użytkownika w przeglądarce.

Yii dostarcza gotowy do użycia zestaw testów wszystkich trzech typów zarówno dla szablonu projektu 
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) jak i 
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced).

W celu uruchomienia testów koniecznie jest zainstalowanie [Codeception](https://github.com/Codeception/Codeception).
Instalację można wykonać lokalnie - dla konkretnego pojedynczego projektu - lub globalnie - na komputerze deweloperskim.

Poniższe komendy służą do instalacji lokalnej:

```
composer require "codeception/codeception=2.0.*"
composer require "codeception/specify=*"
composer require "codeception/verify=*"
```

Do instalacji globalnej należy dodać dyrektywę `global`:

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

Jeśli nigdy wcześniej nie używałeś Composera do globalnych pakietów, uruchom komendę `composer global status`. W odpowiedzi powinieneś uzyskać:

```
Changed current directory to <directory>
```

Następnie dodaj `<directory>/vendor/bin` do zmiennej systemowej `PATH`. Od tej pory będziesz mógł użyć `codecept` z linii komend globalnie.

> Uwaga: instalacja globalna Codeception pozwala na użycie go we wszystkich projektach na komputerze deweloperskim oraz na wykonywanie 
> komendy `codecept` globalnie bez konieczności wskazywania ścieżki. Taka instalacja może jednak nie być pożądana, kiedy, dla przykładu, 
> dwa różne projekty wymagają różnych wersji Codeception.
> Dla uproszczenia wszystkie komendy powłoki odnoszące się do uruchamiania testów użyte w tym przewodniku są napisane przy założeniu, że Codeception
> został zainstalowany globalnie.
