Aliases (Apelidos)
=======

Aliases são usados para representar caminhos de arquivos ou URLs de forma que você não precise acoplar o código usando caminhos absolutos ou URLs em seu projeto. Um alias deve começar com o caractere `@` para se diferenciar de um caminho de arquivo normal ou URL. O Yii já possui vários aliases predefinidos disponíveis. 
Por exemplo, o alias `@yii` representa o local em que o framework Yii foi instalado; `@web` representa a URL base para a aplicação que está sendo executada no momento. 


Definindo Aliases <span id="defining-aliases"></span>
----------------

Você pode definir um alias para um caminho de arquivo ou URL chamando [[Yii::setAlias()]]:

```php
// um alias de um caminho de arquivo
Yii::setAlias('@foo', '/caminho/para/foo');

// um alias de uma URL
Yii::setAlias('@bar', 'http://www.exemplo.com.br');
```

> Observação: O caminho do arquivo ou URL sendo *apelidado* (aliased) *não* necessariamente refere-se a um arquivo ou a recursos existentes.

Dado um alias definido, você pode derivar um novo alias (sem a necessidade de chamar [[Yii::setAlias()]]) apenas acrescentando uma barra `/` seguido de um ou mais segmentos de caminhos de arquivos. Os aliases definidos através de [[Yii::setAlias()]] tornam-se o *alias raiz* (root alias), enquanto que aliases derivados dele, tornam-se *aliases derivados*. Por exemplo, `@foo` é um *alias raiz* (root alias), enquanto `@foo/bar/arquivo.php` é um alias derivado.

Você pode definir um alias usando outro alias (tanto raiz quanto derivado):

```php
Yii::setAlias('@foobar', '@foo/bar');
```

Aliases raiz são normalmente definidos durante o estágio de [inicialização](runtime-bootstrapping.md).
Por exemplo, você pode chamar [[Yii::setAlias()]] no [script de entrada](structure-entry-scripts.md).
Por conveniência, as [aplicações](structure-applications.md) difinem uma propriedade `aliases` que você pode configurar na [configuração](concept-configurations.md) da aplicação:

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/caminho/para/foo',
        '@bar' => 'http://www.exemplo.com.br',
    ],
];
```


Resolvendo Aliases <span id="resolving-aliases"></span>
-----------------

Você pode chamar [[Yii::getAlias()]] em um alias raiz para resolver o caminho de arquivo ou URL que ele representa.
O mesmo método pode resolver também um alias derivado em seu caminho de arquivo ou URL correspondente.

```php
echo Yii::getAlias('@foo');               // exibe: /caminho/para/foo
echo Yii::getAlias('@bar');               // exibe: http://www.example.com
echo Yii::getAlias('@foo/bar/arquivo.php');  // exibe: /caminho/para/foo/bar/arquivo.php
```

O caminho/URL representado por um alias derivado é determinado substituindo a parte do alias raiz com o seu caminho/URL correspondente.

> Observação: O método [[Yii::getAlias()]] não checa se o caminho/URL resultante refere-se a um arquivo ou recursos existentes.

Um alias raiz pode também conter caracteres de barra `/`. O método [[Yii::getAlias()]] é inteligente o suficiente
para descobrir que parte de um alias é um alias raiz e assim determina o caminho de arquivo ou URL correspondente:

```php
Yii::setAlias('@foo', '/caminho/para/foo');
Yii::setAlias('@foo/bar', '/caminho2/bar');
Yii::getAlias('@foo/test/arquivo.php');  // exibe: /caminho/para/foo/test/arquivo.php
Yii::getAlias('@foo/bar/arquivo.php');   // exibe: /caminho2/bar/arquivo.php
```

Se `@foo/bar` não estivesse definido como um alias raiz, a última chamada exibiria `/caminho/para/foo/bar/arquivo.php`.


Usando Aliases <span id="using-aliases"></span>
-------------

Aliases são reconhecidos em muitos lugares no Yii sem a necessidade de chamar [[Yii::getAlias()]] para convertê-los em caminhos e URLs. Por exemplo, [[yii\caching\FileCache::cachePath]] pode aceitar tanto um caminho de arquivo quanto um alias representando o caminho do arquivo, graças ao prefíxo `@` que nos permite diferenciar um caminho de arquivo de um alias.

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

Por favor, consulte a documentação da API para saber se o parâmetro de uma propriedade ou método suporta aliases.


Aliases Predefinidos <span id="predefined-aliases"></span>
------------------

O Yii já predefine uma gama de aliases para referenciar facilmente caminhos de arquivos e URLs comumente usados:

- `@yii`, o diretório onde o arquivo `BaseYii.php` está localizado (também chamado de diretório do framework).
- `@app`, o [[yii\base\Application::basePath|caminho base]] da aplicação sendo executada no momento.
- `@runtime`, o [[yii\base\Application::runtimePath|caminho runtime]] da aplicação sendo executada no momento.
- `@webroot`, o diretório webroot da aplicação sendo executada no momento. Este é determinado baseado no diretório
   contendo o [script de entrada](structure-entry-scripts.md).
- `@web`, a URL base da aplicacão sendo executada no momento. Esta tem o mesmo valor de [[yii\web\Request::baseUrl]].
- `@vendor`, o [[yii\base\Application::vendorPath|caminho da pasta vendor do Composer]]. 
   Seu padrão é `@app/vendor`.
- `@bower`, o caminho raiz que contém os [pacotes bower](http://bower.io/). Seu padrão é `@vendor/bower`.
- `@npm`, o caminho raiz que contém [pacotes npm](https://www.npmjs.org/). Seu padrão é `@vendor/npm`.

O alias `@yii` é definido quando você inclui o arquivo `Yii.php` em seu [script de entrada](structure-entry-scripts.md).
O resto dos aliases são definidos no construtor da aplicação ao aplicar a [configuração](concept-configurations.md) da aplicação.


Aliases para Extensões <span id="extension-aliases"></span>
-----------------

Um alias é automaticamente definido para cada [extensão](structure-extensions.md) que for instalada através do Composer.
Cada alias é nomeado a partir do namespace raiz da extensão como declarada em seu arquivo `composer.json`, e cada alias representa o diretório raiz de seu pacote. Por exemplo, se você instalar a extensão `yiisoft/yii2-jui`, você terá automaticamente o alias `@yii/jui` definido durante o estágio de [inicialização](runtime-bootstrapping.md), equivalente a:

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
