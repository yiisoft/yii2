Pamięć podręczna
================

Mechanizmy wykorzystujące pamięć podręczną pozwalają na poprawienie wydajności aplikacji sieciowej w tani i efektywny sposób. 
Zapisanie statycznych danych w pamięci podręcznej, zamiast generowania ich od podstaw przy każdym wywołaniu, pozwala na znaczne zaoszczędzenie czasu odpowiedzi aplikacji.

Zapis pamięci podręcznej może odbywać się na wielu poziomach i w wielu miejscach aplikacji. Po stronie serwera, na niskim poziomie, 
można wykorzystać pamięć podręczną do zapisania podstawowych danych, takich jak zbiór informacji o najnowszych artykułach pobieranych z bazy danych. 
Na wyższym poziomie, pamięci podręcznej można użyć do przechowania części bądź całości strony www, na przykład w postaci rezultatu wyrenderowania 
listy ww. najświeższych artykułów. Po stronie klienta, pamięć podręczna HTTP przeglądarki może zapisać zawartość ostatnio odwiedzonej strony.

Yii wpiera wszystkie te mechanizmy zapisu w pamięci podręcznej:

* [Pamięć podręczna danych](caching-data.md)
* [Pamięć podręczna fragmentów](caching-fragment.md)
* [Pamięć podręczna stron](caching-page.md)
* [Pamięć podręczna HTTP](caching-http.md)
