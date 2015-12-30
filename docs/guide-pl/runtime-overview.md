Przegl±d
========
      
Za ka¿dym razem kiedy aplikacja Yii obs³uguje ¿±danie, przetwarza je w podobny sposób.      

1. U¿ytkownik wykonuje ¿±danie do [skryptu wej¶ciowego](structure-entry-scripts.md) `web/index.php`.
2. Skrypt wej¶ciowy ³aduje [konfiguracjê](concept-configurations.md) aplikacji i tworzy
   [instancjê aplikacji](structure-applications.md) aby obs³u¿yæ zapytanie.
3. Aplikacja osi±ga ¿±dan± [¶cie¿kê](runtime-routing.md) za pomoc± komponentu 
   [¿±dania](runtime-requests.md) aplikacji.
4. Aplikacja tworzy instancjê [kontrolera](structure-controllers.md), który obs³u¿y ¿±danie.
5. Kontroler tworzy instancjê [akcji](structure-controllers.md) i wykonuje filtry dla akcji.
6. Je¿eli jakikolwiek filtr siê nie wykona, akcja zostanie anulowana.
7. Je¿eli wszystkie filtry przejd±, akcja zostaje wykonana.
8. Akcja wczytuje model danych, byæ mo¿e z bazy danych.
9. Akcja renderuje widok dostarczaj±c go z modelem danych.
10. Wyrenderowana zawarto¶æ jest zwracana do komponentu [odpowiedzi](runtime-responses.md) aplikacji. 
11. Komponent odpowiedzi wysy³a wyrenderowan± zawarto¶æ do przegl±darki u¿ytkownika.
Ten diagram pokazuje jak aplikacja obs³uguje ¿±danie.

![Request Lifecycle](images/request-lifecycle.png)

W tej sekcji opiszemy szczegó³y dotycz±ce niektórych kroków przetwarzania ¿±dania.