Organizacja projektu
====================

W tym dokumencie opisana jest organizacja repozytoriów deweloperskich Yii 2.
 
1. Poszczególne bazowe rozszerzenia i szablony projektów są utrzymywane w oddzielnych *niezależnych* projektach na GitHubie 
   pod GitHubowym szyldem [yiisoft](https://github.com/yiisoft).
    
   Nazwy repozytoriów rozszerzeń są poprzedzone przedrostkiem `yii2-`, np. `yii2-gii` dla rozszenia `gii`.
   Nazwa pakietu composera jest taka sama jak ścieżka repozytorium w Githubie, np. `yiisoft/yii2-gii`.
   
   Nazwy repozytoriów szablonów projektów aplikacji są poprzedzone przedrostkiem `yii2-app-`, np. `yii2-app-basic` dla 
   szablonu aplikacji `basic`.
   Nazwa pakietu composera jest taka sama jak ścieżka repozytorium w Githubie, np. `yiisoft/yii2-app-basic`.
   
   Każde rozszerzenie/projekt aplikacji:
 
   * utrzymuje swoją dokumentację i instrukcje w folderze "docs". Dokumentacja API zostanie wygenerowana w locie, podczas tworzenia nowego wydania.
   * utrzymuje swój kod testów w folderze "tests".
   * utrzymuje swoje tłumaczenia komunikatów i pozostały istotny meta kod.
   * śledzi zgłoszenia w odpowiednim projekcie GitHuba.
      
   Repozytoria rozszerzeń będą wydawane niezależnie w miarę potrzeb, szablony projektów będą wydawane razem z frameworkiem.
   Więcej szczegółów znajdziesz w [polityce wersjonowania](versions.md).

2. Projekt `yiisoft/yii2` jest głównym repozytorium deweloperskim frameworka Yii 2.
   Repozytorium to utrzymuje pakiet composera [yiisoft/yii2-dev](https://packagist.org/packages/yiisoft/yii2-dev).
   Zawiera bazowy kod frameworka, jego testy jednostkowe, przewodnik i zestaw narzędzi przydatnych w procesie tworzenia i wydawania wersji.
   
   Zgłoszenia błędów i nowych funkcjonalności są śledzone w tym projekcie Githuba.
   
3. Repozytorium `yiisoft/yii2-framework` jest wydzielonym tylko do odczytu folderem `framework` z repozytorium deweloperskiego 
   i utrzymuje pakiet composera [yiisoft/yii2](https://packagist.org/packages/yiisoft/yii2), który jest oficjalnym pakietem 
   służącym do instalacji frameworka.

4. Dla ułatwienia procesu deweloperskiego szablony projektów i rozszerzenia mogą zostać dołączone do struktur projektu za pomocą 
   komendy [build dev/app](git-workflow.md#prepare-the-test-environment).
