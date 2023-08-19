Migracje bazy danych
====================

W czasie rozwoju i utrzymywania aplikacji zasilanej danymi z bazy danych, struktura tej ostatniej ewoluuje podobnie jak
sam kod źródłowy. Przykładowo, rozbudowując aplikację konieczne jest dodanie nowej tabeli, lub też już po wydaniu aplikacji 
na serwerze produkcyjnym przydałby się indeks, aby poprawić wydajność zapytania itd. Zmiana struktury bazy danych często 
pociąga za sobą zmiany w kodzie źródłowym, dlatego też Yii udostępnia funkcjonalność tak zwanych *migracji bazodanowych*,
która pozwala na kontrolowanie zmian w bazie danych (*migracji*).

Poniższe kroki pokazują, jak migracje mogą być wykorzystane przez zespół deweloperski w czasie pracy:

1. Tomek tworzy nową migrację (np. dodaje nową tabelę, zmienia definicję kolumny, itp.).
2. Tomek rejestruje ("commit") nową migrację w systemie kontroli wersji (np. Git, Mercurial).
3. Mariusz uaktualnia swoje repozytorium z systemu kontroli wersji i otrzymuje nową migrację.
4. Mariusz dodaje migrację do swojej lokalnej bazy danych, dzięki czemu synchronizuje ją ze zmianami, które wprowadził 
   Tomek.

A poniższe kroki opisują w skrócie jak stworzyć nowe wydanie z migracją bazy danych na produkcji:

1. Rafał tworzy tag wydania dla repozytorium projektu, który zawiera nowe migracje bazy danych.
2. Rafał uaktualnia kod źródłowy na serwerze produkcyjnym do otagowanej wersji.
3. Rafał dodaje zebrane nowe migracje do produkcyjnej bazy danych.

Yii udostępnia zestaw narzędzi konsolowych, które pozwalają na:

* utworzenie nowych migracji;
* dodanie migracji;
* cofnięcie migracji;
* ponowne zaaplikowanie migracji;
* wyświetlenie historii migracji i jej statusu.

Powyższe narzędzia są dostępne poprzez komendę `yii migrate`. W tej sekcji opiszemy szczegółowo w jaki sposób z nich 
korzystać. Możesz również zapoznać się ze sposobem użycia narzędzi w konsoli za pomocą komendy pomocy `yii help migrate`.

> Tip: Migracje mogą modyfikować nie tylko schemat bazy danych, ale również same dane, a także mogą służyć do innych zadań
  jak tworzenie hierarchi kontroli dostępu dla ról (RBAC) lub czyszczenie pamięci podręcznej.

> Note: Modyfikowanie danych w migracji zwykle jest znacznie prostsze, jeśli użyje się do tego klas 
  [Active Record](db-active-record.md), dzięki logice już tam zaimplementowanej. Należy jednak pamiętać, że logika 
  aplikacji jest podatna na częste zmiany, a naturalnym stanem kodu migracji jest jego stałość - w przypadku zmian w 
  warstwie Active Record aplikacji ryzykujemy zepsucie migracji, które z niej korzystają. Z tego powodu kod migracji
  powinien być utrzymywany niezależnie od pozostałej logiki aplikacji.


## Tworzenie migracji <span id="creating-migrations"></span>

Aby utworzyć nową migrację, uruchom poniższą komendę:

```
yii migrate/create <nazwa>
```

Wymagany argument `nazwa` przekazuje zwięzły opis migracji. Przykładowo, jeśli migracja ma dotyczyć utworzenia nowej 
tabeli o nazwie *news*, możesz użyć jako argumentu `create_news_table` i uruchomić komendę:

```
yii migrate/create create_news_table
```

> Note: Argument `nazwa` zostanie użyty jako część nazwy klasy nowej migracji i z tego powodu powinien składać się tylko
  z łacińskich liter, cyfr i/lub znaków podkreślenia.

