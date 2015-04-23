Migrações de Dados (Migrations)
==================

Durante o curso de desenvolvimento e manutenção de uma aplicação orientada a banco de dados, a estrutura 
de banco sendo usada evolui ao mesmo tempo em que o código. Por exemplo, durante o desenvolvimento de 
uma aplicação, a criação de uma nova tabela pode ser necessária; após ser feito o deploy da aplicação em produção,
pode ser descoberto que um índice deveria ser criado para melhorar a performance de alguma query; entre 
outros. Como a mudança de uma estrutura de banco de dados normalmente necessita de alguma mudança no código, 
Yii suporta a então chamada funcionalidade de *migração de dados* que permite que você mantenha um registro
das mudanças feitas no banco de dados em termos de *migrações de dados* que são versionadas em conjunto
com o código fonte da aplicação.

Os seguintes passos mostram como uma migração de dados pode ser usada pela equipe durante o desenvolvimento:

1. João cria uma nova migração (ex. cria uma nova tabela, muda a definição de uma coluna, etc.).
2. João comita a nova migração no sistema de controle de versão (ex. Git, Mercurial).
3. Pedro atualiza seu repositório a partir do sistema de controle de versão e recebe a nova migração.
4. Pedro aplica a nova migração ao seu banco de dados local em seu ambiente de desenvolvimento, e assim, sincronizando seu banco de dados para refletir as mudanças que João fez.

E os seguintes passos mostram como fazer o deploy para produção de uma nova versão:

1. Luiz cria uma nova tag para o repositório do projeto que contem algumas novas migrações de dados.
2. Luiz atualiza o código fonte no servidor em produção para a tag criada.
3. Luiz aplica todas as migrações de dados acumuladas para o banco de dados em produção. 

Yii oferece um conjunto de ferramentas em linha de comando que permitem que você:

* crie novas migrações;
* aplique migrações;
* reverta migrações;
* reaplique migrações;
* exiba um histórico das migrações;

Todas estas ferramentas são acessíveis através do comando `yii migrate`. Nesta seção nós iremos descrever
em detalhes como realizar várias tarefas usando estas ferramentas. Você também pode descobrir como usar 
cada ferramenta através do comando de ajuda `yii help migrate`.


## Criando Migrações <span id="creating-migrations"></span>

Para criar uma nova migração, execute o seguinte comando:

```
yii migrate/create <nome>
```

O argumento obrigatório `nome` serve como uma breve descricão sobre a nova migração. Por exemplo, se
a migração é sobre a criação de uma nova tabela chamada *noticias*, você pode usar o nome `criar_tabela_noticias`
e executar o seguinte comando:

```
yii migrate/create criar_tabela_noticias
```

> Nota: Como o argumento `nome` será usado como parte do nome da classe de migração gerada, este deve
  conter apenas letras, dígitos, e/ou underline. 

O comando acima irá criar um novo arquivo contendo uma classe PHP chamada `m150101_185401_criar_tabela_noticias.php`
na pasta `@app/migrations`. O arquivo contem o seguinte codigo que declara a classe de migração
`m150101_185401_criar_tabela_noticias` com o código esqueleto:

```php
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_criar_tabela_noticias extends Migration
{
    public function up()
    {
    }

    public function down()
    {
        echo "m101129_185401_criar_tabela_noticias cannot be reverted.\n";
        return false;
    }
}
```
Cada migração de dados é definida como uma classe PHP extendida de [[yii\db\Migration]]. O nome da
calsse de migração é automaticamente gerado no formato `m<YYMMDD_HHMMSS>_<Name>`, onde 

* `<YYMMDD_HHMMSS>` refere-se a data UTC em que o comando de criação da migração foi executado.
* `<Name>` é igual ao valor do argumento `name` que você passou no comando.

Na classe de migração, é esperado que você escreva no método `up()` as mudanças a serem feitas na estrutura do banco de dados.
Você pode também escrever código no método `down()` para reverter as mudanças feitas por `up()`. O método `up` é invocado quando você atualiza o seu banco de dados com esta migração, enquanto o método `down()` é invocado quando você reverte as mudanças no banco. O seguinte código mostra como você pode implementar a classe de migração para criar a tabela `noticias`: 

```php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_criar_tabela_noticias extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('noticias', [
            'id' => Schema::TYPE_PK,
            'titulo' => Schema::TYPE_STRING . ' NOT NULL',
            'conteudo' => Schema::TYPE_TEXT,
        ]);
    }

    public function down()
    {
        $this->dropTable('noticias');
    }

}
```

