Cykl produkcyjny Git dla kontrybutorów Yii 2
============================================

Zatem chcesz współtworzyć Yii? Świetnie! Aby przyspieszyć proces akceptacji Twoich modyfikacji, pamiętaj o przestrzeganiu poniższych 
wskazówek. Jeśli pierwszy raz masz do czynienia z repozytorium Git lub GitHubem, zapoznaj się najpierw z 
[dokumentacją pomocy GitHub](https://help.github.com/), witryną [sprawdź Gita](https://try.github.com) lub też poczytaj o 
[wewnętrznym modelu danych Git](https://nfarina.com/post/9868516270/git-is-simpler).

Przygotuj swoje środowisko deweloperskie
----------------------------------------

Poniższe instrukcje pomogą Ci w tworzeniu środowiska deweloperskiego dla Yii, którego możesz użyć podczas pracy nad bazowym kodem 
frameworka Yii. Wykonać je należy tylko raz, przed pierwszą kontrybucją.

### 1. [Sforkuj (zduplikuj własną wersję)](https://help.github.com/fork-a-repo/) repozytorium Yii w serwisie GitHub i sklonuj swojego forka do środowiska deweloperskiego

```
git clone git@github.com:TWOJA-NAZWA-UZYTKOWNIKA-GITHUB/yii2.git
```

Jeśli napotkasz na problemy związane z Gitem i GitHubem na systemie operacyjnym Linux lub też otrzymujesz błędy typu 
"Permission Denied (publickey)" ("Odmowa dostępu (klucz publiczny)"), musisz odpowiednio 
[skonfigurować instalację Gita do pracy z GitHubem](https://help.github.com/linux-set-up-git/).

> Tip: jeśli nie jesteś biegły w używaniu Gita, polecamy doskonałą darmową książkę [Pro Git](https://git-scm.com/book/en/v2) (z polskim tłumaczeniem dla [poprzedniej edycji](https://git-scm.com/book/pl/v1).

### 2. Dodaj główne repozytorium Yii jako dodatkowy zdalny git nazwany "upstream"

Przejdź do folderu, do którego sklonowałeś Yii (zwykle "yii2") i uruchom następującą komendę:

```
git remote add upstream https://github.com/yiisoft/yii2.git
```

### 3. Przygotuj środowisko testowe <span id="prepare-the-test-environment"></span>

Poniższe kroki nie są wymagane, jeśli chcesz pracować tylko nad tłumaczeniami lub dokumentacją.

- uruchom `composer install`, aby zainstalować wymagane zależności (zakładając, że masz [composera zainstalowanego globalnie](https://getcomposer.org/doc/00-intro.md#globally)).

Jeśli zamierzasz pracować z JavaScript:

- uruchom `npm install`, aby zainstalować narzędzia testerskie JavaScript i ich zależności (zakładając, że masz [zainstalowane Node.js i NPM]
(https://nodejs.org/en/download/package-manager/)).

> Note: testy JavaScript są zależne od biblioteki [jsdom](https://github.com/tmpvar/jsdom), która wymaga Node.js 4 lub nowszego.
Zalecane jest używanie Node.js w wersji 6 lub 7.

- uruchom komendę `php build/build dev/app basic <fork>`, aby sklonować podstawowy szablon projektu aplikacji i zainstaluj dla niego zależności composera.
  `<fork>` jest URL Twojego forka repozytorium, np. `git@github.com:my_nickname/yii2-app-basic.git`. Jeśli jesteś kontrybutorem głównego kodu frameworka, możesz pominąć wskazywanie forka.
  Komenda ta zainstaluje normalnie pakiety composera, ale jednocześnie podlinkuje folder yii2 do 
  pobranego wcześniej repozytorium, dzięki czemu otrzymasz instalację jednej instacji całego kodu na raz.
  
  Powtórz ten krok dla zaawansowanego szablonu projektu aplikacji, jeśli chcesz: `php build/build dev/app advanced <fork>`.
  
  Ta komenda służy również do aktualizacji zależności; uruchamia wewnętrznie `composer update`.

> Note: Domyślnie repozytoria git klonowane są za pośrednictwem SSH - aby użyć zamiast tego połączenia HTTPs dodaj flagę `--useHttp` 
> do komendy `build`.

**Teraz dysponujesz już odpowiednim miejscem, aby rozpocząć hackowanie Yii 2.**

Poniższe kroki są opcjonalne.

### Testy jednostkowe

Możesz uruchomić testy jednostkowe za pomocą komendy `phpunit` w głównym folderze repozytorium. 
Jeśli nie posiadasz phpunit zainstalowanego globalnie, użyj zamiast tego komendy `php vendor/bin/phpunit` lub 
`vendor/bin/phpunit.bat` w przypadku korzystania z systemu Windows.

Niektóre testy wymagają przygotowania i skonfigurowania dodatkowych baz danych. Możesz utworzyć plik `tests/data/config.local.php`, 
aby nadpisać konfigurację ustawioną w `tests/data/config.php`.

Możesz ograniczyć testy do grupy tych, nad którymi akurat pracujesz, np. aby uruchomić tylko testy walidatorów i redisa użyj 
`phpunit --group=validators,redis`. Możesz zobaczyć listę dostępnych grup po wpisaniu `phpunit --list-groups`. 

Możesz rozpocząć testy jednostkowe JavaScript, uruchamiając `npm test` w głównym folderze repozytorium.

### Rozszerzenia

Aby pracować nad rozszerzeniami, musisz sklonować ich repozytoria. Stworzyliśmy komendę, która pozwoli Ci to zrobić w prosty sposób:

```
php build/build dev/ext <nazwa-rozszerzenia> <fork>
```

gdzie `<nazwa-rozszerzenia>` jest poprawną nazwą, np. `redis`, a `<fork>` jest URL forka rozszerzenia np. `git@github.com:my_nickname/yii2-redis.git`. 
Jeśli jesteś kontrybutorem głównego kodu frameworka, możesz pominąć wskazywanie forka.

Jeśli chcesz przetestować rozszerzenie w jednym z szablonów projektów, po prostu dodaj je do pliku `composer.json` aplikacji 
w zwyczajowy sposób, np. dodaj `"yiisoft/yii2-redis": "~2.0.0"` do sekcji `require` w podstawowym szablonie aplikacji.
Uruchomienie `php build/build dev/app basic <fork>` zainstaluje rozszerzenie i jego zależności i utworzy symlink do folderu 
`extensions/redis`, dzięki czemu możesz pracować bezpośrednio w repozytorium yii2, a nie folderze vendorowym composera.

> Note: Również w tym przypadku pamiętaj o fladze `--useHttp`, jak to opisano powyżej.


Praca nad błędami i funkcjonalnościami
--------------------------------------

Mając przygotowane środowisko deweloperskie, jak zostało to opisane powyżej,  możesz rozpocząć prace nad poprawianiem błędów 
i rozwijaniem funkcjonalności.

### 1. Upewnij się, że istnieje zgłoszony problem dotyczący kodu, który zamierzasz modyfikować, jeśli wymaga on wzmożonej pracy

Wszystkie nowe funkcjonalności i poprawki błędów powinny być powiązane ze zgłoszeniem, aby zapewnić punkt odniesienia dla dyskusji 
i komentarzy. Poświęć kilka minut, aby przejrzeć istniejące zgłoszenia i odszukać takie, które opisuje Twoją przyszłą kontrybucję. 
Jeśli je znajdziesz na liście, dopisz w nim komentarz z informacją, że pracujesz aktualnie nad tym zagadnieniem. Jeśli takiego 
zgłoszenia nie znajdziesz, [stwórz nowe](report-an-issue.md) lub dodaj prośbę o dołączenie kodu bezpośrednio, jeśli to prosta poprawka. 
Pozwoli to zespołowi programistów zapoznać się z Twoją sugestią i zapewnić odpowiednią pomoc i komentarz w czasie całego procesu.

> W przypadku drobnych zmian, problemów z dokumentacją czy też szybkich poprawek kodu, nie musisz zgłaszać nowego problemu; prośba o dołączenie kodu jest wystarczająca.

### 2. Pobierz aktualny kod z głównego repozytorium Yii

```
git fetch upstream
```

Od tego kroku powinieneś zawsze zaczynać w przypadku każdej nowej kontrybucji, aby upewnić się, że pracujesz na aktualnej wersji kodu.

### 3. Stwórz nową gałąź repozytorium dla Twojej funkcjonalności bazując na aktualnej gałęzi master Yii

> Jest to bardzo ważne, ponieważ nie będziesz mógł wysłać więcej niż jednej prośby o dołączenie kodu z Twojego konta, jeśli będziesz 
> używać gałęzi master.

Każdy oddzielna poprawka błędu czy też zmiana kodu powinna być utworzona w swojej własnej gałęzi. Nazwy gałęzi powinny być opisowe 
i zaczynać się od numeru zgłoszenia, które jej dotyczą. Jeśli nie poprawiasz któregoś z konkretnych zgłoszeń, po prostu pomiń numer.
Dla przykładu:

```
git checkout upstream/master
git checkout -b 999-nazwa-twojej-galezi-w-tym-miejscu
```

### 4. Zademonstruj swoją magię, napisz swój kod

Upewnij się, że działa poprawnie :)

Testy jednostkowe są zawsze mile widziane. Prawidłowo i w całości przetestowany kod znacznie upraszcza proces weryfikacji kontrybucji.
Akceptowane są również testy jednostkowe kończące się porażką jako opisy zgłoszeń problemów.

### 5. Zaktualizuj CHANGELOG (dziennik zmian)

Zedytuj plik CHANGELOG, aby dołączyć informację o Twojej modyfikacji; powinna ona znaleźć się na samym początku pliku zaraz 
pod pierwszym nagłówkiem (określającym wersję, nad którą właśnie trwa praca). Linia w dzienniku zmian powinna być zapisana 
po angielsku i wyglądać jak jedna z poniższych:

```
Bug #999: a description of the bug fix (Your Name)
Enh #999: a description of the enhancement (Your Name)
```

`#999` jest numerem zgłoszenia, do którego odnosi się `Bug` (błąd) lub `Enh` (ulepszenie).
Dziennik zmian powinien być pogrupowany według typu (`Bug`, `Enh`) i posortowany według numerów zgłoszeń.

W przypadku drobnych zmian, np. literówek i poprawek dokumentacji, nie ma potrzeby aktualizować pliku CHANGELOG.

### 6. Zatwierdź swoje modyfikacje

Dodaj swoje pliki/zmiany, które chcesz zatwierdzić do [kolejki oczekujących](https://git.github.io/git-reference/basic/#add) za pomocą

```
git add sciezka/do/mojego/pliku.php
```

Możesz użyć opcji `-p`, aby wybrać modyfikacje, które chcesz, aby pojawiły się w zgłoszeniu.

Zatwierdź swoje zmiany wraz z odpowiednio opisującym je komentarzem. Upewnij się, że podałeś w nim numer zgłoszenia w postaci `#XXX`, 
aby GitHub mógł automatycznie połączyć modyfikacje ze zgłoszeniem:

```
git commit -m "A brief description of this change which fixes #999 goes here"
```

### 7. Pobierz świeży kod Yii z upstream do Twojej gałęzi

```
git pull upstream master
```

Dzięki temu możesz być pewny, że posiadasz aktualny kod w Twojej gałęzi przed wysłaniem prośby o dołączenie go. 
Jeśli pojawią się jakiekolwiek konflikty scalania, powinieneś je naprawić i zatwierdzić zmiany jeszcze raz. 
Dzięki temu ekipa programistów Yii będzie mogła scalić Twoje zmiany z bazowym kodem za pomocą tylko jednego kliknięcia.

### 8. Po rozwiązaniu wszystkich konfliktów, wyślij swój kod do GitHuba

```
git push -u origin 999-nazwa-twojej-galezi-w-tym-miejscu
```

Parametr `-u` spowoduje, że Twoja gałąź zostanie automatycznie wysłana i pobrana z gałęzi GitHuba. Oznacza to tyle, że następnym 
razem, kiedy napiszesz `git push`, będzie wiedział, gdzie ją wysłać. Ułatwia to pracę w przypadku, gdy chcesz zatwierdzić więcej 
modyfikacji w pojedynczej prośbie o dołączenie kodu.

### 9. Otwórz [prośbę o połączenie kodu](https://help.github.com/articles/creating-a-pull-request-from-a-fork/) z upstream.

Przejdź do swojego repozytorium na GitHubie i kliknij "Pull Request", wybierz swoją gałąź po prawej stronie i podaj dodatkowe 
szczegóły w polu komentarza. Aby połączyć wysyłaną prośbę ze zgłoszeniem umieść gdziekolwiek w komentarzu `#999`, gdzie 999 jest 
numerem zgłoszenia.

> Zwróć uwagę na to, że każda prośba o dołączenie powinna zawierać kod poprawki dla pojedynczego zgłoszenia. 
> W przypadku wielu niepowiązanych ze sobą modyfikacji otwórz odpowiednie zgłoszenia do każdej z nich.

### 10. Ktoś przejrzy i oceni Twój kod

Ktoś zerknie na Twój kod i możesz zostać poproszony o wprowadzenie kilku zmian, a jeśli tak, przejdź do kroku #6 (nie musisz 
tworzyć nowego zgłoszenia, jeśli aktualne jest wciąż otwarte). W momencie, gdy Twój kod będzie zaakceptowany, zostanie scalony 
z kodem głównej gałęzi i stanie się częścią następnego wydania Yii. Jeśli jednak nie uzyska on akceptacji, nie zniechęcaj się - 
różni ludzie potrzebują różnych funkcjonalności i Yii nie może zapewnić ich wszystkich dla każdego. Twój kod pozostanie dostępny 
na GitHubie dla osób, które będą tego potrzebować.

### 11. Sprzątanie

Kiedy Twój kod zostanie zaakceptowany bądź odrzucony, możesz usunąć gałęzie, na których pracowałeś z lokalnego repozytorium 
i z `origin`.

```
git checkout master
git branch -D 999-nazwa-twojej-galezi-w-tym-miejscu
git push origin --delete 999-nazwa-twojej-galezi-w-tym-miejscu
```

### Note:

W celu wczesnego wykrycia ewentualnych problemów z integracją, każde żądanie scalenia głównego kodu Yii na GitHubie jest 
weryfikowane przez automatyczne testy [Travis CI](https://travis-ci.com). Ponieważ ekipa głównych programistów stara się nie 
nadużywać tej usługi, [`[ci skip]`](https://docs.travis-ci.com/user/customizing-the-build/#Skipping-a-build) jest dodawane przy komentarzu 
scalenia kodu, jeśli żądanie:

* dotyczy jedynie javascript, css lub plików obrazków,
* aktualizuje dokumentację,
* modyfikuje jedynie stałe łańcuchy znaków (np. w przypadku aktualizacji tłumaczeń)

Dzięki temu pomijane są automatyczne testy travisa dla zmian, które i tak nie są nimi pokryte.

### Przegląd komend (dla zaawansowanych kontrybutorów)

```
git clone git@github.com:TWOJA-NAZWA-UZYTKOWNIKA-GITHUB/yii2.git
git remote add upstream https://github.com/yiisoft/yii2.git
```

```
git fetch upstream
git checkout upstream/master
git checkout -b 999-nazwa-twojej-galezi-w-tym-miejscu

/* pokaż swoją magię, zaktualizuj dziennik zmian, jeśli to konieczne */

git add sciezka/do/mojego/pliku.php
git commit -m "A brief description of this change which fixes #999 goes here"
git pull upstream master
git push -u origin 999-nazwa-twojej-galezi-w-tym-miejscu
```
