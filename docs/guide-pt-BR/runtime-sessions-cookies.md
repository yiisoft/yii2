Sessões e Cookies
====================

Sessões e cookies permitem que dados sejam persistentes entre várias requisições de usuários. No PHP puro você pode acessá-los através das variáveis globais `$_SESSION` e `$_COOKIE`, respectivamente. Yii encapsula sessões e cookies como objetos e portanto, permite que você possa acessá-los de um modo orientado à objetos com melhorias adicionais úteis.


## Sessões<span id="sessions"></span>

Assim como [requisições](runtime-requests.md) e [respostas](runtime-responses.md), você pode ter acesso a sessões através do [componente de aplicação](structure-application-components.md) `session` que é uma instância de [[yii\web\Session]], por padrão.


### Abrindo e Fechando Sessões <span id="opening-closing-sessions"></span>

Para abrir e fechar uma sessão, você pode fazer o seguinte:

```php
$session = Yii::$app->session;

// verifica se a sessão está pronta para abrir
if ($session->isActive) ...

// abre uma sessão
$session->open();

// fecha uma sessão
$session->close();

// destrói todos os dados registrados em uma sessão.
$session->destroy();
```

Você pode chamar [[yii\web\Session::open()|open()]] and [[yii\web\Session::close()|close()]] várias vezes, sem causar erros; internamente os métodos irão verificar primeiro se a sessão já está aberta.


### Acessando Dados da Sessão <span id="access-session-data"></span>

Para acessar os dados armazenados em sessão, você pode fazer o seguinte:

```php
$session = Yii::$app->session;

// obter uma variável de sessão. Os exemplos abaixo são equivalentes:
$language = $session->get('language');
$language = $session['language'];
$language = isset($_SESSION['language']) ? $_SESSION['language'] : null;

// definir uma variável de sessão. Os exemplos abaixo são equivalentes:
$session->set('language', 'en-US');
$session['language'] = 'en-US';
$_SESSION['language'] = 'en-US';

// remover uma variável de sessão. Os exemplos abaixo são equivalentes:
$session->remove('language');
unset($session['language']);
unset($_SESSION['language']);

// verifica se a variável de sessão existe. Os exemplos abaixo são equivalentes:
if ($session->has('language')) ...
if (isset($session['language'])) ...
if (isset($_SESSION['language'])) ...

// percorrer todas as variáveis de sessão. Os exemplos abaixo são equivalentes:
foreach ($session as $name => $value) ...
foreach ($_SESSION as $name => $value) ...
```

> Observação: Quando você acessa os dados da sessão através do componente `session`, uma sessão será automaticamente aberta caso não tenha sido feito antes. Isso é diferente de acessar dados da sessão através de `$_SESSION`, o que requer uma chamada explícita de `session_start()`.

Ao trabalhar com dados de sessão que são arrays, o componente `session` tem uma limitação que o impede de modificar diretamente um elemento do array. Por exemplo,

```php
$session = Yii::$app->session;

// o seguinte código não funciona
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// o seguinte código funciona:
$session['captcha'] = [
    'number' => 5,
    'lifetime' => 3600,
];

// o seguinte código também funciona:
echo $session['captcha']['lifetime'];
```

Você pode usar uma das seguintes soluções para resolver este problema:

```php
$session = Yii::$app->session;

// use diretamente $_SESSION (certifique-se que Yii::$app->session->open() tenha sido chamado)
$_SESSION['captcha']['number'] = 5;
$_SESSION['captcha']['lifetime'] = 3600;

// obter todo o array primeiro, modificá-lo e depois salvá-lo
$captcha = $session['captcha'];
$captcha['number'] = 5;
$captcha['lifetime'] = 3600;
$session['captcha'] = $captcha;

// use ArrayObject em vez de array
$session['captcha'] = new \ArrayObject;
...
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// armazenar dados de array utilizando chaves com um prefixo comum
$session['captcha.number'] = 5;
$session['captcha.lifetime'] = 3600;
```

Para um melhor desempenho e legibilidade do código, recomendamos a última solução alternativa. Isto é, em vez de armazenar um array como uma variável de sessão única, você armazena cada elemento do array como uma variável de sessão que compartilha o mesmo prefixo de chave com outros elementos do array.


### Armazenamento de Sessão Personalizado <span id="custom-session-storage"></span>

A classe padrão [[yii\web\Session]] armazena dados da sessão como arquivos no servidor. Yii Também fornece as seguintes classes de sessão implementando diferentes formas de armazenamento:

