Wydawanie nowej wersji
======================

Lista kroków koniecznych do wykonania podczas wydawania frameworka urosła znacznie w ciągu ostatnich lat i stała się uciążliwa do 
utrzymywania ręcznego, zatem stworzyliśmy narzędzie dostępne z linii komend, aby upewnić się, że żaden z kroków nie zostanie pominięty.

Omówienie kroków wydania
------------------------

- ...

Komenda wydania
---------------

Poniższe kroki są zautomatyzowane za pomocą [konsolowej komendy wydania](../../build/controllers/ReleaseController.php), która jest 
dostępna w deweloperskim repozytorium frameworka.

Komenda wydania może być uruchomiona za pomocą aplikacji Yii w folderze `build` frameworka:

```bash
./build/build help release  # uruchom w fodlerze głównym frameworka
```

> Info: Możesz uruchomić komendę z opcją `--dryRun`, aby zobaczyć co może zrobić. Używając tej opcji, nie zostanie wykonana 
> żadna zmiana, a modyfikacje plików i tagi nie będą tworzone i wysyłane.

### Wymagania

Działanie komenda wydania uzależnione jest od środowiska deweloperskiego opisanego w 
[Cyklu produkcyjnym Git](git-workflow.md#extensions), przykładowo szablony aplikacji muszą znajdować się w folderze `/apps/` 
a rozszerzenia w `/extensions/`.
Struktury te najlepiej utworzyć za pomocą komend `dev/app` i `dev/ext`.

Przykładowa instalacja rozszerzenia:

```bash
./build/build dev/ext authclient
```

lub szablonu:

```bash
./build/build dev/app basic
```

Taka instalacja zapewni użycie tego samego kodu repozytorium dla rozszerzenia, jaki znajduje się w aktualnej wersji repozytorium.

### Informacje o wersji

Aby sprawdzić informacje dotyczące wersji frameworka i rozszerzeń, możesz uruchomić

```bash
./build/build release/info
```

Możesz uruchomić powyższą komendę z `--update`, aby pobrać listę tagów dla wszystkich repozytoriów w celu uzyskania najnowszych 
informacji.

### Tworzenie wydania

Tworzenie wydania frameworka zawiera poniższe komendy (szablony aplikacji są zawsze wydawane razem z frameworkiem):

```bash
./build release framework
./build release app-basic
./build release app-advanced
```

Tworzenie wydania rozszerzenia zawiera tylko jedną komendę (np. dla `redis`):

```bash
./build release redis
```

Domyślnie komenda wydania wydaje nową pomniejszą wersję w aktualnej gałęzi.
Aby wydać inną wersję niż domyślna, należy określić ją bezpośrednio używając opcji `--version`, np. `--version=2.1.0` 
lub `--version=2.1.0-beta`.

#### Wydanie nowej głównej wersji np. 2.1.0

Wydawanie nowej głownej wersji obejmuje zmianę gałęzi, jak to opisano w [polityce wersjonowania](versions.md).
Poniższy przykład opisuje wydanie wersji `2.1.0`, stworzonej w gałęzi `2.1` pochodzącej z `master`. `master` zawiera już wcześniej 
wersje `2.0.x`.

- stwórz nową gałąź `2.0` z `master`,
- upewnij się, że composer.json nie zawiera już aliasu dla tej gałęzi,
- scal potrzebne zmiany z `master` do `2.1`,
- ustaw `master` na wskazywanie ostatniego wprowadzenia zmian w `2.1`,
- ustaw alias gałęzi w composer.json dla master na `2.1.x-dev`,
- skasuj gałąź `2.1`.

Teraz pobierz `master` i uruchom komendę wydania z opcją `--version=2.1.0`. 
