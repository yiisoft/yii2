Autoloading de Classes 
=================

O Yii baseia-se no [mecanismo de autoloading de classe](http://www.php.net/manual/en/language.oop5.autoload.php) para localizar e incluir todos os arquivos de classe necessários. Ele fornece um autoloader de alto desempenho que é compatível com o
[PSR-4 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md).
O autoloader é instalado quando o arquivo `Yii.php` é incluído. 
> Observação: Para simplificar a descrição, nesta seção, nós falaremos apenas sobre autoloading de classe. No entanto, tenha em mente que o conteúdo que estamos descrevendo aqui se aplica a autoloading de interfaces e traits também.


Usando o Autoloader do Yii <span id="using-yii-autoloader"></span>
------------------------

Para fazer uso da autoloader de classe do Yii, você deve seguir duas regras simples ao criar e nomear suas classes:

* Cada classe deve estar debaixo de um [namespace](http://php.net/manual/en/language.namespaces.php) (exemplo. `foo\bar\MyClass`)
* Cada classe deve ser salvo em um arquivo individual cujo caminho é determinado pelo seguinte algoritmo:

```php
// $className é um nome de classe totalmente qualificado sem o primeiro barra invertida
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```
Por exemplo, se um nome de classe e seu namespace for `foo\bar\MyClass`, o [alias](concept-aliases.md) correspondente ao caminho do arquivo da classe seria `@foo/bar/MyClass.php`. Para que este alias seja resolvido em um caminho de arquivo, de outra forma `@foo` ou `@foo/bar` deve ser um [alias raiz](concept-aliases.md#defining-aliases).

Quando se utiliza o [Template Básico de Projetos](start-installation.md), você pode colocar suas classes sob o namespace de nível superior `app` de modo que eles podem ser carregados automaticamente pelo Yii sem a necessidade de definir um novo alias. Isto é porque
`@app` é um [alias predefinido](concept-aliases.md#predefined-aliases), e um nome de classe como `app\components\MyClass` pode ser resolvido no arquivo de classe `AppBasePath/components/MyClass.php`, de acordo com o algoritmo já descrito.

No [Template Avançado de Projetos](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR/README.md), cada camada tem seu própria alias raiz. Por exemplo, a camada front-end tem um alias raiz `@frontend`, enquanto a camada back-end o alias raiz é `@backend`. Como resultado, você pode colocar as classes do front-end debaixo do namespace `frontend` enquanto as classes do back-end estão debaixo do namespace `backend`. Isto permitirá que estas classes sejam carregadas automaticamente pelo Yii autoloader.


Mapeamento de Classes <span id="class-map"></span>
---------

O autoloader de classe do Yii suporta o recurso de *mapeamento de classe*, que mapeia nomes de classe para os caminhos de arquivo das classes correspondentes.
Quando o autoloader está carregando uma classe, ele irá primeiro verificar se a classe se encontra no mapa. Se assim for, o caminho do arquivo correspondente será incluído diretamente, sem mais verificações. Isso faz com que classe seja carregada super rápido. De fato, todas as classes principais do Yii são carregadas automaticamente dessa maneira.
Você pode adcionar uma classe no mapa de classe, armazenado em `Yii::$classMap`, usando:

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

Os [aliases](concept-aliases.md) podem ser usados para especificar caminhos de arquivo de classe. Você deve definir o mapa de classe no processo de [inicialização (bootstrapping)](runtime-bootstrapping.md) de modo que o mapa está pronto antes de suas classes serem usadas.


Usando outros Autoloaders <span id="using-other-autoloaders"></span>
-----------------------

Uma vez que o Yii utiliza o Composer como seu gerenciador de dependência de pacotes, é recomendado que você sempre instale o autoloader do Composer. Se você está utilizando bibliotecas de terceiros que tem seus próprios autoloaders, você também deve instala-los. 
Ao usar o Yii autoloader junto com outros autoloaders, você deve incluir o arquivo `Yii.php` *depois* de todos os outros autoloaders serem instalados. Isso fará com que o Yii autoloader seja o primeiro a responder a qualquer solicitação de autoloading  de classe. Por exemplo, o código a seguir foi extraído do [script de entrada](structure-entry-scripts.md) do [Template Básico de Projeto](start-installation.md). A primeira linha instala o Composer autoloader, enquanto a segunda linha instala o Yii autoloader:

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```
Você pode usar o autoloader do Composer sozinho sem o autoloader do Yii. No entanto, ao fazê-lo, o desempenho do carregamento automático das classes pode ser degradada, e você deve seguir as regras estabelecidas pelo Composer para que suas classes sejam auto carregáveis.

> Informação: Se você não quiser utilizar o autoloader do Yii, você deve criar sua própria versão do arquivo `Yii.php` e incluí-lo no seu [script de entrada](structure-entry-scripts.md).


Autoloading de Classes de Extensões <span id="autoloading-extension-classes"></span>
-----------------------------

O autoloader do Yii é capaz de realizar autoloading de classes de [extensões](structure-extensions.md). O único requisito é que a extensão especifique a seção `autoload` corretamente no seu arquivo `composer.json`. Por favor, consulte a
[documentação do Composer](https://getcomposer.org/doc/04-schema.md#autoload) para mais detalhes sobre especificação de `autoload`.

No caso de você não usar o autoloader do Yii, o autoloader do Composer ainda pode realizar o autoload das classes de extensão para você.

