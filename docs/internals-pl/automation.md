Zadania zautomatyzowane
=======================

Istnieją zadania wykonywane automatycznie podczas pracy nad Yii:

- Generowanie mapy klas `classes.php` umieszczonej w folderze głównym frameworka.
  Uruchom `./build/build classmap`, aby ją wygenerować.

- Uaktualnianie magicznego pliku z typami Mime (`framework/helpers/mimeTypes.php`) z repozytorium Apache HTTPd.
  Uruchom `./build/build mime-type`, aby uaktualnić plik.
  
- Korekta porządku wpisów w pliku CHANGELOG może być przeprowadzona poprzez uruchomienie `./build/build release/sort-changelog framework`.

Wszystkie powyższe komendy są uruchamiane w [procesie wydawania nowej wersji](release.md). 
Mogą być też uruchomione pomiędzy wydaniami, ale nie jest to konieczne.