Powyższa komenda utworzy nowy plik klasy PHP o nazwie podobnej do `m150101_185401_create_news_table.php` w folderze 
`@app/migrations`. Plik będzie zawierał poniższy kod, gdzie zadeklarowany jest szkielet klasy `m150101_185401_create_news_table`:

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {

    }

    public function down()
    {
        echo "m101129_185401_create_news_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
```

Każda migracja zdefiniowana jest jako klasa PHP rozszerzająca [[yii\db\Migration]]. Nazwa klasy migracji jest generowana 
automatycznie w formacie `m<YYMMDD_HHMMSS>_<Nazwa>`, gdzie

* `<YYMMDD_HHMMSS>` to data i czas UTC wskazujące na moment utworzenia migracji,
* `<Nazwa>` jest identyczna z wartością argumentu `nazwa` podanego dla komendy.

Wewnątrz klasy migracji należy napisać kod w metodzie `up()`, która wprowadzi zmiany w strukturze bazy danych.
Można również napisać kod w metodzie `down()`, który spowoduje cofnięcie zmian wprowadzonych w `up()`. Metoda `up()` jest
uruchamiana w momencie aktualizacji bazy, a `down()` w momencie przywracania jej do poprzedniego stanu.
Poniższy kod pokazuje, jak można zaimplementować klasę migracji, aby utworzyć tabelę `news`:

```php
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . ' NOT NULL',
            'content' => Schema::TYPE_TEXT,
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

> Info: Nie wszystkie migracje są odwracalne. Dla przykładu, jeśli w `up()` usuwane są wiersze z tabeli, możesz nie być 
  w stanie przywrócić ich w metodzie `down()`. Może też zdarzyć się, że celowo nie podasz nic w `down()` - cofanie zmian
  migracji bazy danych nie jest czymś powszechnym - w takim wypadku należy zwrócić `false` w metodzie `down()`, aby 
  wyraźnie wskazać, że migracja nie jest odwracalna.

Podstawowa klasa migracji [[yii\db\Migration]] umożliwia połączenie z bazą danych poprzez właściwość 
[[yii\db\Migration::db|db]]. Możesz użyć jej do modyfikowania schematu bazy za pomocą metod opisanych w sekcji 
[Praca ze schematem bazy danych](db-dao.md#database-schema).

Przy tworzeniu tabeli albo kolumny zamiast używać rzeczywistych typów, powinno się stosować *typy abstrakcyjne*, dzięki 
czemu migracje będą niezależne od pojedynczych silników bazodanowych. Klasa [[yii\db\Schema]] definiuje zestaw stałych, 
które reprezentują wspierane typy abstrakcyjne. Stałe te nazwane są według schematu `TYPE_<Nazwa>`. Dla przykładu, 
`TYPE_PK` odnosi się do typu klucza głównego z autoinkrementacją; `TYPE_STRING` do typu łańcucha znaków. Kiedy migracja 
jest dodawana do konkretnej bazy danych, typy abstrakcyjne są tłumaczone na odpowiadające im typy rzeczywiste. W przypadku 
MySQL, `TYPE_PK` jest zamieniony w `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, a `TYPE_STRING` staje się `varchar(255)`.

Możesz łączyć abstrakcyjne typy z dodatkowymi definicjami - w powyższym przykładzie ` NOT NULL` jest dodane do 
`Schema::TYPE_STRING`, aby oznaczyć, że kolumna nie może być ustawiona jako `null`.

> Info: Mapowanie typów abstrakcyjnych na rzeczywiste jest określone we właściwości [[yii\db\QueryBuilder::$typeMap|$typeMap]]
  dla każdej klasy `QueryBuilder` poszczególnych wspieranych silników baz danych.

Począwszy od wersji 2.0.6, możesz skorzystać z nowej klasy budowania schematów, która pozwala na znacznie wygodniejszy 
sposób definiowana kolumn. Dzięki temu migracja z przykładu powyżej może być napisana następująco:

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);
    }

    public function down()
    {
        $this->dropTable('news');
    }
}
```

Lista wszystkich metod do definiowania typów kolumn dostępna jest w dokumentacji API dla [[yii\db\SchemaBuilderTrait]].


## Generowanie migracji <span id="generating-migrations"></span>

Począwszy od wersji 2.0.7 konsola migracji pozwala na wygodne utworzenie nowej migracji.

Jeśli nazwa migracji podana jest w jednej z rozpoznawalnych form, np. `create_xxx_table` lub `drop_xxx_table`, wtedy 
wygenerowany plik migracji będzie zawierał dodatkowy kod, w tym przypadku odpowiednio kod tworzenia i usuwania tabeli.
Poniżej opisane są wszystkie warianty tej funkcjonalności.

### Tworzenie tabeli

```
yii migrate/create create_post_table
```

generuje

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

Aby jednocześnie od razu dodać kolumny tabeli, zdefiniuj je za pomocą opcji `--fields`.

```
yii migrate/create create_post_table --fields="title:string,body:text"
```

generuje

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}

```

Możesz określić też więcej parametrów kolumny.

```
yii migrate/create create_post_table --fields="title:string(12):notNull:unique,body:text"
```

generuje

```php
/**
 * Handles the creation for table `post`.
 */
class m150811_220037_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('post');
    }
}
```

> Note: Klucz główny jest dodawany automatycznie i nazwany domyślnie `id`. Jeśli chcesz użyć innej nazwy, możesz 
  zdefiniować go bezpośrednio np. `--fields="name:primaryKey"`.

#### Klucze obce

Począwszy od wersji 2.0.8 generator pozwala na zdefiniowanie kluczy obcych za pomocą opcji `foreignKey`.

```
yii migrate/create create_post_table --fields="author_id:integer:notNull:foreignKey(user),category_id:integer:defaultValue(1):foreignKey,title:string,body:text"
```

generuje

```php
/**
 * Handles the creation for table `post`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `category`
 */
