Testowanie
==========

Testowanie jest istotnym elementem produkcji każdego oprogramowania. Niezależnie, czy jesteśmy tego świadomi, czy też nie, testy przeprowadzamy nieustannie.
Dla przykładu, kiedy napiszemy klasę w PHP, możemy debugować ją krok po kroku lub po prostu użyć wyrażeń jak echo lub die, aby sprawdzić, czy implementacja 
działa zgodnie z naszym początkowym planem. W przypadku aplikacji web wprowadzamy testowe dane w formularzach, aby upewnić się, że strona odpowiada tak, jak powinna.

Proces testowania może zostać zautomatyzowany, dzięki czemu za każdym razem, kiedy musimy coś sprawdzić, wystarczy wywołać kod, który zrobi to za nas. 
Kod, który weryfikuje zgodność wyniku z planowaną odpowiedzią, jest nazywany testem, a proces jego tworzenia i późniejszego wykonania jest nazywany testowaniem zautomatyzowanym, 
co jest głównym tematem tych rozdziałów.


Tworzenie kodu z testami
------------------------

Tworzenie kodu opartego na testach (Test-Driven Development, TDD) i opartego na zachowaniach (Behavior-Driven Development, BDD) jest podejściem deweloperskim 
opierającym się na opisywaniu zachowania fragmentu kodu lub też całej jego funkcjonalności jako zestawu scenariuszy lub testów przed napisaniem właściwego kodu
i dopiero potem stworzeniu implementacji, która pozwoli na poprawne przejście testów, spełniających zadane kryteria.

Proces tworzenia funkcjonalności wygląda następująco:

- Stwórz nowy test, opisujący funkcjonalność do zaimplementowania.
- Uruchom nowy test i upewnij się, że zakończy się błędem. To właściwe zachowanie, ponieważ nie ma jeszcze implementacji funkcjonalności.
- Napisz prosty kod, który przejdzie poprawnie nowy test.
- Uruchom wszystkie testy i upewnij się, że wszystkie zakończą się poprawnie.
- Ulepsz kod, sprawdzając czy testy wciąż są zdane.

Po zakończeniu proces jest powtarzany dla kolejnej funkcjonalności lub ulepszenia. Jeśli istniejąca funkcjonalność ma być zmodyfikowana,
testy powinny być również zmienione.

> **Wskazówka**: Jeśli czujesz, że tracisz czas, przeprowadzając dużo krótkich i prostych iteracji, spróbuj objąć testowym scenariuszem więcej działań,
> aby sprawdzić więcej kodu, przed ponownym uruchomieniem testów. Jeśli debugujesz zbyt dużo, spróbuj zrobić dokładnie na odwrót.

Powodem tworzenia testów przed jakąkolwiek implementacją, jest możliwość skupienia się na tym, co chcemy osiągnąć, zanim przystąpimy do "w jaki sposób to zrobić".
Zwykle prowadzi to do stworzenia lepszej warstwy abstrakcji i łatwiejszej obsługi testów w przypadku poprawek funkcjonalności.

Podsumowując, zalety takiego projektowania są następujące:

- Pozwala na skupienie się na pojedynczej rzeczy na raz, dzięki czemu pozwala na lepsze planowanie i implementacje.
- Obejmuje testami więcej funkcjonalności w większym stopniu, co oznacza, że jeśli testy zakończyły się poprawnie jest spore prawdopodobieństwo, że wszystko działa poprawnie.

Na dłuższą metę przynosi to zwykle efekt w postaci mnóstwa oszczędzonego czasu i problemów.

> **Wskazówka**: Jeśli chcesz dowiedzieć się więcej na temat reguł ustalania wymagań dla oprogramowania i modelowania istoty tego rozdziału, 
> warto zapoznać się z domenowym podejściem do tworzenia aplikacji [(Domain Driven Development, DDD)](https://pl.wikipedia.org/wiki/Domain-Driven_Design).

Kiedy i jak testować
--------------------

Podejście typu "testy najpierw" opisane powyżej, ma sens w przypadku długofalowych i relatywnie skomplikowanych projektów i może być przesadne w przypadku prostszych. 
Przesłanki, kiedy testy są odpowiednie są następujące:

- Projekt jest już duży i skomplikowany.
- Wymagania projektowe zaczynają być skomplikowane. Projekt wciąż się powiększa.
- Projekt jest planowany jako długoterminowy.
- Koszty potencjalnych błędów są zbyt duże.

Nie ma nic złego w tworzeniu testów obejmujących zachowania istniejących implementacji.

- Projekt jest oparty starszym kodzie i stopniowo przepisywany.
- Projekt, nad którym masz pracować, nie ma w ogóle testów.

W niektórych przypadkach jakakolwiek forma automatycznego testu może być nadmiarowa:

- Projekt jest prosty i nie jest rozbudowywany.
- Projekt jest jednorazowym zadaniem, które nie będzie rozwijane.

Pomimo tego, jeśli masz na to czas, automatyzacja testowania jest również dobrym pomysłem.


Biblioteka
-------------

- Test Driven Development: By Example - Kent Beck. (ISBN: 0321146530)