* [[yii\web\DbSession]]: armazena dados de sessão em uma tabela no banco de dados.
* [[yii\web\CacheSession]]: armazena dados de sessão em um cache com a ajuda de um [cache component](caching-data.md#cache-components) configurado.
* [[yii\redis\Session]]: armazena dados de sessão utilizando [redis](https://redis.io/) como meio de armazenamento.
* [[yii\mongodb\Session]]: armazena dados de sessão em um [MongoDB](https://www.mongodb.com/).

Todas essas classes de sessão suportam o mesmo conjunto de métodos da API. Como resultado, você pode mudar para uma classe de armazenamento de sessão diferente, sem a necessidade de modificar o código da aplicação que usa sessões.

> Observação: Se você deseja acessar dados de sessão via`$_SESSION` enquanto estiver usando armazenamento de sessão personalizado, você deve certificar-se de que a sessão já foi iniciada por [[yii\web\Session::open()]]. Isso ocorre porque os manipuladores de armazenamento de sessão personalizada são registrados dentro deste método.

Para saber como configurar e usar essas classes de componentes, por favor consulte a sua documentação da API. Abaixo está um exemplo que mostra como configurar [[yii\web\DbSession]] na configuração da aplicação para usar uma tabela do banco de dados para armazenamento de sessão:

```php
return [
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // ID do componente de aplicação da conexão DB. O padrão é 'db'.
            // 'sessionTable' => 'my_session', // nome da tabela de sessão. O padrão é 'session'.
        ],
    ],
];
```

Você também precisa criar a tabela de banco de dados a seguir para armazenar dados de sessão:

```sql
CREATE TABLE session
(
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```
onde 'BLOB' refere-se ao tipo BLOB do seu DBMS preferido. Estes são os tipos de BLOB que podem ser usados para alguns SGBD populares:

- MySQL: LONGBLOB
- PostgreSQL: BYTEA
- MSSQL: BLOB

> Observação: De acordo com a configuração `session.hash_function` no php.ini , pode ser necessário ajustar o tamanho da coluna `id`. Por exemplo, se a configuração for `session.hash_function=sha256`, você deve usar um tamanho de 64 em vez de 40.


### Dados Flash <span id="flash-data"></span>

Dados flash é um tipo especial de dados de sessão que, uma vez posto em uma requisição, só estarão disponíveis durante a próxima requisição e serão automaticamente excluídos depois. Dados flash são mais comumente usados para implementar mensagens que só devem ser exibidas para os usuários finais, uma vez, tal como uma mensagem de confirmação exibida após um usuário submeter um formulário com sucesso.

Você pode definir e acessar dados de flash através do componente da aplicação `session`. Por exemplo,

```php
$session = Yii::$app->session;

// Request #1
// defini uma mensagem flash chamada "postDeleted"
$session->setFlash('postDeleted', 'You have successfully deleted your post.');

// Request #2
// exibe uma mensagem flash chamada "postDeleted"
echo $session->getFlash('postDeleted');

// Request #3
// $result será falso uma vez que a mensagem flash foi automaticamente excluída
$result = $session->hasFlash('postDeleted');
```

Assim como os dados da sessão regular, você pode armazenar dados arbitrários como os dados flash.

Quando você chama [[yii\web\Session::setFlash()]], ele substituirá todos os dados flash existente que tenha o mesmo nome.
Para acrescentar novos dados flash para uma mensagem existente com o mesmo nome, você pode chamá [[yii\web\Session::addFlash()]].
Por exemplo:

```php
$session = Yii::$app->session;

// Request #1
// adicionar algumas mensagens flash com o nome de "alerts"
$session->addFlash('alerts', 'You have successfully deleted your post.');
$session->addFlash('alerts', 'You have successfully added a new friend.');
$session->addFlash('alerts', 'You are promoted.');

// Request #2
// $alerts é um array de mensagens flash com o nome de "alerts"
$alerts = $session->getFlash('alerts');
```

> Observação: Tente não usar [[yii\web\Session::setFlash()]] junto com [[yii\web\Session::addFlash()]] para dados flash com o mesmo nome. Isto porque o último método coloca os dados flash automaticamente em um array, de modo que ele pode acrescentar novos dados flash com o mesmo nome. Como resultado, quando você chamar [[yii\web\Session::getFlash()]], você pode às vezes achar que está recebendo um array, quando na verdade você está recebendo uma string, dependendo da ordem da invocação destes dois métodos.

> Dica: Para mostrar mensagens flash você pode usar o widget [[yii\bootstrap\Alert|bootstrap Alert]] da seguinte forma:
>
> ```php
> echo Alert::widget([
>    'options' => ['class' => 'alert-info'],
>    'body' => Yii::$app->session->getFlash('postDeleted'),
> ]);
> ```


## Cookies <span id="cookies"></span>

Yii representa cada cookie como um objeto de [[yii\web\Cookie]]. Ambos, [[yii\web\Request]] e [[yii\web\Response]] mantém uma coleção de cookies através da propriedade chamada `cookies`. A coleção de cookie no primeiro representa os cookies submetidos na requisição, enquanto a coleção de cookie no último representa os cookies que são para serem enviados ao usuário.


### Lendo Cookies <span id="reading-cookies"></span>

Você pode obter os cookies na requisição corrente usando o seguinte código:

```php
// pega a coleção de cookie  (yii\web\CookieCollection) do componente "request"
$cookies = Yii::$app->request->cookies;

// pega o valor do cookie "language". se o cookie não existir, retorna "en" como o valor padrão.
$language = $cookies->getValue('language', 'en');

// um caminho alternativo para pegar o valor do cookie "language"
if (($cookie = $cookies->get('language')) !== null) {
    $language = $cookie->value;
}

// você também pode usar $cookies como um array
if (isset($cookies['language'])) {
    $language = $cookies['language']->value;
}

// verifica se existe um cookie "language"
if ($cookies->has('language')) ...
if (isset($cookies['language'])) ...
```


### Enviando Cookies <span id="sending-cookies"></span>

Você pode enviar cookies para o usuário final utilizando o seguinte código:

```php
// pega a coleção de cookie (yii\web\CookieCollection) do componente "response"
$cookies = Yii::$app->response->cookies;

// adicionar um novo cookie a resposta que será enviado
$cookies->add(new \yii\web\Cookie([
    'name' => 'language',
    'value' => 'zh-CN',
]));

// remove um cookie
$cookies->remove('language');
// outra alternativa para remover um cookie
unset($cookies['language']);
```

Além das propriedades [[yii\web\Cookie::name|name]], [[yii\web\Cookie::value|value]] mostradas nos exemplos acima, a classe [[yii\web\Cookie]] também define outras propriedades para representar plenamente todas as informações de cookie disponíveis, tal como [[yii\web\Cookie::domain|domain]], [[yii\web\Cookie::expire|expire]]. Você pode configurar essas propriedades conforme necessário para preparar um cookie e, em seguida, adicioná-lo à coleção de cookie da resposta.

> Observação: Para melhor segurança, o valor padrão de [[yii\web\Cookie::httpOnly]] é definido para `true`. Isso ajuda a reduzir o risco de um script do lado do cliente acessar o cookie protegido (se o browser suporta-lo). Você pode ler o [httpOnly wiki article](https://owasp.org/www-community/HttpOnly) para mais detalhes.


### Validação de Cookie <span id="cookie-validation"></span>

Quando você está lendo e enviando cookies através dos componentes `request` e `response` como mostrado nas duas últimas subseções, você aproveita a segurança adicional de validação de cookie que protege que os cookies sejam modificados no lado do cliente. Isto é conseguido através da assinatura de cada cookie com um hash string, que permite a aplicação dizer se o cookie foi modificado no lado do cliente. Se assim for, o cookie não será acessível através do [[yii\web\Request::cookies|cookie collection]] do componente `request`.

> Observação: Validação de cookie apenas protege os valores dos cookies de serem modificados. Se um cookie não for validado, você ainda pode acessá-lo através de `$_COOKIE`. Isso ocorre porque as bibliotecas de terceiros podem manipular os cookies de forma independente que não envolve a validação de cookie.

Validação de cookie é habilitada por padrão. Você pode desabilitá-la definindo a propriedade [[yii\web\Request::enableCookieValidation]] para `false`, entretanto recomendamos fortemente que você não o faça.

> Observação: Cookies que são enviados/recebidos diretamente através de `$_COOKIE` e `setcookie()` NÃO serão validados.

Ao usar a validação de cookie, você deve especificar uma [[yii\web\Request::cookieValidationKey]] que será usada para gerar o supracitado hash strings. Você pode fazê-lo através da configuração do componente `request` na configuração da aplicação:

```php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'fill in a secret key here',
        ],
    ],
];
```

> Observação: [[yii\web\Request::cookieValidationKey|cookieValidationKey]] é fundamental para a segurança da sua aplicação. Ela só deve ser conhecida por pessoas de sua confiança. Não armazená-la no sistema de controle de versão.
