Automatizacija
==============

Postoje taskovi koji se rade automatski kada radite sa Yii frejmvorkom:

- Generisanje mape klasa `classes.php` koji se nalazi u rutu frejmvork direktorijuma.
  Pokrenite `./build/build classmap` kako bi izgenerisali fajl.

- Generisanje `@property` anotacija u fajlovima sa klasama koje opisuju osobine koje su uveli geteri i seteri.
  Pokrenite `./build/build php-doc/property` kako bi ih osvežili.

- Ispravljanje stila pisanja koda i ostalih sitnijih problema u phpdoc komentarima.
  Pokrenite `./build/build php-doc/fix` kako bi ih ispravili.
  Proverite izmene pre njihovog komitovanja zato što se mogu desiti neželjene promene zato što komanda nije idealna.
  Možete koristiti `git add -p` kako bi pregledali izmene.