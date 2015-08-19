Przegl�d
========
      
Za ka�dym razem kiedy aplikacja Yii obs�uguje ��danie, przetwarza je w podobny spos�b.      

1. U�ytkownik wykonuje ��danie do [skryptu wej�ciowego](structure-entry-scripts.md) `web/index.php`.
2. Skrypt wej�ciowy �aduje [konfiguracj�](concept-configurations.md) aplikacji i tworzy
   [instancj� aplikacji](structure-applications.md) aby obs�u�y� zapytanie.
3. Aplikacja osi�ga ��dan� [�cie�k�](runtime-routing.md) za pomoc� komponentu 
   [��dania](runtime-requests.md) aplikacji.
4. Aplikacja tworzy instancj� [kontrolera](structure-controllers.md), kt�ry obs�u�y ��danie.
5. Kontroler tworzy instancj� [akcji](structure-controllers.md) i wykonuje filtry dla akcji.
6. Je�eli jakikolwiek filtr si� nie wykona, akcja zostanie anulowana.
7. Je�eli wszystkie filtry przejd�, akcja zostaje wykonana.
8. Akcja wczytuje model danych, by� mo�e z bazy danych.
9. Akcja renderuje widok dostarczaj�c go z modelem danych.
10. Wyrenderowana zawarto�� jest zwracana do komponentu [odpowiedzi](runtime-responses.md) aplikacji. 
11. Komponent odpowiedzi wysy�a wyrenderowan� zawarto�� do przegl�darki u�ytkownika.
Ten diagram pokazuje jak aplikacja obs�uguje ��danie.

![Request Lifecycle](images/request-lifecycle.png)

W tej sekcji opiszemy szczeg�y dotycz�ce niekt�rych krok�w przetwarzania ��dania.