class m160328_040430_create_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->defaultValue(1),
            'title' => $this->string(),
            'body' => $this->text(),
        ]);

        // creates index for column `author_id`
        $this->createIndex(
            'idx-post-author_id',
            'post',
            'author_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex(
            'idx-post-category_id',
            'post',
            'category_id'
        );

        // add foreign key for table `category`
        $this->addForeignKey(
            'fk-post-category_id',
            'post',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-post-author_id',
            'post'
        );

        // drops index for column `author_id`
        $this->dropIndex(
            'idx-post-author_id',
            'post'
        );

        // drops foreign key for table `category`
        $this->dropForeignKey(
            'fk-post-category_id',
            'post'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            'idx-post-category_id',
            'post'
        );

        $this->dropTable('post');
    }
}
```

Umiejscowienie słowa `foreignKey` w definicji kolumny nie ma znaczenia dla generatora, zatem:

- `author_id:integer:notNull:foreignKey(user)`
- `author_id:integer:foreignKey(user):notNull`
- `author_id:foreignKey(user):integer:notNull`

wygenerują ten sam kod.

Opcja `foreignKey` może być wzbogacona o parametr w nawiasach, który oznacza nazwę tabeli relacji dla generowanego 
klucza obcego. Bez tego parametru użyta zostanie nazwa tabeli relacji zgodna z nazwą kolumny.

W przykładzie powyżej `author_id:integer:notNull:foreignKey(user)` wygeneruje kolumnę o nazwie `author_id` z kluczem 
obcym wskazującym na tabelę `user`, natomiast `category_id:integer:defaultValue(1):foreignKey` wygeneruje kolumnę 
`category_id` z kluczem obcym wskazującym na tabelę `category`.

Począwszy od wersji 2.0.11, dla `foreignKey` można podać drugi parametr, oddzielony białym znakiem, z nazwą kolumny 
relacji dla generowanego klucza obcego. Jeśli drugi parametr nie jest podany, nazwa kolumny jest pobierana ze schematu tabeli.
Jeśli schemat nie istnieje , klucz główny nie jest ustawiony lub jest kluczem kompozytowym, używana jest domyślna nazwa `id`.

### Usuwanie tabeli

```
yii migrate/create drop_post_table --fields="title:string(12):notNull:unique,body:text"
```

generuje

```php
class m150811_220037_drop_post_table extends Migration
{
    public function up()
    {
        $this->dropTable('post');
    }

    public function down()
    {
        $this->createTable('post', [
            'id' => $this->primaryKey(),
            'title' => $this->string(12)->notNull()->unique(),
            'body' => $this->text()
        ]);
    }
}
```

### Dodawanie kolumny

Jeśli nazwa migracji jest w postaci `add_xxx_column_to_yyy_table`, wtedy plik będzie zawierał wywołania metod `addColumn` 
i `dropColumn`.

Aby dodać kolumnę:

```
yii migrate/create add_position_column_to_post_table --fields="position:integer"
```

co generuje

```php
class m150811_220037_add_position_column_to_post_table extends Migration
{
    public function up()
    {
        $this->addColumn('post', 'position', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('post', 'position');
    }
}
```

Możesz dodać wiele kolumn jednocześnie:

```
yii migrate/create add_xxx_column_yyy_column_to_zzz_table --fields="xxx:integer,yyy:text"
```

### Usuwanie kolumny

Jeśli nazwa migracji jest w postaci `drop_xxx_column_from_yyy_table`, wtedy plik będzie zawierał wywołania metod
`dropColumn` i `addColumn`.

```php
yii migrate/create drop_position_column_from_post_table --fields="position:integer"
```

generuje

```php
class m150811_220037_drop_position_column_from_post_table extends Migration
{
    public function up()
    {
        $this->dropColumn('post', 'position');
    }

    public function down()
    {
        $this->addColumn('post', 'position', $this->integer());
    }
}
```

### Dodawanie tabeli węzła

Jeśli nazwa migracji jest w postaci `create_junction_table_for_xxx_and_yyy_tables` lub `create_junction_xxx_and_yyy_tables`,
wtedy plik będzie zawierał kod potrzebny do wygenerowania tabeli węzła pomiędzy tabelami `xxx` i `yyy`.

```
yii migrate/create create_junction_table_for_post_and_tag_tables --fields="created_at:dateTime"
```

generuje

```php
/**
 * Handles the creation for table `post_tag`.
 * Has foreign keys to the tables:
 *
 * - `post`
 * - `tag`
 */
class m160328_041642_create_junction_table_for_post_and_tag_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('post_tag', [
            'post_id' => $this->integer(),
            'tag_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'PRIMARY KEY(post_id, tag_id)',
        ]);

        // creates index for column `post_id`
        $this->createIndex(
            'idx-post_tag-post_id',
            'post_tag',
            'post_id'
        );

