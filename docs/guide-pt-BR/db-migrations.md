Migrações de Dados (Migrations)
==================

Durante o curso de desenvolvimento e manutenção de uma aplicação orientada a banco de dados, a estrutura de banco sendo usada evolui ao mesmo tempo em que o código. Por exemplo, durante o desenvolvimento de uma aplicação, a criação de uma nova tabela pode ser necessária; após ser feito o deploy da aplicação em produção, pode ser descoberto que um índice deveria ser criado para melhorar a performance de alguma query; entre outros. Como a mudança de uma estrutura de banco de dados normalmente necessita de alguma mudança no código, o Yii suporta a então chamada funcionalidade de *migração de dados* que permite que você mantenha um registro das mudanças feitas no banco de dados em termos de *migrações de dados* que são versionadas em conjunto com o código fonte da aplicação.

Os seguintes passos mostram como uma migração de dados pode ser usada pela equipe durante o desenvolvimento:

1. João cria uma nova migração (ex. cria uma nova tabela, muda a definição de uma coluna, etc.).
2. João comita a nova migração no sistema de controle de versão (ex. Git, Mercurial).
3. Pedro atualiza seu repositório a partir do sistema de controle de versão e recebe a nova migração.
4. Pedro aplica a nova migração ao seu banco de dados local em seu ambiente de desenvolvimento, e assim, sincronizando seu banco de dados para refletir as mudanças que João fez.

E os seguintes passos mostram como fazer o deploy para produção de uma nova versão:

1. Luiz cria uma nova tag para o repositório do projeto que contem algumas novas migrações de dados.
2. Luiz atualiza o código fonte no servidor em produção para a tag criada.
3. Luiz aplica todas as migrações de dados acumuladas para o banco de dados em produção. 

O Yii oferece um conjunto de ferramentas de linha de comando que permitem que você:

* crie novas migrações;
* aplique migrações;
* reverta migrações;
* reaplique migrações;
* exiba um histórico das migrações;

Todas estas ferramentas são acessíveis através do comando `yii migrate`. Nesta seção nós iremos descrever em detalhes como realizar várias tarefas usando estas ferramentas. Você também pode descobrir como usar cada ferramenta através do comando de ajuda `yii help migrate`.

> Observação: os migrations (migrações) podem afetar não só o esquema do banco de dados, mas também ajustar os dados existentes para se conformar ao novo esquema, como criar novas hierarquias de RBAC ou limpar dados de cache.
   

## Criando Migrações <span id="creating-migrations"></span>

Para criar uma nova migração, execute o seguinte comando:

```
yii migrate/create <nome>
```

O argumento obrigatório `nome` serve como uma breve descrição sobre a nova migração. Por exemplo, se
a migração é sobre a criação de uma nova tabela chamada *noticias*, você pode usar o nome `criar_tabela_noticias`
e executar o seguinte comando:

```
yii migrate/create criar_tabela_noticias
```

> Observação: Como o argumento `nome` será usado como parte do nome da classe de migração gerada, este deve conter apenas letras, dígitos, e/ou underline. 

O comando acima criará um novo arquivo contendo uma classe PHP chamada `m150101_185401_criar_tabela_noticias.php`
na pasta `@app/migrations`. O arquivo contém o seguinte código que declara a classe de migração
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

Cada migração de dados é definida como uma classe PHP estendida de [[yii\db\Migration]]. O nome da classe de migração é automaticamente gerado no formato `m<YYMMDD_HHMMSS>_<Nome>`, onde 

* `<YYMMDD_HHMMSS>` refere-se a data UTC em que o comando de criação da migração foi executado.
* `<Nome>` é igual ao valor do argumento `nome` que você passou no comando.

Na classe de migração, é esperado que você escreva no método `up()` as mudanças a serem feitas na estrutura do banco de dados.
Você também pode escrever códigos no método `down()` para reverter as mudanças feitas por `up()`. O método `up()` é invocado quando você atualiza o seu banco de dados com esta migração, enquanto o método `down()` é invocado quando você reverte as mudanças no banco. O seguinte código mostra como você pode implementar a classe de migração para criar a tabela `noticias`: 

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
  você possivelmente não será capaz de recuperar este registro com o método `down()`. Em alguns casos, você pode ter 
  tido muita preguiça e não ter implementado o método `down()`, porque não é muito comum reverter migrações de dados.
  Neste caso, você deve retornar `false` no método `down()` para indicar que a migração não é reversível.

