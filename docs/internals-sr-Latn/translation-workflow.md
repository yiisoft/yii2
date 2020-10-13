Proces rada u prevođenju
========================

Yii je preveden na više jezika kako bi bio koristan za internacionalne aplikacije i programere. Dve glavne oblasti u kojima je doprinos veoma poželjan jeste dokumentacija i frejmvork poruke.

Frejmvork poruke
----------------

Frejmvork ima dva tipa poruka: izuzeci koji su namenjeni programeru i koje se nikada ne prevode i poruke koje su zapravo vidljive krajnjem korisniku kao na primer validacijske greške.

Da bi započeli rad sa prevodom poruka potrebno je da:

1. Otvorite `framework/messages/config.php` i proverite da li je vaš jezik naveden u `languages`. Ako nije,
   dodajte tu vaš jezik (ne zaboravite da zadržite alfabetički rapored). Format koda jezika treba da prati 
   [IETF jezičku tag specifikaciju](http://en.wikipedia.org/wiki/IETF_language_tag), na primer, `ru`, `zh-CN`.
2. Uđite u `framework` direktorijum i pokrenite `./yii message/extract @yii/messages/config.php --languages=<your_language>`.
3. Prevedite poruke unutar `framework/messages/your_language/yii.php` fajla. Pobrinite se da se fajl sačuva sa UTF-8 enkodingom.
4. [Napravite pull zahtev](https://github.com/yiisoft/yii2/blob/master/docs/internals-sr-Latn/git-workflow.md).

Kako bi  vaš prevod bio ažuran možete pokrenuti komandu `./yii message/extract @yii/messages/config.php --languages=<your_language>` još jednom. Ona će automatski ponovo izvući poruke ostavljajući nepromenjene netaknutim.

U fajlu za prevođenje svaki element niza predstavlja prevod (vrednost) poruke (ključ). Ako je vrednost prazna, poruka se smatra neprevedenom. Poruke koje više ne trebaju prevod će imati svoje prevode zatvorene između para '@@' znaka. Poruke se mogu koristiti i u formatu za množinu. Pogledajte [i18n sekciju uputstva](../guide-sr-Latn/tutorial-i18n.md) za više informacija.

Dokumentacija
-------------

Stavite prevode dokumentacije unutar `docs/<original>-<language>` gde `<original>` je originalno ime dokumentacije kao što je `guide` ili `internals`, a `<language>` je jezički kod od dokumentacije jezika na koji se prevodi. Za rusko uputstvo prevodi se nalaze u `docs/guide-ru`.

Nakon što je inicijalni posao završen možete dobiti šta je promenjeno nakon poslednjeg prevoda fajla korišćenjem specijalne komande unutar `build` direktorijuma:

```
php build translation "../docs/guide" "../docs/guide-ru" "Russian guide translation report" > report_guide_ru.html
```

Ako se bude bunio u vezi composer-a, izvršite `composer install` u samom root-u direktorijumu.
