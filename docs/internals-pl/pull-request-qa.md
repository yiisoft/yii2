Zapewnienie jakości kodu w prośbie o połączenie
===============================================

Podczas sprawdzania czy PR może być scalony lub nie, następujące kryteria powinny być również wzięte pod uwagę:

- Powinno istnieć zgłoszenie powiązane z PR lub PR powinien zawierać dobry opis tego, co dodaje lub zmienia.
- Testy jednostkowe. Nieobowiązkowe, ale bardzo cenione. Powinny się nie udać przy braku kodu, który PR wprowadza. 
- Wpis w CHANGELOG powinien być obecny w sekcji następnego wydania, posortowany według typu i numeru zgłoszenia.
  Pseudonimy autorów powinny też być dodane.
- [Styl kodu](core-code-style.md) oraz [styl widoków](view-code-style.md) powinien być bez zastrzeżeń. Poprawki tego dotyczące można 
  wprowadzić w trakcie scalania, jeśli bardziej odpowiada to osobie scalającej.
