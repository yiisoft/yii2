Zadania zautomatyzowane
=======================

Istnieją zadania wykonywane automatycznie podczas pracy nad Yii:

- Generowanie mapy klas `classes.php` umieszczonej w folderze głównym frameworka.
  Uruchom `./build/build classmap`, aby ją wygenerować.

- Generowanie notacji `@property` w plikach klas, które opisują właściwości wprowadzane przez gettery i settery.
  Uruchom `./build/build php-doc/property`, aby je zaktualizować.

- Poprawianie stylu kodu i innych drobnych kwestii w komentarzach phpdoc.
  Uruchom `./build/build php-doc/fix`, aby je poprawić.
  Sprawdź efekty poprawek, zanim je zatwierdzisz, ponieważ mogą wystąpić pewne niechciane modyfikacje (proces nie jest doskonały).
  Możesz użyć `git add -p`, aby przejrzeć zmiany.

- Uaktualnianie magicznego pliku z typami Mime (`framework/helpers/mimeTypes.php`) z repozytorium Apache HTTPd.
  Uruchom `./build/build mime-type`, aby uaktualnić plik.
  
- Korekta porządku wpisów w pliku CHANGELOG może być przeprowadzona poprzez uruchomienie `./build/build release/sort-changelog framework`.

Wszystkie powyższe komendy są uruchamiane w [procesie wydawania nowej wersji](release.md). 
Mogą być też uruchomione pomiędzy wydaniami, ale nie jest to konieczne.
