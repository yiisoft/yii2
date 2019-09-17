Cykl tworzenia tłumaczenia
==========================

Yii jest przetłumaczony na wiele języków, dzięki czemu może być używany przez międzynarodowe grono deweloperów. Dwa główne obszary, 
gdzie kontrybucje tłumaczeń są mile widziane, to dokumentacja i komunikaty frameworka.

Komunikaty frameworka
---------------------

Framework posługuje się dwoma typami komunikatów: wyjątkami, przeznaczonymi dla deweloperów, które nie są nigdy tłumaczone, 
oraz pozostałymi, zwykle widocznymi dla użytkowników końcowych, takimi jak błędy walidacji.

Aby rozpocząć tłumaczenie komunikatów:

1. Sprawdź plik `framework/messages/config.php` i upewnij się, że Twój język jest wymieniony w sekcji `languages`. 
   Jeśli nie, dodaj go tam (pamiętaj, że lista powinna być posortowana alfabetycznie). Format kodu języka powinien być zgodny 
   ze [specyfikacją IETF](http://en.wikipedia.org/wiki/IETF_language_tag) (przykładowo `ru`, `zh-CN`).
2. Przejdź do folderu `framework` i uruchom `./yii message/extract @yii/messages/config.php --languages=<twoj_jezyk>`.
3. Przetłumacz komunikaty w pliku `framework/messages/twoj_jezyk/yii.php`. Upewnij się, że zapisujesz plik z kodowaniem UTF-8.
4. [Wyślij prośbę o dołączenie kodu](git-workflow.md).

Aby Twoje tłumaczenia pozostawały aktualne, możesz uruchomić `./yii message/extract @yii/messages/config.php --languages=<twoj_jezyk>` ponownie po pewnym czasie. 
Komunikaty zostaną jeszcze raz wyekstraktowane, pozostawiając w pliku te, które się nie zmieniły.

W pliku tłumaczeń każdy element tablicy reprezentuje przetłumaczony (wartość) komunikat (klucz). Jeśli wartość jest pusta,
komunikat jest uznawany za nieprzetłumaczony. Tłumaczenia komunikatów, które nie są już wymagane, ujęte są w pary znaków '@@'. 
Łańcuch znaków komunikatu może być zapisany w formacie liczb mnogich. Więcej szczegółów na ten temat znajdziesz w 
[sekcji i18n przewodnika](../guide/tutorial-i18n.md).

Dokumentacja
------------

Umieść tłumaczenia dokumentacji w folderze `docs/<pierwotny_folder>-<jezyk>`, gdzie `<pierwotny_folder>` jest nazwą folderu 
zawierającego oryginalną dokumentację, jak np. `guide` lub `internals`, a `<jezyk>` jest kodem języka użytego do tłumaczenia. 
Dla polskiego tłumaczenia przewodnika folder ten to `docs/guide-pl`.

Po tym jak nowe tłumaczenia zostaną już dodane, możesz sprawdzić co się zmieniło od ostatniego tłumaczenia pliku używając 
specjalnej komendy w folderze `build`:

```
php build translation "../docs/guide" "../docs/guide-pl" "Raport tłumaczeń dla polskiego przewodnika" > report_guide_pl.html
```

Jeśli zobaczysz komunikaty związane z composerem, uruchom `composer install` w źródłowym folderze.

Aby zapoznać się z informacjami na temat składni dokumentacji i stylu użytego w przewodniku, przejdź do [documentation_style_guide.md](../documentation_style_guide.md).