        // add foreign key for table `post`
        $this->addForeignKey(
            'fk-post_tag-post_id',
            'post_tag',
            'post_id',
            'post',
            'id',
            'CASCADE'
        );

        // creates index for column `tag_id`
        $this->createIndex(
            'idx-post_tag-tag_id',
            'post_tag',
            'tag_id'
        );

        // add foreign key for table `tag`
        $this->addForeignKey(
            'fk-post_tag-tag_id',
            'post_tag',
            'tag_id',
            'tag',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // drops foreign key for table `post`
        $this->dropForeignKey(
            'fk-post_tag-post_id',
            'post_tag'
        );

        // drops index for column `post_id`
        $this->dropIndex(
            'idx-post_tag-post_id',
            'post_tag'
        );

        // drops foreign key for table `tag`
        $this->dropForeignKey(
            'fk-post_tag-tag_id',
            'post_tag'
        );

        // drops index for column `tag_id`
        $this->dropIndex(
            'idx-post_tag-tag_id',
            'post_tag'
        );

        $this->dropTable('post_tag');
    }
}
```

Począwszy od wersji 2.0.11, nazwy kolumn kluczy obcych dla tabeli węzła są pobierane ze schematu tabel.
Jeśli tabela nie jest zdefiniowana w schemacie, lub jej klucz główny nie jest ustawiony lub jest kluczem kompozytowym, 
używana jest domyślna nazwa `id`.

### Migracje transakcyjne <span id="transactional-migrations"></span>

Przy wykonywaniu skomplikowanych migracji bazodanowych, bardzo ważnym jest zapewnienie, aby wszystkie ich operacje 
zakończyły się sukcesem, a w przypadku niepowodzenia nie zostały wprowadzone tylko częściowo, dzięki czemu baza danych 
może zachować spójność. Zalecane jest, aby w tym celu wykonywać operacje migracji wewnątrz [transakcji](db-dao.md#performing-transactions).

Najprostszym sposobem implementacji migracji transakcyjnych jest umieszczenie ich kodu w metodach `safeUp()` i `safeDown()`.
Metody te różnią się od `up()` i `down()` tym, że są wywoływane automatycznie wewnątrz transakcji.
W rezultacie niepowodzenie wykonania dowolnej z operacji skutkuje automatycznym cofnięciem wszystkich poprzenich udanych 
operacji.

W poniższym przykładzie oprócz stworzenia tabeli `news` dodatkowo dodajemy pierwszy wiersz jej danych.

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'content' => $this->text(),
        ]);

        $this->insert('news', [
            'title' => 'test 1',
            'content' => 'content 1',
        ]);
    }

    public function safeDown()
    {
        $this->delete('news', ['id' => 1]);
        $this->dropTable('news');
    }
}
```

Zwróć uwagę na to, że dodając wiele operacji bazodanowych w `safeUp()`, zwykle powinieneś odwrócić kolejność ich 
wykonywania w `safeDown()`. W naszym przykładzie najpierw tworzymy tabelę, a potem dodajemy wiersz w `safeUp()`, natomiast
w `safeDown()` najpierw kasujemy wiersz, a potem usuwamy tabelę.

