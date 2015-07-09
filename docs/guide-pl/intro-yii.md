Czym jest Yii
=============

Yii jest wysoko wydajnym, opartym na komponentach frameworkiem PHP do szybkiego programowania 
nowoczesnych stron internetowych. Nazwa Yii (wymawiana [ji:]) oznacza w języku chińskim "prosto i ewolucyjnie".
Może to być również rozumiane jako akronim dla **Yes It Is**!


Dla jakich zastosowań Yii jest najlepszy?
-----------------------------------------

Yii jest frameworkiem ogólnego przeznaczenia, co oznacza, że może być wykorzystany do stworzenia 
każdego rodzaju aplikacji internetowych korzystających z PHP. Z uwagi na architekturę 
opartą na komponentach i zaawansowane wsparcie dla mechanizmów pamięci podręcznej jest on odpowiedni
do tworzenia rozbudowanych aplikacji, takich jak: portale, fora, systemy zarządzania treścią (CMS),
projekty komercyjne (e-sklepy), usługi sieciowe i inne.


Jak wygląda porównanie Yii z innymi frameworkami?
-------------------------------------------------

Jeśli korzystałeś już z innych frameworków, na pewno docenisz, jak Yii wypada na ich tle:

* Jak większość frameworków, Yii wykorzystuje architekturę MVC (Model-Widok-Kontroler) i wspiera organizację kodu zgodną z tym wzorcem.
* Yii opiera się na filozofii, która mówi, że kod powinien być napisany w prosty, ale jednocześnie elegancki sposób. Yii nigdy nie będzie upierać się przy przeprojektowaniu 
kodu jedynie w celu dokładnego trzymania się zasad wzorca projektowego.
* Yii jest w pełni rozwiniętym frameworkiem dostarczającym sprawdzonych i gotowych do użycia funkcjonalności: konstruktorów zapytań
oraz ActiveRecord dla baz danych relacyjnych i NoSQL, wsparcia dla tworzenia RESTful API oraz wielopoziomowych mechanizmów pamięci podręcznej i wielu, wielu innych.
* Yii jest ekstremalnie rozszerzalny. Możesz dostosować lub wymienić praktycznie każdy fragment podstawowego kodu. 
Dodatkowo Yii wykorzystuje architekturę rozszerzeń, dzięki czemu możesz w prosty sposób stworzyć i opublikować swoje własne moduły i widżety.
* Podstawowym celem, do którego Yii zawsze dąży, jest wysoka wydajność.

Yii nie jest efektem pracy pojedynczego programisty - projekt wspiera zarówno [grupa doświadczonych deweloperów][about_yii], jak i ogromna społeczność programistyczna, nieustannie 
przyczyniając się do jego rozwoju. Deweloperzy trzymają rękę na pulsie najnowszych trendów Internetu, za pomocą prostych i eleganckich interfejsów wzbogacając Yii w najlepsze sprawdzone 
rozwiązania i funkcjonalności, dostępne w innych frameworkach i projektach.

 
Wersje Yii
----------

Yii aktualnie dostępny jest w dwóch głównych wersjach: 1.1 i 2.0. Wersja 1.1 jest kodem starszej generacji, obecnie w fazie utrzymaniowej. 
Wersja 2.0 jest całkowicie przepisaną wersją Yii z uwzględnieniem najnowszych protokołów i technologii, takich jak Composer, PSR, przestrzenie nazw, traity i wiele innych.
2.0 reprezentuje aktualną generację frameworka i na niej skupi się głównie praca programistów w ciągu najbliższych lat. 
Ten przewodnik opisuje wersję 2.0.


Wymagania i zależności
----------------------

Yii 2.0 wymaga PHP w wersji 5.4.0 lub nowszej. Aby otrzymać więcej informacji na temat wymagań i indywidualnych funkcjonalności, 
uruchom specjalny skrypt testujący system `requirements.php`, dołączony w każdym wydaniu Yii.

Używanie Yii wymaga podstawowej wiedzy o programowaniu obiektowym w PHP (OOP), ponieważ Yii
jest frameworkiem czysto obiektowym. Yii 2.0 wykorzystuje ostatnie udoskonalenia w PHP, jak 
[przestrzenie nazw](http://www.php.net/manual/pl/language.namespaces.php) i [traity](http://www.php.net/manual/pl/language.oop5.traits.php). 
Zrozumienie tych konstrukcji pomoże Ci szybciej i łatwiej rozpocząć pracę z Yii 2.0.

