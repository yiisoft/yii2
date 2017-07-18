Decyzje projektowe
==================

Ten dokument zawiera listę decyzji projektowych, które podjęliśmy po długich debatach. O ile nie ma bardzo ważnych powodów ku temu, 
aby działać inaczej, postanowienia te powinny być zawsze w mocy, dla zachowania spójności. Każda zmiana poniższych decyzji 
powinna być najpierw zatwierdzona przez głównych deweloperów.

1. **[Kiedy wspierać aliasy ścieżek dostępu](https://github.com/yiisoft/yii2/pull/3079#issuecomment-40312268)**
   powinniśmy wspierać tworzenie aliasów ścieżej dla właściwości, które są konfigurowalne, ponieważ używanie aliasów ścieżek 
   w konfiguracjach jest bardzo wygodne. W pozostałych przypadkach powinniśmy ograniczyć wsparcie dla aliasów.
2. **Kiedy tłumaczyć komunikaty**
   komunikaty powinny być tłumaczone, kiedy są prezentowane użytkownikowi końcowemu nie będącemu deweloperem i są wobec tego dla niego 
   zrozumiałe. Komunikaty statusów HTTP, wyjątki w kodzie itp. nie powinny być tłumaczone. Komunikaty konsoli prezentowane są zawsze 
   w języku angielskim z powodu potencjalnych problemów ze stroną kodową znaków.
3. **[Dodawanie wsparcia dla nowych klientów uwierzytelniających](https://github.com/yiisoft/yii2/issues/1652)**
   W celu łatwiejszej utrzymalności kodu, nie będziemy dodawać żadnych dodatkowych klientów uwierzytelniających do bazowego rozszerzenia. 
   Dodatkowe klienty powinny być dodane w rozszerzeniach użytowników. 
4. **Podczas używania domknięć** zalecane jest, aby **podać wszystkie przekazywane parametry** w sygnaturze funkcji, nawet jeśli 
   nie wszystkie z nich są wykorzystane. Dzięki temu modyfikowanie i kopiowanie kodu jest znacznie łatwiejsze, ponieważ wszystkie 
   informacje są bezpośrednio widoczne i nie ma konieczności sprawdzania w dokumentacji, które parametry są w ogóle dostępne. 
   ([#6584](https://github.com/yiisoft/yii2/pull/6584), [#6875](https://github.com/yiisoft/yii2/issues/6875))
5. Preferowane jest używanie typu **int zamiast unsigned int** w schematach baz danych. Użycie int ma tę zaletę, że może być 
   prezentowany w PHP jako typ integer.
   W przypadku unsigned i 32 bitowych systemów, musielibyśmy używać typu string do tej prezentacji.
   Dodatkowo, pomimo tego, że unsigned int podwaja jego zakres, jeśli tabela wymaga tak dużych liczb, bezpieczniej jest używać typu 
   bigint lub mediumint, niż polegać na unsigned.
   <https://github.com/yiisoft/yii/pull/1923#issuecomment-11881967>
6. [Klasy pomocnicze vs oddzielne niestatyczne klasy](https://github.com/yiisoft/yii2/pull/12661#issuecomment-251599463)
7. **Łańcuchowanie metod setterów** powinno być unikane, jeśli w klasie znajdują się metody zwracające ważne wartości. 
   Łańcuchowanie może być wspierane, jeśli klasa jest typu budującego, gdzie wszystkie settery modyfikują jedynie wewnętrzne stany: https://github.com/yiisoft/yii2/issues/13026
   