> Note: Nie wszystkie silniki baz danych wspierają transakcje i nie wszystkie rodzaje komend bazodanowych można umieszczać
  w transakcjach. Dla przykładu, zapoznaj się z rozdziałem dokumentacji MySQL 
  [Statements That Cause an Implicit Commit](https://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html). W przypadku 
  braku możliwości skorzystania z transakcji, powinieneś użyć `up()` i `down()`.


### Metody pozwalające na dostęp do bazy danych <span id="db-accessing-methods"></span>

Bazowa klasa migracji [[yii\db\Migration]] udostępnia zestaw metod, dzięki którym można połączyć się z i manipulować 
bazą danych. Metody te są nazwane podobnie jak [metody DAO](db-dao.md) klasy [[yii\db\Command]].
Przykładowo metoda [[yii\db\Migration::createTable()]] pozwala na stworzenie nowej tabeli, tak jak 
[[yii\db\Command::createTable()]].

Zaletą korzystania z metod [[yii\db\Migration]] jest brak konieczności bezpośredniego tworzenia instancji [[yii\db\Command]],
a wywołanie każdej z tych metod dodatkowo wyświetli użyteczne informacje na temat operacji bazodanowych i 
czasu ich wykonywania.

Poniżej znajdziesz listę wspomnianych wcześniej metod:

* [[yii\db\Migration::execute()|execute()]]: wykonywanie komendy SQL
* [[yii\db\Migration::insert()|insert()]]: dodawanie pojedynczego wiersza
* [[yii\db\Migration::batchInsert()|batchInsert()]]: dodawanie wielu wierszy
* [[yii\db\Migration::upsert()|upsert()]]: dodawanie pojedynczego wiersza lub aktualizowanie go, jeśli już istnieje (od 2.0.14)
* [[yii\db\Migration::update()|update()]]: aktualizowanie wierszy
* [[yii\db\Migration::delete()|delete()]]: usuwanie wierszy
* [[yii\db\Migration::createTable()|createTable()]]: tworzenie tabeli
* [[yii\db\Migration::renameTable()|renameTable()]]: zmiana nazwy tabeli
* [[yii\db\Migration::dropTable()|dropTable()]]: usuwanie tabeli
* [[yii\db\Migration::truncateTable()|truncateTable()]]: usuwanie wszystkich wierszy w tabeli
* [[yii\db\Migration::addColumn()|addColumn()]]: dodawanie kolumny
* [[yii\db\Migration::renameColumn()|renameColumn()]]: zmiana nazwy kolumny
* [[yii\db\Migration::dropColumn()|dropColumn()]]: usuwanie kolumny
* [[yii\db\Migration::alterColumn()|alterColumn()]]: zmiana definicji kolumny
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: dodawanie klucza głównego
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: usuwanie klucza głównego
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: dodawanie klucza obcego
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: usuwanie klucza obcego
* [[yii\db\Migration::createIndex()|createIndex()]]: tworzenie indeksu
* [[yii\db\Migration::dropIndex()|dropIndex()]]: usuwanie indeksu
* [[yii\db\Migration::addCommentOnColumn()|addCommentOnColumn()]]: dodawanie komentarza do kolumny
* [[yii\db\Migration::dropCommentFromColumn()|dropCommentFromColumn()]]: usuwanie komentarza z kolumny
* [[yii\db\Migration::addCommentOnTable()|addCommentOnTable()]]: dodawanie komentarza do tabeli
* [[yii\db\Migration::dropCommentFromTable()|dropCommentFromTable()]]: usuwanie komentarza z tabeli

> Info: [[yii\db\Migration]] nie udostępnia metod dla kwerendy danych. Wynika to z tego, że zwykle nie jest potrzebne
  wyświetlanie dodatkowych informacji na temat pobieranych danych z bazy. Dodatkowo możesz zawsze użyć potężnego
  [Konstruktora kwerend](db-query-builder.md) do zbudowania i wywołania skomplikowanych kwerend.
  Użycie konstruktora kwerend w migracji może wyglądać następująco:
>
> ```php
> // uaktualnij kolumnę statusu dla wszystkich użytkowników
> foreach((new Query)->from('users')->each() as $user) {
>     $this->update('users', ['status' => 1], ['id' => $user['id']]);
> }
> ```

## Stosowanie migracji <span id="applying-migrations"></span>

Aby uaktualnić bazę danych do najświeższej wersji jej struktury, należy zastosować wszystkie dostępne nowe migracje, 
korzystając z poniższej komendy:

```
yii migrate
```

Komenda ta wyświetli listę wszystkich migracji, które jeszcze nie zostały zastosowane. Jeśli potwierdzisz, że chcesz je
zastosować, wywoła ona metodę `up()` lub `safeUp()` dla każdej z migracji na liście, w kolejności ich znaczników czasu.
Jeśli którakolwiek z migracji nie powiedzie się, komenda zakończy działanie bez stosowania pozostałych migracji.

> Tip: Jeśli nie masz dostępu do linii komend na serwerze, wypróbuj rozszerzenie [web shell](https://github.com/samdark/yii2-webshell).

Dla każdej udanej migracji komenda doda wiersz do bazy danych w tabeli `migration`, aby oznaczyć fakt zastosowania migracji.
Pozwoli to na identyfikację, która z migracji została już zastosowana, a która jeszcze nie.

> Info: Narzędzie do migracji automatycznie utworzy tabelę `migration` w bazie danych, wskazaną przez opcję 
  [[yii\console\controllers\MigrateController::db|db]] komendy. Domyślnie jest to baza danych określona w 
  [komponencie aplikacji](structure-application-components.md) `db`.

Czasem możesz mieć potrzebę zastosowania tylko jednej bądź kilku nowych migracji, zamiast wszystkich na raz.
Możesz tego dokonać określając liczbę migracji, które chcesz zastosować uruchamiając komendę.
Przykładowo, poniższa komenda spróbuje zastosować następne trzy dostępne migracje:

```
yii migrate 3
```

Możesz również dokładnie wskazać konkretną migrację, która powinna być zastosowana na bazie danych, używając komendy 
`migrate/to` na jeden z poniższych sposobów:

```
yii migrate/to 150101_185401                      # używając znacznika czasu z nazwy migracji
yii migrate/to "2015-01-01 18:54:01"              # używając łańcucha znaków, który może być sparsowany przez strtotime()
yii migrate/to m150101_185401_create_news_table   # używając pełnej nazwy
yii migrate/to 1392853618                         # używając UNIXowego znacznika czasu
```

Jeśli dostępne są niezaaplikowane migracje wcześniejsze niż ta wyraźnie wskazane w komendzie, zostaną one zastosowane 
automatycznie przed wskazaną migracją.

Jeśli wskazana migracja została już wcześniej zaaplikowana, wszystkie zaaplikowane aplikacje z późniejszą datą zostaną
cofnięte.


## Cofanie migracji <span id="reverting-migrations"></span>

Aby odwrócić (wycofać) jedną lub więcej migracji, które zostały wcześniej zastosowane, możesz uruchomić następującą komendę:

```
yii migrate/down     # cofa ostatnio dodaną migrację
yii migrate/down 3   # cofa 3 ostatnio dodane migracje
```

> Note: Nie wszystkie migracje są odwracalne. Próba cofnięcia takiej migracji spowoduje błąd i zatrzyma cały proces.


## Ponawianie migracji <span id="redoing-migrations"></span>

Ponawianie migracji oznacza najpierw wycofanie jej, a potem ponowne zastosowanie. Można tego dokonać następująco:

```
yii migrate/redo        # ponawia ostatnio zastosowaną migrację
yii migrate/redo 3      # ponawia ostatnie 3 zastosowane migracje
```

> Note: Jeśli migracja nie jest odwracalna, nie będziesz mógł jej ponowić.

## Odświeżanie migracji <span id="refreshing-migrations"></span>

Począwszy od Yii 2.0.13 możliwe jest usunięcie wszystkich tabel i kluczy obcych z bazy danych i zastosowanie wszystkich 
migracji od początku.

```
yii migrate/fresh       # czyści bazę danych i wykonuje wszystkie migracje od początku
```

## Lista migracji <span id="listing-migrations"></span>

Aby wyświetlić listę wszystkich zastosowanych i oczekujących migracji, możesz użyć następujących komend:

```
yii migrate/history     # pokazuje ostatnie 10 zastosowanych migracji
yii migrate/history 5   # pokazuje ostatnie 5 zastosowanych migracji
yii migrate/history all # pokazuje wszystkie zastosowane migracje

yii migrate/new         # pokazuje pierwsze 10 nowych migracji
yii migrate/new 5       # pokazuje pierwsze 5 nowych migracji
yii migrate/new all     # pokazuje wszystkie nowe migracje
```


## Modyfikowanie historii migracji <span id="modifying-migration-history"></span>

Czasem zamiast aplikowania lub odwracania migracji, możesz chcieć po prostu zaznaczyć, że baza danych zostałą już 
uaktualniona do konkretnej migracji. Może się tak zdarzyć, gdy ręcznie modyfikujesz bazę i nie chcesz, aby migracja(e) z
tymi zmianami zostały potem ponownie zaaplikowane. Możesz to osiągnąć w następujący sposób:

```
yii migrate/mark 150101_185401                      # używając znacznika czasu z nazwy migracji
yii migrate/mark "2015-01-01 18:54:01"              # używając łańcucha znaków, który może być sparsowany przez strtotime()
yii migrate/mark m150101_185401_create_news_table   # używając pełnej nazwy
yii migrate/mark 1392853618                         # używając UNIXowego znacznika czasu
```

Komenda zmodyfikuje tabelę `migration` poprzez dodanie lub usunięcie wierszy, aby zaznaczyć, że baza danych ma już 
zastosowane migracje aż do tej określonej w komendzie. Migracje nie zostaną faktycznie zastosowane lub usunięte.


## Dostosowywanie migracji <span id="customizing-migrations"></span>

Dostępnych jest kilka opcji pozwalających na dostosowanie komendy migracji do własnych potrzeb.


### Użycie opcji linii komend <span id="using-command-line-options"></span>

Komenda migracji ma kilka opcji, które pozwalają na zmianę jej działąnia:

* `interactive`: boolean (domyślnie `true`), określa czy przeprowadzić migrację w trybie interaktywnym.
  Jeśli ustawione jest `true`, użytkownik będzie poproszony o potwierdzenie przed wykonaniem określonych operacji.
  Możesz chcieć zmienić to ustawienie na `false`, jeśli komenda ma być używana w tle.

* `migrationPath`: string|array (domyślnie `@app/migrations`), określa folder, gdzie znajdują się wszystkie pliki migracji.
  Parametr może być określony jako rzeczywista ścieżka lub [alias](concept-aliases.md).
  Zwróć uwagę na to, że folder musi istnieć, inaczej okmenda może wywołać błąd. Począwszy od wersji 2.0.12 można tutaj 
  podać tablicę, aby załadować migracje z wielu źródeł.

* `migrationTable`: string (domyślnie `migration`), określa nazwę tabeli w bazie danych, gdzie trzymana będzie historia 
  migracji. Tabela będzie automatycznie stworzona, jeśli nie istnieje.
  Możesz również utworzyć ją ręcznie używając struktury `version varchar(255) primary key, apply_time integer`.

* `db`: string (domyślnie `db`), określa identyfikator bazodanowego [komponentu aplikacji](structure-application-components.md).
  Reprezentuje on bazę danych, na której będą zastosowane migracje.

* `templateFile`: string (domyślnie `@yii/views/migration.php`), określa ścieżkę pliku szablonu, używanego do generowania
  szkieletu plików migracji. Parametr może być określony jako rzeczywista ścieżka lub [alias](concept-aliases.md). 
  Plik szablonu jest skryptem PHP, w którym możesz użyć predefiniowanej zmiennej `$className`, aby pobrać nazwę klasy
  migracji.

* `generatorTemplateFiles`: array (domyślnie `[
        'create_table' => '@yii/views/createTableMigration.php',
        'drop_table' => '@yii/views/dropTableMigration.php',
        'add_column' => '@yii/views/addColumnMigration.php',
        'drop_column' => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php'
  ]`), określa pliki szablonów do generowania kodu migracji. Po więcej szczegółów przejdź do 
  "[Generowanie migracji](#generating-migrations)".

* `fields`: tablica definicji kolumn w postaci łańcuchów znaków do wygenerowania kodu migracji. Domyślnie `[]`.
  Format każdej definicji to `NAZWA_KOLUMNY:TYP_KOLUMNY:DEKORATOR_KOLUMNY`. Dla przykładu, 
  `--fields=name:string(12):notNull` generuje kolumnę typu "string" o rozmiarze 12, która nie może mieć wartości `null`.

Poniższy przykład pokazuje jak można użyć tych opcji.

Chcemy zmigrować moduł `forum`, którego pliki migracji znajdują się w folderze `migrations` modułu - używamy następującej 
komendy:

```
# stosuje migracje dla modułu forum w trybie nieinteraktywnym
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### Konfigurowanie komendy globalnie <span id="configuring-command-globally"></span>

Zamiast podawać żmudnie te same opcje za każdym razem, gdy uruchamiamy komendę migracji, można ją skonfigurować w 
konfiguracji aplikacji:

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationTable' => 'backend_migration',
        ],
    ],
];
```

Powyższa konfiguracja powoduje, że z każdym uruchomieniem komendy migracji, tabela `backend_migration` jest używana do
zapisu historii migracji i nie musisz już określać jej za pomocą opcji linii komend `migrationTable`.


### Migracje w przestrzeni nazw <span id="namespaced-migrations"></span>

Począwszy od 2.0.10 możliwe jest używanie przestrzeni nazw w klasach migracji. Możesz zdefiniować listę przestrzeni nazw
za pomocą [[yii\console\controllers\MigrateController::migrationNamespaces|migrationNamespaces]]. Korzystanie z przestrzeni
nazw pozwala na łatwe używanie wielu źródeł migracji. Przykładowo:

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => null, // wyłącz ścieżkę do folderu migracji, jeśli dodajesz app\migrations na liście poniżej
            'migrationNamespaces' => [
                'app\migrations', // Wspólne migracje dla całej aplikacji
                'module\migrations', // Migracje konkretnego modułu
                'some\extension\migrations', // Migracje konkretnego rozszerzenia
            ],
        ],
    ],
];
```

> Note: Migracje zaaplikowane z różnych przestrzeni nazw będą dodane do **pojedynczej** historii migracji, przez co np. 
  niemożliwym jest zastosowanie lub cofnięcie migracji z tylko wybranej przestrzeni nazw.

Wykonując operacje na migracjach z przestrzeni nazw: dodając nowe, odwracając je, itd., należy podać pełną przestrzeń nazw
przed nazwą migracji. Zwróć uwagę na to, że odwrotny ukośnik (`\`) jest zwykle uważany za znak specjalny linii komend, 
zatem musisz odpowiednio zastosować symbol ucieczki, aby uniknąć błędów konsoli i niespodziewanych skutków komendy. 
Dla przykładu:

```
yii migrate/create app\\migrations\\CreateUserTable
```

> Note: Migracje, których lokalizacja określona jest poprzez 
  [[yii\console\controllers\MigrateController::migrationPath|migrationPath]] nie mogą zawierać przestrzeni nazw. Migracje 
  w przestrzeni nazw mogą być zaaplikowane tylko jeśli są wymienione we właściwości 
  [[yii\console\controllers\MigrateController::migrationNamespaces]].

Począwszy od wersji 2.0.12 właściwość [[yii\console\controllers\MigrateController::migrationPath|migrationPath]] pozwala
również na podanie tablicy wymieniającej wszystkie foldery zawierające migracje bez przestrzeni nazw.
Zmiana ta została wprowadzona dla istniejących projektów, które używają migracji z wielu lokalizacji, głównie z zewnętrznych
źródeł jak rozszerzenia Yii tworzone przez innych deweloperów, które z tego powodu nie mogą łatwo być zmodyfikowane, aby 
używać przestrzeni nazw.

#### Generowanie migracji w przestrzeni nazw

Migracje w przestrzeni nazw korzystają z formatu nazw "CamelCase" `M<YYMMDDHHMMSS><Nazwa>` (przykładowo `M190720100234CreateUserTable`). 
Generując taką migrację pamiętaj, że nazwa tabeli będzie przekonwertowana z formatu "CamelCase" na format "podkreślnikowy".
Dla przykładu:

```
yii migrate/create app\\migrations\\DropGreenHotelTable
```

generuje migrację w przestrzeni nazw `app\migrations` usuwającą tabelę `green_hotel`, a

```
yii migrate/create app\\migrations\\CreateBANANATable
```

generuje migrację w przestrzeni nazw `app\migrations` tworzącą tabelę `b_a_n_a_n_a`.

Jeśli nazwa tabeli zawiera małe i wielkie litery (like `studentsExam`), poprzedź nazwę podkreślnikiem:

```
yii migrate/create app\\migrations\\Create_studentsExamTable
```

To wygeneruje migrację w przestrzeni nazw `app\migrations` tworzącą tabelę `studentsExam`.

### Rozdzielenie migracji <span id="separated-migrations"></span>

Czasem korzystanie z pojedynczej historii migracji dla wszystkich migracji w projekcie jest uciążliwe. Dla przykładu, 
możesz zainstalować rozszerzenie 'blog', zawierające całkowicie oddzielne funkcjonalności i dostarczające własne migracje, 
które nie powinny wpływać na te dedykowane dla funkcjonalności głównego projektu.

Jeśli chcesz, aby część migracji mogła być zastosowana i śledzona całkowicie niezależnie od pozostałych, możesz skonfigurować 
kilka komend migracji, które będą używać różnych przestrzeni nazw i tabeli historii migracji:

```php
return [
    'controllerMap' => [
        // Wspólne migracje dla całej aplikacji
        'migrate-app' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => ['app\migrations'],
            'migrationTable' => 'migration_app',
            'migrationPath' => null,
        ],
        // Migracje dla konkretnego modułu
        'migrate-module' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => ['module\migrations'],
            'migrationTable' => 'migration_module',
            'migrationPath' => null,
        ],
        // Migrations dla konkretnego rozszerzenia
        'migrate-rbac' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@yii/rbac/migrations',
            'migrationTable' => 'migration_rbac',
        ],
    ],
];
```

Zwróć uwagę na to, że teraz, aby zsynchronizować bazę danych, musisz uruchomić kilka komend zamiast jednej:

```
yii migrate-app
yii migrate-module
yii migrate-rbac
```


## Migrowanie wielu baz danych <span id="migrating-multiple-databases"></span>

Domyślnie migracje są stosowane do jednej bazy danych określonej przez 
[komponent aplikacji](structure-application-components.md) `db`. Jeśli chcesz, aby były zastosowane do innej bazy, musisz
zdefiniować opcję `db` w linii komend, jak poniżej,

```
yii migrate --db=db2
```

Ta komenda zastosuje migracje do bazy `db2`.

Czasem konieczne jest, aby zastosować *niektóre* migracje do jednej bazy, a inne do drugiej. Aby to uzyskać, podczas 
implementacji klasy migracji należy bezpośrednio wskazać identyfikator komponentu bazy danych, który migracja ma użyć, 
jak poniżej:

```php
<?php

