Przegląd
========

Za każdym razem kiedy aplikacja Yii obsługuje żądanie, przetwarza je w podobny sposób.

1. Użytkownik wykonuje żądanie do [skryptu wejściowego](structure-entry-scripts.md) `web/index.php`.
2. Skrypt wejściowy ładuje [konfigurację](concept-configurations.md) aplikacji i tworzy [instancję aplikacji](structure-applications.md), aby obsłużyć zapytanie.
3. Aplikacja osiąga żądaną [ścieżkę](runtime-routing.md) za pomocą komponentu [żądania](runtime-requests.md) aplikacji.
4. Aplikacja tworzy instancję [kontrolera](structure-controllers.md), który obsłuży żądanie.
5. Kontroler tworzy instancję [akcji](structure-controllers.md) i przetwarza filtry dla akcji.
6. Jeżeli jakikolwiek filtr się nie wykona, akcja zostanie anulowana.
7. Jeżeli wszystkie filtry przejdą, akcja zostaje wykonana.
8. Akcja wczytuje model danych, być może z bazy danych.
9. Akcja renderuje widok dostarczając go z modelem danych.
10. Wyrenderowana zawartość jest zwracana do komponentu [odpowiedzi](runtime-responses.md) aplikacji.
11. Komponent odpowiedzi wysyła wyrenderowaną zawartość do przeglądarki użytkownika.

![Cykl życia żądania](images/request-lifecycle.png)

W tej sekcji opiszemy szczegóły dotyczące niektórych kroków przetwarzania żądania.