> Observação: Nem todas as migrações são reversíveis. Por exemplo, se o método `up()` deleta um registro de uma tabela,
  você possívelmente não será capas de recuperar este registro com o método `down()`. Em alguns casos, você pode ter 
  tido muita preguiça e não ter implementado o método `down()`, porque não é muito comum reverter migrações de dados.
  Neste caso, você deve retornar `false` no método `down()` para indicar que a migração não é reversível.

A classe base Migration [[yii\db\Migration]] expões a conexão ao banco através da propriedade [[yii\db\Migration::db|db]].
Você pode usá-la para manípular o esquema do banco de dados usando os métods como descritos em 
[Working with Database Schema](db-dao.md#database-schema).

Ao invés de usar típos físicos, ao criar uma tabela ou coluna, você deve usar *tipos abstratos* para que
suas migrações sejam independentes do SGBD. A classe [[yii\db\Schema]] define uma gama de constantes para
representar os tipos abstratos suportados. Estas constantes são nomeandas no formato `TYPE_<Name>`. Por exemplo,
`TYPE_PK` refere-se ao tipo chave primária auto-incrementavel; `TYPE_STRING` refere-se ao típo string. 
Quando a migração for aplicada a um banco de dados em particular, os tipos abstratos serão traduzidos nos
respectívos tipos físicos. No caso do MySQL, `TYPE_PK` será traduzida para 
`int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, enquanto `TYPE_STRING` será `varchar(255)`. 

Você pode adicionar algumas constraints ao usar tipos abstratos. No exemplo acíma, ` NOT NULL` é adicionado
a `Schema::TYPE_STRING` para especificar que a coluna não pode ser nula.

> Observação: O mapeamento entre tipos abstratos e tipos físicos é especificado pela propriedade [[yii\db\QueryBuilder::$typeMap|$typeMap]] em cada classe `QueryBuilder`.


### Migrações Transacionais <span id="transactional-migrations"></span>

Ao realizar migrações de dados complexas, é importante assegurar que cada migração irá ter sucesso ou irá falhar 
por completo para que o banco não perca sua integridade e consistencia. Para atingir este objetivo, recomenda-se que
você encapsule suas operações de banco de dados em cada migração em uma [transaction](db-dao.md#performing-transactions).

Um jeito mais fácil de implementar uma migraçãó transacional é colocar o seu código de migração nos métodos `safeUp()` e `safeDown()`. Estes métodos diferem de `up()` e `down()` por que eles estão implicitamente encapsulados em uma transação. Como resultado, se qualquer operação nestes métodos falhar, todas as operações anteriores sofrerão roll back
automaticamente.

No exemplo a seguir, além de criar a tabela `noticias` nós também inserimos um registro inicial a esta tabela.

```php

use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_criar_tabela_noticias extends Migration
{
    public function safeUp()
    {
        $this->createTable('noticias', [
            'id' => 'pk',
            'titulo' => Schema::TYPE_STRING . ' NOT NULL',
            'conteudo' => Schema::TYPE_TEXT,
        ]);
        
        $this->insert('noticias', [
            'titulo' => 'título 1',
            'conteudo' => 'conteúdo 1',
        ]);
    }

    public function safeDown()
    {
        $this->delete('noticias', ['id' => 1]);
        $this->dropTable('noticias');
    }
}
```

Note que normalmente quando você realiza multiplas operações em `safeUp()`, você deverá reverter a ordem de execução
em `safeDown()`. No exemplo acima nós primeiramente criamos a tabela e depois inserimos uma túpla em `safeUp()`; enquanto
em `safeDown()` nós primeiramente apagamos o registro e depois eliminamos a tabela.

> Nota: Nem todos os SGBDs suportam transações. E algumas requisições de banco não podem ser encapsuladas em uma transação. Para alguns exemplos, referir a [implicit commit](http://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html). Se este for o caso, implemente os métodos `up()` e `down()`.

### Métodos de acesso ao Banco de Dados <span id="db-accessing-methods"></span>

A classe base migration [[yii\db\Migration]] entrega vários métodos que facilitam o acesso e a manipulação de 
bancos de dados. Você deve achar que estes métodos são nomeados similarmente a [DAO methods](db-dao.md) encontrados 
na classe [[yii\db\Command]]. Por exemplo, o método [[yii\db\Migration::createTable()]] permite que você crie uma 
nova tabela assim como [[yii\db\Command::createTable()]] o faz.

O benefício ao usar os métodos encontrados em [[yii\db\Migration]] é que você não precisa criar explícitamente
instancias de [[yii\db\Command]] e a execução de cada método automaticamente exibirá mensagens úteis que dirão
a você quais operações estão sendo feitas e quanto tempo elas estão durando.

Abaixo etá uma lista de todos estes métodos de acesso ao banco de dados:

* [[yii\db\Migration::execute()|execute()]]: executando um SQL
* [[yii\db\Migration::insert()|insert()]]: inserindo um novo regístro
* [[yii\db\Migration::batchInsert()|batchInsert()]]: inserindo vários registros
* [[yii\db\Migration::update()|update()]]: atualizando registros
* [[yii\db\Migration::delete()|delete()]]: apagando registros
* [[yii\db\Migration::createTable()|createTable()]]: criando uma tabela
* [[yii\db\Migration::renameTable()|renameTable()]]: renomeando uma tabela
* [[yii\db\Migration::dropTable()|dropTable()]]: removendo uma tabela
* [[yii\db\Migration::truncateTable()|truncateTable()]]: removendo todos os registros em uma tabela
* [[yii\db\Migration::addColumn()|addColumn()]]: adicionando uma coluna
* [[yii\db\Migration::renameColumn()|renameColumn()]]: renomeando uma coluna
* [[yii\db\Migration::dropColumn()|dropColumn()]]: removendo uma coluna
* [[yii\db\Migration::alterColumn()|alterColumn()]]: alterando uma coluna
* [[yii\db\Migration::addPrimaryKey()|addPrimaryKey()]]: adicionando uma chave primária
* [[yii\db\Migration::dropPrimaryKey()|dropPrimaryKey()]]: removendo uma chave primária
* [[yii\db\Migration::addForeignKey()|addForeignKey()]]: adicionando uma chave estrangeira
* [[yii\db\Migration::dropForeignKey()|dropForeignKey()]]: removendo uma chave estrangeira
* [[yii\db\Migration::createIndex()|createIndex()]]: criando um índice
* [[yii\db\Migration::dropIndex()|dropIndex()]]: removendo um índice

> Observação: [[yii\db\Migration]] não possui um método de consulta ao banco de dados. Isto porque você normalmente não irá precisar exibir informações extras ao recuperar informações de um banco de dados. E além disso você pode usar o poderoso [Query Builder](db-query-builder.md) para construir e executar consultas complexas.


## Aplicando migrações <span id="applying-migrations"></span>

Para atualizar um banco de dados para a sua estrutura mais atual, você deve aplicar todas as migracões disponíveis usando o seguinte comando:

```
yii migrate
```

Este comando irá listar todas as migrações que não foram alicadas até agora. Se você confirmar que deseja aplicar
estas migrações, cada nova classe de migração irá executar os métodos `up()` ou `safeUp()` um após o outro, na 
ordem relacionada a data marcada em seus nomes. Se qualquer uma das migrações falhar o comando terminará sem aplicar
o resto das migrações. 

Para cada migração aplicada com sucesso, o comando irá inserir um registro numa tabela no banco de dados chamada
`migration` para registrar uma aplicação de migração. Isto irá permitir que a ferramenta de migração identifique
quais migrações foram aplicadas e quais não foram.

> Observação: Esta ferramenta de migração irá automaticamente criar a tabela `migration` no banco de dados especificado pela opção do comando [[yii\console\controllers\MigrateController::db|db]]. Por padrão, o banco de dados é especificado por `db` [application component](structure-application-components.md).

Eventualmente, você 
  
Sometimes, you may only want to apply one or a few new migrations, instead of all available migrations.
You can do so by specifying the number of migrations that you want to apply when running the command.
For example, the following command will try to apply the next three available migrations:

```
yii migrate 3
```

You can also explicitly specify a particular migration to which the database should be migrated
by using the `migrate/to` command in one of the following formats:

```
yii migrate/to 150101_185401                      # using timestamp to specify the migration
yii migrate/to "2015-01-01 18:54:01"              # using a string that can be parsed by strtotime()
yii migrate/to m150101_185401_create_news_table   # using full name
yii migrate/to 1392853618                         # using UNIX timestamp
```

If there are any unapplied migrations earlier than the specified one, they will all be applied before the specified
migration is applied.

If the specified migration has already been applied before, any later applied migrations will be reverted.


## Reverting Migrations <span id="reverting-migrations"></span>

To revert (undo) one or multiple migrations that have been applied before, you can run the following command:

```
yii migrate/down     # revert the most recently applied migration
yii migrate/down 3   # revert the most 3 recently applied migrations
```

> Nota: Not all migrations are reversible. Trying to revert such migrations will cause an error and stop the
  entire reverting process.


## Redoing Migrations <span id="redoing-migrations"></span>

Redoing migrations means first reverting the specified migrations and then applying again. This can be done
as follows:

```
yii migrate/redo        # redo the last applied migration 
yii migrate/redo 3      # redo the last 3 applied migrations
```

> Nota: If a migration is not reversible, you will not be able to redo it.


## Listing Migrations <span id="listing-migrations"></span>

To list which migrations have been applied and which are not, you may use the following commands:

```
yii migrate/history     # showing the last 10 applied migrations
yii migrate/history 5   # showing the last 5 applied migrations
yii migrate/history all # showing all applied migrations

yii migrate/new         # showing the first 10 new migrations
yii migrate/new 5       # showing the first 5 new migrations
yii migrate/new all     # showing all new migrations
```


## Modifying Migration History <span id="modifying-migration-history"></span>

Instead of actually applying or reverting migrations, sometimes you may simply want to mark that your database
has been upgraded to a particular migration. This often happens when you manually change the database to a particular
state and you do not want the migration(s) for that change to be re-applied later. You can achieve this goal with
the following command:

```
yii migrate/mark 150101_185401                      # using timestamp to specify the migration
yii migrate/mark "2015-01-01 18:54:01"              # using a string that can be parsed by strtotime()
yii migrate/mark m150101_185401_create_news_table   # using full name
yii migrate/mark 1392853618                         # using UNIX timestamp
```

The command will modify the `migration` table by adding or deleting certain rows to indicate that the database
has been applied migrations to the specified one. No migrations will be applied or reverted by this command.


## Customizing Migrations <span id="customizing-migrations"></span>

There are several ways to customize the migration command.


### Using Command Line Options <span id="using-command-line-options"></span>

The migration command comes with a few command-line options that can be used to customize its behaviors:

* `interactive`: boolean (defaults to true), specifies whether to perform migrations in an interactive mode. 
  When this is true, the user will be prompted before the command performs certain actions.
  You may want to set this to false if the command is being used in a background process.

* `migrationPath`: string (defaults to `@app/migrations`), specifies the directory storing all migration 
  class files. This can be specified as either a directory path or a path [alias](concept-aliases.md). 
  Note that the directory must exist, or the command may trigger an error.

* `migrationTable`: string (defaults to `migration`), specifies the name of the database table for storing
  migration history information. The table will be automatically created by the command if it does not exist.
  You may also manually create it using the structure `version varchar(255) primary key, apply_time integer`.

* `db`: string (defaults to `db`), specifies the ID of the database [application component](structure-application-components.md).
  It represents the database that will be migrated using this command.

* `templateFile`: string (defaults to `@yii/views/migration.php`), specifies the path of the template file
  that is used for generating skeleton migration class files. This can be specified as either a file path
  or a path [alias](concept-aliases.md). The template file is a PHP script in which you can use a predefined variable
  named `$className` to get the migration class name.

The following example shows how you can use these options.

For example, if we want to migrate a `forum` module whose migration files
are located within the module's `migrations` directory, we can use the following
command:

```
# migrate the migrations in a forum module non-interactively
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### Configuring Command Globally <span id="configuring-command-globally"></span>

Instead of entering the same option values every time you run the migration command, you may configure it
once for all in the application configuration like shown below:

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

With the above configuration, each time you run the migration command, the `backend_migration` table
will be used to record the migration history. You no longer need to specify it via the `migrationTable`
command-line option.


## Migrating Multiple Databases <span id="migrating-multiple-databases"></span>

By default, migrations are applied to the same database specified by the `db` [application component](structure-application-components.md).
If you want them to be applied to a different database, you may specify the `db` command-line option like shown below,

```
yii migrate --db=db2
```

The above command will apply migrations to the `db2` database.

Sometimes it may happen that you want to apply *some* of the migrations to one database, while some others to another
database. To achieve this goal, when implementing a migration class you should explicitly specify the DB component
ID that the migration would use, like the following:

```php
use yii\db\Schema;
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

The above migration will be applied to `db2`, even if you specify a different database through the `db` command-line
option. Note that the migration history will still be recorded in the database specified by the `db` command-line option.

If you have multiple migrations that use the same database, it is recommended that you create a base migration class
with the above `init()` code. Then each migration class can extend from this base class.

> Tip: Besides setting the [[yii\db\Migration::db|db]] property, you can also operate on different databases
  by creating new database connections to them in your migration classes. You then use the [DAO methods](db-dao.md)
  with these connections to manipulate different databases.

Another strategy that you can take to migrate multiple databases is to keep migrations for different databases in
different migration paths. Then you can migrate these databases in separate commands like the following:

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

The first command will apply migrations in `@app/migrations/db1` to the `db1` database, the second command
will apply migrations in `@app/migrations/db2` to `db2`, and so on.