use yii\db\Migration;

class m150101_185401_create_news_table extends Migration
{
    public function init()
    {
        $this->db = 'db2';
        parent::init();
    }
}
```

Ta migracja będzie zastosowana do bazy `db2`, nawet jeśli w opcjach komendy określona będzie inna baza. 
Zwróć uwagę na to, że historia migracji będzie uaktualniona wciąż w bazie danych określonej przez opcję `db` linii komend.

Jeśli masz wiele migracji korzystających z tej samej bazy danych, zalecane jest utworzenie bazowej klasy migracji z 
powyższym kodem metody `init()`, a następnie dziedziczenie po niej w każdej kolejnej migracji.

> Tip: Oprócz ustawiania właściwości [[yii\db\Migration::db|db]], możesz również operować na różnych bazach poprzez 
  tworzenie nowych połączeń bazodanowych w klasach migracji, a następnie korzystanie z [metod DAO](db-dao.md) i tych
  połączeń.

Inną strategią migracji wielu baz danych jest utrzymywanie migracji dla różnych baz w różnych folderach migracji. Dzięki
temu możesz te bazy migrować w osobnych komendach:

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

Pierwsza komenda zastosuje migracje z folderu `@app/migrations/db1` na bazie `db1`, druga migracje z folderu 
`@app/migrations/db2` na bazie `db2`, itd.
