Git proces rada za Yii 2 saradnike
===================================

Želite da doprinesete Yii razvoju? Divno! Kako bi povećali šanse da vaše izmene budu prihvaćene što pre, molimo da 
ispratite sledeće korake. Ako ste novi sa Git-om
i GitHub-om, možda bi želeli da prvo pogledate [GitHub pomoć](https://help.github.com/), [probate Git](https://try.github.com)
ili naučite nešto novo o [Git internom modelu podataka](https://nfarina.com/post/9868516270/git-is-simpler).

Pripremite vaše razvojno okruženje
------------------------------------

Sledeći koraci će napraviti razvojno okruženje za Yii, koje možete koristiti kako bi radili
na baznom kodu Yii frejmvorka. Ovi se koraci trebaju uraditi samo jednom.

### 1. [Forkujte](https://help.github.com/fork-a-repo/) Yii repozitorijum na GitHub-u i klonirajte vaš fork na vašem razvojnom okruženju

```
git clone git@github.com:VASE-GITHUB-KORISNICKO-IME/yii2.git
```

Ako imate problema sa podešavanjem Git-a sa GitHub-om na Linux-u ili dobijate greške tipa "Permission Denied (publickey)",
onda morate [podesiti vašu Git instalaciju da radi sa GitHub-om](https://help.github.com/linux-set-up-git/)

### 2. Dodajte glavni Yii repozitorijum kao dodatni git repozitorijum sa nazivom "upstream"

Locirajte se u direktorijum gde ste klonirali Yii, podrazumevano, "yii2" direktorijum. Nakon toga izvršite sledeću komandu:

```
git remote add upstream https://github.com/yiisoft/yii2.git
```

### 3. Pripremite okruženje za testiranje

Sledeći koraci nisu neophodni ako želite da radite samo na prevodima i dokumentaciji.

- pokrenite `composer update` kako bi instalirali neophodne pakete (podrazumeva se da imate [composer instaliran globalno](https://getcomposer.org/doc/00-intro.md#globally)).
- pokrenite `php build/build dev/app basic` kako bi klonirali "basic" aplikaciju i instalirali composer neophodne pakete "basic" aplikacije.
  Ova komanda će instalirati spoljne composer pakete i ulinkovati yii2 repozitorujum sa trenutnim preuzetim repozitorijumom, tako da imate samo jednu instancu celog instaliranog koda.
  
  Ponovite postupak za "advanced" aplikaciju ako je potrebno, pokretanjem: `php build/build dev/app advanced`.
  
  Ova komanda će se takođe koristiti da bi se osvežili potrebni paketi, ona pokreće `composer update` interno.

**Sada ste spremni za rad na Yii 2 frejmvorku.**

Sledeći koraci su neobavezni.

### Unit testovi

Možete izvršiti unit testove pokretanjem `phpunit` unutar root direktorijuma repozitorijuma. Ako nemate phpunit instaliran globalno možete pokrenuti `php vendor/bin/phpunit` umesto toga.

Neki testovi zahtevaju dodatne baze podataka da budu postavljene i podešene. Možete napraviti `tests/data/config.local.php` fajl kako bi pregazili podešavanja koja su definisana unutar `tests/data/config.php` fajla.

Možete ograničiti testove na grupu testova na kojima radite, na primer, da pokrenete testove za samo validaciju i redis koristite `phpunit --group=validators,redis`. Listu dostupnih grupa možete dobiti pokretanjem `phpunit --list-groups`. 

### Ekstenzije

Kako bi radili na ekstenzijama morate klonirati repozitorijum ekstenzije. Napravili smo komandu koja može to uraditi umesto vas:

```
php build/build dev/ext <extension-name>
```

gde je `<extension-name>` ime ekstenzije, na primer `redis`.

Ako želite da testirate ekstenziju u jednom od aplikacijskih šablona, samo dodajte repozitorijum u `composer.json` aplikacije kao što bi to radili normalno, na primer dodali bi `"yiisoft/yii2-redis": "~2.0.0"` unutar`require` sekcije za "basic" aplikaciju.
Pokretanjem `php build/build dev/app basic` ćete instalirati ekstenziju i njene neophodne pakete i ulinkovaće se `extensions/redis` direktorijum kako ne bi radili u vendor direktorijumu nego u yii2 repozitorijumu direktno.


Rad na bagovima i poboljšanjima
-------------------------------

Pošto je razvojno okruženje spremno kako je objašnjeno iznad možete započeti rad na nekoj novoj funkcionalnosti ili bagu.

### 1. Postarajte se da je problem prijavljen za bug na kom radite ako zahteva značajniji rad na ispravljanju

Sve nove funkcionalnosti i bugovi bi trebali imati povezanu temu koju bi koristili kao jedinstvenu tačku za diskusiju i dokumentaciju. Bacite pogled na postojeću listu koja ima temu koja se poklapa sa onim na čemu bi želeli da radite. Ako pronađete da tema već postoji u listi, onda ostavite komentar na toj temi u kome iskažite da želite da radite na tome. Ako ne pronađete postojeću temu/problem koja se poklapa sa onim na čemu bi želeli da radite, molimo da [postavite temu/prijavite problem](report-an-issue.md) ili napravite pull zahtev direktno ako nije komplikovano rešenje. Na ovaj način, tim će moći da pogleda vaše rešenje i dodatno vas uputi.


> Za sitne izmene ili dokumentacijske probleme ili za jednostavnije probleme, nije potrebno praviti posebnu temu, pull zahtev je više nego dovoljan u ovom slučaju.

### 2. Dohvatite poslednji kod sa glavne Yii grane

```
git fetch upstream
```

Trebali bi krenuti uvek od ove tačke kada krećete sa radom kako bi se osigurali da radite sa poslednjim kodom.

### 3. Napravite novu granu za novu funkcionalnost/rešenje baga baziranu na trenutnoj Yii master grani

> Ovo je jako važno zato što nećete moći da pošaljete više od jednog pull zahteva sa vašeg naloga ako koristite master.

Svako posebno rešenje baga ili izmena bi trebala da se nalazi u svojoj posebnoj grani. Imena grana trebaju biti opisna i u imenu sadrže broj teme na koju se odnosi. Ako ne radite na ispravci nekog određenog probelma, prekočite broj teme. Na primer:

```
git checkout upstream/master
git checkout -b 999-IME-VASE-GRANE
```

### 4. Bacite se na posao, napišite vaš kod

Potrudite se da funkcioniše :)

Unit testovi su uvek dobrodošli. Testiranje i dobro pokriven kod značajno pojednostavljuje proveru koda.
Neuspeli unit testovi kao opis teme se takođe prihvataju.

### 5. Izmenite CHANGELOG

Izmenite CHANGELOG fajl kako bi uključili vašu izmenu, trebali bi je uneti na vrhu fajla ispod "Work in progress" naslova, linija u CHNAGELOG fajlu bi trebalo da izgleda nešto nalik sledećem:

```
Bug #999: opis vaše ispravke (vaše ime)
Enh #999: opis vašeg poboljšanja (vaše ime)
```

`#999` je broj teme na koju se `Bug` ili `Enh` odnosi.
CHANGELOG bi trebao biti grupisan po tipu (`Bug`,`Enh`) i sortiran po broju teme.

Za veoma male izmene, na primer, greške u tekstu, izmene na dokumentaciji, nije potrebno menjati CHANGELOG.

### 6. Komitujte promene

dodajte fajlove/promene koje želite da [komitujete](https://git.github.io/git-reference/basic/#add) sa

```
git add path/to/my/file.php
```

Možete koristit `-p` opciju kako bi izabrali izmene koje želite da komitujete.

Komitujte vaše izmene sa opisnom porukom komita. Potrudite se da napomente broj teme sa `#XXX` kako bi GitHub automatski ulinkovao vaš komit sa temom:

```
git commit -m "Ovde napišite kratak opis promene koja ispravlja #999"
```

### 7. Preuzmite poslednji Yii kod sa upstream-a u vašu granu

```
git pull upstream master
```

Ovo nas osigurava da imamo poslednji kod u vašoj lokalnoj grani pre nego napravimo pull zahtev. Ako postoje konfilkti, trebali bi ih ispraviti i komitovati izmene ponovo. Na ovaj način biće lakše Yii timu da poveže izmene sa jednim klikom.

### 8. Nakon razrešenih svih konflikata, postavite vaš kod na GitHub

```
git push -u origin 999-IME-VASE-GRANE
```

Parametar `-u` će osigurati da će vaša grana moći da šalje pull i push zahteve sa GitHub grane. To znači da ako pozovete `git push` sledeći put će znati gde treba kod da se pošalje. Ovo je korisno ako budete hteli da kasnije dodate više komitova u jednom pull zahtevu.

### 9. Otvorite [pull zahtev](https://help.github.com/articles/creating-a-pull-request-from-a-fork/) na upstream-u.

Posetite vaš repozitorijum na Github-u i kliknite na "Pull Request", izaberite vašu granu na desnoj strani i unesite neki opis u polje za komentar. Kako bi povezali pull zahtev sa temom unesite bilo gde u komentaru `#999` gde 999 je broj teme.

> Imajte na umu da svaki pull zahtev treba ispraviti samo jednu stvar. Za više, nevezanih izmena, molimo koristite više pull zahteva.

### 10. Neko će pregledati vaš kod

Neko će pregledati vaš kod, i možda će se od vas tražiti još neke izmene, ako je tako idite na korak #6 (ne morate da pravite novi pull zahtev ako je vaš trenutni još uvek otvoren). Ako je vaš kod prihvaćen biće spojen u glavnu granu i postaće deo sledećeg Yii izdanja. Ako to nije slučaj, ne budite obeshrabreni, različiti ljudi žele različite funkcionalnosti i Yii ne može biti sve svima, vaš kod će biti dostupan na GitHub-u kao referenca za ljude kojima je to potrebno.

### 11. Čišćenje

Nakom što je vaš kod ili prihvaćen ili odbijen možete obrisati vaše grane na kojima ste radili na vašem lokalnom repozitorijumu i `origin`.

```
git checkout master
git branch -D 999-IME-VASE-GRANE
git push origin --delete 999-IME-VASE-GRANE
```

### Napomena:

Kako bi rano otkrili regresije u Yii kodu prilikom svake integracije na GitHub-u pokreće se [Travis CI](https://travis-ci.com) kako bi se radilo testiranje. Pošto Yii tim ne želi da preoptereti ovaj servis,
[`[ci skip]`](https://docs.travis-ci.com/user/customizing-the-build/#Skipping-a-build) će biti uključen prilikom svake integracije ako pull zahtev:

* utiče samo na javascript, css i slike,
* osvežava dokumentaciju,
* menja samo fiksne stringove (npr. izmene u prevodu)

Na ovaj način će Travis započinjati testiranje samo izmena koje nisu prvenstveno pokrivene testovima.

### Pregled komandi (za napredne saradnike)

```
git clone git@github.com:VASE-GITHUB-KORISNICKO-IME/yii2.git
git remote add upstream https://github.com/yiisoft/yii2.git
```

```
git fetch upstream
git checkout upstream/master
git checkout -b 999-IME-VASE-GRANE

/* bacite se na posao, izmenite changelog ako je potrebno */

git add path/to/my/file.php
git commit -m "Ovde napišite kratak opis promene koja ispravlja #999"
git pull upstream master
git push -u origin 999-IME-VASE-GRANE
```
