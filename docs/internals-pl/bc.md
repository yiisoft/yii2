# Wsteczna kompatybilność

Absolutnie nie łamiemy wstecznej kompatybilności w wydaniach - łatach typu `2.x.y.Z` i staramy się unikać koniecznych do wprowadzenia 
zmian niekompatybilnych wstecznie w pomniejszych wydaniach typu `2.x.Y`.

Zapoznaj się z sekcją [Wersjonowanie Yii](versions.md), aby dowiedzieć się więcej o numerowaniu wersji. 

## Metodyka użytkowania

### Interfejsy

Przypadek użycia | Łamie kompatybilność wsteczną?
-----------------|-------------------------------
Informacja o zwracanym typie w intefejsie | Tak
Wywołanie metody interfejsu | Tak
**Implementacja interfejsu i ...** |
Implementacja metody | Tak
Dodanie argumentu do implementowanej metody | Tak
Dodanie domyślnej wartości do argumentu | Tak

### Klasy

Przypadek użycia | Łamie kompatybilność wsteczną?
-----------------|-------------------------------
Informacja o zwracanym typie w klasie | Tak
Tworzenie nowej instancji | Tak
Rozszerzenie klasy | Tak
Odwołanie do publicznej właściwości | Tak
Wywołanie publicznej metody | Tak
**Rozszerzenie klasy i ...** |
Odwołanie do chronionej właściwości	| Tak
Wywołanie chronionej metody	| Tak
Przeciążenie publicznej właściwości | Tak
Przeciążenie chronionej właściwości | Tak
Przeciążenie publicznej metody | Tak
Przeciążenie chronionej metody | Tak
Dodanie nowej właściwości | Nie
Dodanie nowej metody | Nie
Dodanie argumentu do przeciążonej metody | Tak
Dodanie domyślnej wartości do argumentu | Tak
Wywołanie prywatnej metody (przez Refleksję) | Nie
Odwołanie do prywatnej właściwości (przez Refleksję) | Nie


## Metodyka rozwoju

### Zmiana interfejsów

Przypadek użycia | Łamie kompatybilność wsteczną?
-----------------|-------------------------------
Usunięcie | Nie
Zmiana nazwy lub przestrzeni nazw | Nie
Dodanie interfejsu - rodzica | Tak, jeśli nie ma dodanych nowych metod
Usunięcie interfejsu - rodzica | Nie
**Metody interfejsu** | 
Dodanie metody | Nie
Usunięcie metody | Nie
Zmiana nazwy | Nie
Przeniesienie do interfejsu - rodzica | Tak
Dodanie argumentu bez domyślnej wartości | Nie
Dodanie argumentu z domyślną wartością | Nie
Usunięcie argumentów | Tak (tylko ostatnich)
Dodanie domyślnej wartości do argumentu | Nie
Usunięcie domyślnej wartości z argumentu | Nie
Dodanie informacji o typie argumentu | Nie
Usunięcie informacji o typie argumentu | Nie
Zmiana typu argumentu | Nie
Zmiana typu zwracanej wartości | Nie
**Stałe** |	 
Dodanie stałej | Tak
Usunięcie stałej | Nie
Zmiana wartości stałej | Tak, z wyjątkiem obiektów, które będą serializowane. Obowiązkowa dokumentacja w UPGRADE.md.

### Klasy

Przypadek użycia | Łamie kompatybilność wsteczną?
-----------------|-------------------------------
Usunięcie | Nie
Określenie jako final | Nie
Określenie jako abstract | Nie
Zmiana nazwy lub przestrzeni nazw | Nie
Zmiana klasy - rodzica | Tak, ale oryginalna klasa - rodzic musi pozostać przodkiem klasy.
Dodanie interfejsu | Tak
Usunięcie interfejsu | Nie
**Publiczne właściwości** | 
Dodanie publicznej właściwości | Tak
Usunięcie publicznej właściwości | Nie
Ograniczenie widoczności | Nie
Przeniesienie do klasy - rodzica | Tak
**Chronione właściwości** | 	 
Dodanie chronionej właściwości | Tak
Usunięcie chronionej właściwości | Nie
Ograniczenie widoczności | Nie
Przeniesienie do klasy - rodzica | Tak
**Prywatne właściwości** | 
Dodanie prywatnej właściwości | Tak
Usunięcie prywatnej właściwości | Tak
**Konstruktory** | 
Usunięcie konstruktora | Nie
Ograniczenie widoczności publicznego konstruktora | Nie
Ograniczenie widoczności chronionego konstruktora | Nie
Przeniesienie do klasy - rodzica | Tak
**Publiczne metody** |
Dodanie publicznej metody | Tak
Usunięcie publicznej metody | Nie
Zmiana nazwy | Nie
Ograniczenie widoczności | Nie
Przeniesienie do klasy - rodzica | Tak
Dodanie argumentu bez domyślnej wartości | Nie
Dodanie argumentu z domyślną wartością | Nie
Usunięcie argumentów | Tak, tylko ostatnich
Dodanie domyślnej wartości do argumentu | Nie
Usunięcie domyślnej wartości z argumentu | Nie
Dodanie informacji o typie argumentu | Nie
Usunięcie informacji o typie argumentu | Nie
Zmiana typu argumentu | Nie
Zmiana typu zwracanej wartości | Nie
**Chronione metody** | 	 
Dodanie chronionej metody | Tak
Usunięcie chronionej metody | Nie
Zmiana nazwy | Nie
Ograniczenie widoczności | Nie
Przeniesienie do klasy - rodzica | Tak
Dodanie argumentu bez domyślnej wartości | Nie
Dodanie argumentu z domyślną wartością | Nie
Usunięcie argumentów | Tak, tylko ostatnich
Dodanie domyślnej wartości do argumentu | Nie
Usunięcie domyślnej wartości z argumentu | Nie
Dodanie informacji o typie argumentu | Nie
Usunięcie informacji o typie argumentu | Nie
Zmiana typu argumentu | Nie
Zmiana typu zwracanej wartości | Nie
**Prywatne metody** | 	 
Dodanie prywatnej metody | Tak
Usunięcie prywatnej metody | Tak
Zmiana nazwy | Tak
Dodanie argumentu bez domyślnej wartości | Tak
Dodanie argumentu z domyślną wartością | Tak
Usunięcie argumentu | Tak
Dodanie domyślnej wartości do argumentu | Tak
Usunięcie domyślnej wartości z argumentu | Tak
Dodanie informacji o typie argumentu | Tak
Usunięcie informacji o typie argumentu | Tak
Zmiana typu argumentu | Tak
Zmiana typu zwracanej wartości | Tak
**Statyczne metody** | 
Zmiana niestatycznej metody w statyczną | Nie
Zmiana statycznej metody w niestatyczną | Nie
**Stałe** | 	 
Dodanie stałej | Tak
Usunięcie stałej | Nie
Zmiana wartości stałej | z wyjątkiem obiektów, które będą serializowane. Obowiązkowa dokumentacja w UPGRADE.md.