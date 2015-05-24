Git proces rada za Yii 2 saradnike
===================================

Želite da doprinesete Yii razvoju? Divno! Kako bi povećali šanse da vaše izmene budu prihvaćene što pre, molimo da 
ispratite sledeće korake. Ako ste novi sa Git-om
i GitHub-om, možda bi želeli da prvo pogledate [GitHub pomoć](http://help.github.com/), [probate Git](https://try.github.com)
ili naučite nešto novo o [Git internom modelu podataka](http://nfarina.com/post/9868516270/git-is-simpler).

Pripremite vaše razvojno okruženje
------------------------------------

Sledeći koraci će napraviti razvojno okruženje za Yii, koje možete koristiti kako bi radili
na baznom kodu Yii frejmvorka. Ovi se koraci trebaju uraditi samo jednom.

### 1. [Forkujte](http://help.github.com/fork-a-repo/) Yii repozitorijum na GitHub-u i klonirajte vaš fork na vešem razvojnom okruženju

```
git clone git@github.com:VASE-GITHUB-KORISNICKO-IME/yii2.git
```

Ako imate problema sa podešavanjem Git-a sa GitHub-om na Linux-u ili dobijate greške tipa "Permission Denied (publickey)",
onda morate [podesiti vašu Git instalaciju da radi sa GitHub-om](http://help.github.com/linux-set-up-git/)

### 2. Dodajte glavni Yii repozitorijum kao dodatni git repozitorijum sa nazivom "upstream"

Locirajte se u direktorijum gde ste klonirali Yii, podrazumevano, "yii2" direktorijum. Nakon toga izvršite sledeću komandu:

```
git remote add upstream git://github.com/yiisoft/yii2.git
```

### 3. Pripremite okruženje za testiranje

Sledeći koraci nisu neophodni ako želite da radite samo na prevodima i dokumentaciji.

- pokrenite `composer update` kako bi instalirali neophodne pakete (podrazumeva se da imate [composer instaliran globalno](https://getcomposer.org/doc/00-intro.md#globally)).
- pokrenite `php build/build dev/app basic` kako bi klonirali "basic" aplikaciju i instalirali composer neophonde pakete "basic" aplikacije.
  Ova komanda će instlirati spoljne composer pakete i ulinkovati yii2 repozitorujum sa trenutnim preuzetim repozitorijumom, tako da imate samo jednu instancu celog instaliranog koda.
  
  Ponovite postupak za "advanced" aplikaciju ako je potrebno, pokretanjem: `php build/build dev/app advanced`.
  
  Ova komanda će se takođe koristiti da bi se osvežili potrebni paketi, ona pokreće `composer update` interno.

**Sada ste spremni za rad na Yii 2 frejmvorku.**

Sledeći koraci su neobavezni.

### Unit testovi

Možete izvršiti unit testove pokretanjem `phpunit` unutr root direktorijuma repozitorijuma. Ako nemate phpunit instaliran globalno možete pokrenuti `php vendor/bin/phpunit` umesto toga.

Neki testovi zahtevaju dodatne baze podataka da budu postavljene i podešene. Možete napraviti  `tests/data/config.local.php` fajl kako bi pregazili podešavanja koja su definisana unutar `tests/data/config.php` fajla.

Možete ograničiti testove na grupu testova na kojima radite, na primer, da pokrenete testove za samo validaciju i redis koristite `phpunit --group=validators,redis`. Listu dostupnih grupa možete dobiti pokretanjem `phpunit --list-groups`. 

### Ekstenzije

Kako bi radili na ekstenzijama morate klonirati repozitorijum ekstenzije. Napravili smo komandu koja može to uraditi umesto vas:

```
php build/build dev/ext <extension-name>
```

gde je `<extension-name>` ime ekstenzije, na primer `redis`.

Ako želite da testirate ekstenziju u jednom od aplikacijskih šablona, samo dodajte repozitorijum u `composer.json` aplikacije kao što bi to radili normalno, na primer dodali bi `"yiisoft/yii2-redis": "*"` unutar`require` sekcije za "basic" aplikaciju.
Pokretanjem `php build/build dev/app basic` ćete instalirati ekstenziju i njene neophonde pakete i ulinkovaće se `extensions/redis` direktorijum kako ne bi radili u vendor direktorijumu nego u yii2 repozitorijumu direktno.