A classe base [[yii\db\Migration]] expõe a conexão ao banco através da propriedade [[yii\db\Migration::db|db]].
Você pode usá-la para manipular o esquema do banco de dados usando os métodos como descritos em [Trabalhando com um Esquema de Banco de Dados](db-dao.md#database-schema).

Ao invés de usar tipos físicos, ao criar uma tabela ou coluna, você deve usar *tipos abstratos* para que
suas migrações sejam independentes do SGBD. A classe [[yii\db\Schema]] define uma gama de constantes para
representar os tipos abstratos suportados. Estas constantes são nomeadas no formato `TYPE_<NOME>`. Por exemplo,
`TYPE_PK` refere-se ao tipo chave primária auto incrementável; `TYPE_STRING` refere-se ao típo string. 
Quando a migração for aplicada a um banco de dados em particular, os tipos abstratos serão traduzidos nos
respectivos tipos físicos. No caso do MySQL, `TYPE_PK` será traduzida para 
`int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`, enquanto `TYPE_STRING` será `varchar(255)`. 

Você pode adicionar algumas constraints ao usar tipos abstratos. No exemplo acima, ` NOT NULL` é adicionado
a `Schema::TYPE_STRING` para especificar que a coluna não pode ser nula.

> Observação: O mapeamento entre tipos abstratos e tipos físicos é especificado pela propriedade [[yii\db\QueryBuilder::$typeMap|$typeMap]] em cada classe `QueryBuilder`.


### Migrações Transacionais <span id="transactional-migrations"></span>

Ao realizar migrações de dados complexas, é importante assegurar que cada migração terá sucesso ou falhará 
por completo para que o banco não perca sua integridade e consistência. Para atingir este objetivo, recomenda-se que
você encapsule suas operações de banco de dados de cada migração em uma [transação](db-dao.md#performing-transactions-).

Um jeito mais fácil de implementar uma migração transacional é colocar o seu código de migração nos métodos `safeUp()` e `safeDown()`. Estes métodos diferem de `up()` e `down()` porque eles estão implicitamente encapsulados em uma transação. Como resultado, se qualquer operação nestes métodos falhar, todas as operações anteriores sofrerão roll back
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

Observe que normalmente quando você realiza múltiplas operações em `safeUp()`, você deverá reverter a ordem de execução
em `safeDown()`. No exemplo acima nós primeiramente criamos a tabela e depois inserimos uma túpla em `safeUp()`; enquanto
em `safeDown()` nós primeiramente apagamos o registro e depois eliminamos a tabela.

> Observação: Nem todos os SGBDs suportam transações. E algumas requisições de banco não podem ser encapsuladas em uma transação. Para alguns exemplos, referir a [commit implícito](https://dev.mysql.com/doc/refman/5.1/en/implicit-commit.html). Se este for o caso, implemente os métodos `up()` e `down()`.


### Métodos de Acesso ao Banco de Dados <span id="db-accessing-methods"></span>

A classe base [[yii\db\Migration]] entrega vários métodos que facilitam o acesso e a manipulação de 
bancos de dados. Você deve achar que estes métodos são nomeados similarmente a [métodos DAO](db-dao.md) encontrados 
na classe [[yii\db\Command]]. Por exemplo, o método [[yii\db\Migration::createTable()]] permite que você crie uma 
nova tabela assim como [[yii\db\Command::createTable()]] o faz.

O benefício ao usar os métodos encontrados em [[yii\db\Migration]] é que você não precisa criar explícitamente
instancias de [[yii\db\Command]] e a execução de cada método automaticamente exibirá mensagens úteis que dirão
a você quais operações estão sendo feitas e quanto tempo elas estão durando.

Abaixo está uma lista de todos estes métodos de acesso ao banco de dados:

* [[yii\db\Migration::execute()|execute()]]: executando um SQL
* [[yii\db\Migration::insert()|insert()]]: inserindo um novo registro
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

> Observação: [[yii\db\Migration]] não possui um método de consulta ao banco de dados. Isto porque você normalmente não precisará exibir informações extras ao recuperar informações de um banco de dados. E além disso você pode usar o poderoso [Query Builder](db-query-builder.md) para construir e executar consultas complexas.


## Aplicando Migrações <span id="applying-migrations"></span>

Para atualizar um banco de dados para a sua estrutura mais atual, você deve aplicar todas as migrações disponíveis usando o seguinte comando:

```
yii migrate
```

Este comando listará todas as migrações que não foram alicadas até agora. Se você confirmar que deseja aplicar
estas migrações, cada nova classe de migração executará os métodos `up()` ou `safeUp()` um após o outro, na 
ordem relacionada à data marcada em seus nomes. Se qualquer uma das migrações falhar, o comando terminará sem aplicar
o resto das migrações. 

Para cada migração aplicada com sucesso, o comando inserirá um registro numa tabela no banco de dados chamada
`migration` para registrar uma aplicação de migração. Isto permitirá que a ferramenta de migração identifique
quais migrações foram aplicadas e quais não foram.

> Observação: Esta ferramenta de migração automaticamente criará a tabela `migration` no banco de dados especificado pela opção do comando [[yii\console\controllers\MigrateController::db|db]]. Por padrão, o banco de dados é especificado por `db` em [Componentes de Aplicação](structure-application-components.md).

Eventualmente, você desejará aplicar apenas uma ou algumas migrações, em vez de todas as disponíveis.
Você pode fazê-lo especificando o número de migrações que deseja aplicar ao executar o comando.
Por exemplo, o comando a seguir tentará aplicar as próximas 3 migrações disponíveis:
  
```
yii migrate 3
```
Você também pode especificar para qual migração em particular o banco de dados deve ser migrado
usando o comando `migrate/to` em um dos formatos seguintes:

```
yii migrate/to 150101_185401                        # usando a marcação de data para especificar a migração
yii migrate/to "2015-01-01 18:54:01"                # usando uma string que pode ser analisada por strtotime()
yii migrate/to m150101_185401_criar_tabela_noticias # usando o nome completo
yii migrate/to 1392853618                           # usando uma marcação de data no estilo UNIX
```
Se existirem migrações mais recentes do que a especificada, elas serão todas aplicadas antes da migração definida.

Se a migração especificada já tiver sido aplicada, qualquer migração posterior já aplicada será revertida.


## Revertendo Migrações <span id="reverting-migrations"></span>

Para reverter uma ou múltiplas migrações que tenham sido aplicadas antes, você pode executar o seguinte comando:

```
yii migrate/down     # reverter a última migração aplicada
yii migrate/down 3   # reverter as 3 últimas migrações aplicadas
```

> Observação: Nem todas as migrações são reversíveis. Tentar reverter tais migrações causará um erro que cancelará todo o processo de reversão.


## Refazendo Migrações <span id="redoing-migrations"></span>

Refazer as migrações significa primeiramente reverter migrações especificadas e depois aplicá-las novamente.
Isto pode ser feito da seguinte maneira:

```
yii migrate/redo        # refazer a última migração aplicada 
yii migrate/redo 3      # refazer as 3 últimas migrações aplicadas
```

> Observação: Se a migração não for reversível, você não poderá refazê-la.


## Listando Migrações <span id="listing-migrations"></span>

Para listar quais migrações foram aplicadas e quais não foram, você deve usar os seguintes comandos:

```
yii migrate/history     # exibir as 10 últimas migrações aplicadas
yii migrate/history 5   # exibir as 5 últimas migrações aplicadas
yii migrate/history all # exibir todas as migrações aplicadas

yii migrate/new         # exibir as 10 primeiras novas migrações
yii migrate/new 5       # exibir as 5 primeiras novas migrações
yii migrate/new all     # exibir todas as novas migrações
```


## Modificando o Histórico das Migrações <span id="modifying-migration-history"></span>

Ao invés de aplicar ou reverter migrações, pode ser que você queira apenas definir que o seu banco de dados
foi atualizado para uma migração em particular. Isto normalmente acontece quando você muda manualmente o banco
de dados para um estado em particular, e não deseja que as mudanças para aquela migração sejam reaplicadas
posteriormente. Você pode alcançar este objetivo com o seguinte comando:

```
yii migrate/mark 150101_185401                        # usando a marcação de data para especificar a migração
yii migrate/mark "2015-01-01 18:54:01"                # usando uma string que pode ser analisada por strtotime()
yii migrate/mark m150101_185401_criar_tabela_noticias # usando o nome completo
yii migrate/mark 1392853618                           # usando uma marcação de data no estilo UNIX
```
O comando modificará a tabela `migration` adicionando ou deletando certos registros para indicar que o banco
de dados sofreu as migrações especificadas. Nenhuma migração será aplicada ou revertida por este comando.


## Customizando Migrações <span id="customizing-migrations"></span>

Existem várias maneiras de customizar o comando de migração.


### Usando Opções na Linha de Comando <span id="using-command-line-options"></span>

O comando de migração vem com algumas opções de linha de comando que podem ser usadas para customizar o seu comportamento:

* `interactive`: boolean (o padrão é `true`), especifica se as migrações serão executadas em modo interativo.
  Quando for `true`, ao usuário será perguntado se a execução deve continuar antes de o comando executar certas ações. 
  Você provavelmente marcará isto para falso se o comando estiver sendo feito em algum processo em segundo plano.

* `migrationPath`: string (o padrão é `@app/migrations`), especifica o diretório em que os arquivos das classes de migração estão. Isto pode ser especificado ou como um diretório ou como um [alias](concept-aliases.md).
  Observe que o diretório deve existir, ou o comando disparará um erro. 

* `migrationTable`: string (o padrão é `migration`), especifica o nome da tabela no banco de dados para armazenar o histórico das migrações. A tabela será automaticamente criada pelo comando caso não exista.
  Você também pode criá-la manualmente usando a estrutura `version varchar(255) primary key, apply_time integer`.

* `db`: string (o padrão é `db`), especifica o banco de dados do [componente de aplicação](structure-application-components.md).
  Representa qual banco sofrerá as migrações usando este comando.

* `templateFile`: string (o padrão é `@yii/views/migration.php`), especifica o caminho do arquivo de modelo que é usado para gerar um esqueleto para os arquivos das classes de migração. Isto pode ser especificado por um caminho de arquivo ou por um [alias](concept-aliases.md). O arquivo modelo é um script PHP em que você pode usar uma variával pré-definida `$className` para obter o nome da classe de migração.

O seguinte exemplo exibe como você pode usar estas opções.

Por exemplo, se nós quisermos migrar um módulo `forum` cujo os arquivos de migração estão localizados dentro da pasta `migrations` do módulo, nós podemos usar o seguinte comando:

```
# migrate the migrations in a forum module non-interactively
yii migrate --migrationPath=@app/modules/forum/migrations --interactive=0
```


### Configurando o Comando Globalmente <span id="configuring-command-globally"></span>

Ao invés de fornecer opções todas as vezes que você executar o comando de migração,
você pode configurá-lo de uma vez por todas na configuração da aplicação como exibido a seguir:

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

Com a configuração acima, toda a vez que você executar o comando de migração, a tabela `backend_migration`
será usada para gravar o histórico de migração. Você não precisará mais fornecê-la através da opção `migrationTable`.


## Migrando Múltiplos Bancos De Dados <span id="migrating-multiple-databases"></span>

Por padrão, as migrações são aplicadas no mesmo banco de dados especificado por `db` do [componente de aplicação](structure-application-components.md).
Se você quiser que elas sejam aplicadas em um banco de dados diferente, você deve especificar na opção `db` como exibido a seguir:

```
yii migrate --db=db2
```

O comando acima aplicará as migrações para o banco de dados `db2`.

Algumas vezes pode ocorrer que você queira aplicar *algumas* das migrações para um banco de dados, e outras para
outro banco de dados. Para atingir este objetivo, ao implementar uma classe de migração você deve especificar a
ID do componente DB que a migração usará, como o seguinte:

```php
use yii\db\Schema;
use yii\db\Migration;

class m150101_185401_criar_tabela_noticias extends Migration
{
    public function init()
    {
        $this->db = 'db2';
        parent::init();
    }
}
```

A migração acima será aplicada a `db2`, mesmo que você especifique um banco de dados diferente através da opção `db`. Observe que o histórico da migração continuará sendo registrado no banco especificado pela opção `db`.
Se você tiver múltiplas migrações que usam o mesmo banco de dados, é recomenda-se criar uma classe de migração
base com o código acima em `init()`. Então cada classe de migração poderá ser estendida desta classe base. 

> Dica: Apesar de definir a propriedade [[yii\db\Migration::db|db]], você também pode operar em diferentes bancos
  de dados ao criar novas conexões de banco para eles em sua classe de migração. Você então usará os [métodos DAO](db-dao.md)
  com estas conexões para manipular diferentes bancos de dados.

Outra estratégia que você pode seguir para migrar múltiplos bancos de dados é manter as migrações para diferentes bancos
de dados em diferentes pastas de migrações. Então você poderá migrar estes bancos de dados em comandos separados como os seguintes:

```
yii migrate --migrationPath=@app/migrations/db1 --db=db1
yii migrate --migrationPath=@app/migrations/db2 --db=db2
...
```

O primeiro comando aplicará as migrações em `@app/migrations/db1` para o banco de dados `db1`, e o segundo comando
aplicará as migrações em `@app/migrations/db2` para `db2`, e assim sucessivamente. 
