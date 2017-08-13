Tworzenie własnej struktury aplikacji
=====================================

> Note: Ta sekcja jest w trakcie tworzenia.

[Podstawowy](https://github.com/yiisoft/yii2-app-basic) i [zaawansowany](https://github.com/yiisoft/yii2-app-advanced) szablon projektu jest w pełni wystarczający w większości 
przypadków, ale czasem może zajść potrzeba stworzenia własnego szablonu, na bazie którego tworzony będzie projekt.

Szablony projektów w Yii to po prostu repozytoria zawierające plik `composer.json` i zarejestrowane jako paczki Composera.
Każde repozytorium może stać sie taką paczką, dzięki czemu można je zainstalować wywołując komendę Composera `create-project`.

Ponieważ stworzenie nowego szablonu projektu od podstaw jest pracochłonne, łatwiej po prostu użyć jednego z gotowych szablonów jako bazy. W tym przykładzie skorzystamy 
z podstawowego szablonu.

Kopia podstawowego szablonu projektu
------------------------------------

Pierwszym krokiem jest wykonanie kopii podstawowego szablonu Yii z repozytorium Git:

```bash
git clone git@github.com:yiisoft/yii2-app-basic.git
```

Po zakończeniu pobierania plików repozytorium, można skasować folder `.git` wraz z zawartością, ponieważ wprowadzonych przez nas zmian nie zamierzamy wysyłać z powrotem.

Modyfikacja plików
------------------

Następnie należy zmodyfikować plik `composer.json`, aby opisywał nasz szablon. Zmień wartości `name` (nazwa), `description` (opis), `keywords` (słowa kluczowe), `homepage` 
(strona domowa), `license` (licencja), i `support` (wsparcie) na takie, które odpowiadają nowemu szablonowi. Zmodyfikuj również `require`, `require-dev`, `suggest` i wszelkie inne opcje 
zgodnie z wymaganiami.

> Note: W pliku `composer.json` użyj parametru `writable` znajdującego się w elemencie `extra`, aby określić 
> uprawnienia dla plików, które zostaną ustawione po utworzeniu aplikacji na podstawie szablonu.

Następnie należy zmodyfikować właściwą strukturę i zawartość aplikacji, aby stanowiły domyślną początkową wersję dla projektów. 
Na samym końcu zmodyfikuj plik README, aby pasował do szablonu.

Tworzenie paczki
----------------

Nowy szablon umieść w odpowiadającym mu repozytorium Git. Jeśli zamierzasz udostępnić go jako open source, [Github](http://github.com) jest najlepszym miejscem do tego celu. 
Jeśli jednak nie przewidujesz współpracy z innymi nad swoim szablonem, dowolne repozytorium Git będzie odpowiednie.

Następnie należy zarejestrować swoją paczkę dla Composera. Dla publicznie dostępnych szablonów paczkę należy zarejestrować w serwisie [Packagist](https://packagist.org/).
Z prywatnymi szablonami sprawa jest trochę bardziej skomplikowana - instrukcję, jak to zrobić, znajdziesz w 
[dokumentacji Composera](https://getcomposer.org/doc/05-repositories.md#hosting-your-own).

Użycie szablonu
---------------

Tylko tyle jest wymagane, aby stworzyć nowy szablon projektu Yii. Teraz już możesz rozpocząć pracę nad świeżym projektem, używając swojego szablonu, za pomocą komend:

```
composer global require "fxp/composer-asset-plugin:^1.3.1"
composer create-project --prefer-dist --stability=dev mojafirma/yii2-app-fajna nowy-projekt
```
