Ambiente de Hospedagem Compartilhada
==========================

Ambientes de hospedagem compartilhada geralmente são muito limitados com relação a configuração e estrutura de diretórios. Ainda assim, na maioria dos casos, você pode executar Yii 2.0 em um ambiente de hospedagem compartilhada com poucos ajustes.

Implantação do Template Básico
---------------------------

Uma vez que em um ambiente de hospedagem compartilhada geralmente não há apenas um webroot, use o template básico, se puder. Consulte o [Capítulo Instalando o Yii](start-installation.md) e instale o modelo de projeto básico localmente. Depois de ter sua aplicação funcionando localmente, vamos fazer alguns ajustes para que possa ser hospedado em seu servidor de hospedagem compartilhada.

### Renomear webroot <span id="renaming-webroot"></span>

Ao conectar no seu servidor compartilhado através de FTP ou outros meios, você provavelmente verá algo como a seguir:
 
```
config
logs
www
```

No exemplo acima, `www` é seu diretório raíz do servidor web e pode possuir nomes diferente. Nomes comuns são: `www`,` htdocs`, e `public_html`.

O diretório raíz de nosso template básico é chamado `web`. Antes de enviar a aplicação para seu servidor renomeie seu diretório raiz local de acordo com o do seu servidor, ou seja, de `web` para `www`, `public_html` ou qualquer que seja o nome do seu diretório raíz na hospedagem.

### Diretório raiz FTP precisa ser gravável

Se você possui permissão de escrita no diretório raíz, (onde estão `config`, `logs` e `www`), então faça upload de `assets`, `commands` etc.

### Recursos extras para servidor web <span id="add-extras-for-webserver"></span>

Se o seu servidor web é Apache você precisará adicionar um arquivo `.htaccess` com o seguinte conteúdo em `web` (ou `public_html` ou qualquer outro) (onde o arquivo` index.php` está localizado):

```
Options +FollowSymLinks
IndexIgnore */*

RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# otherwise forward it to index.php
RewriteRule . index.php
```

Se o seu servidor web é Nginx, você não precisa de nenhuma configuração extra.

### Verifique os requisitos

Para executar Yii, o seu servidor web deve atender alguns requisitos. O requisito mínimo é PHP 5.4. Para verificar os requisitos copie o arquivo `requirements.php` da raíz da aplicação para o diretório raíz do servidor web e execute-o através do navegador usando o endereço `http://example.com/requirements.php`. Não se esqueça de apagar o arquivo depois.

Implantação do Template Avançado
---------------------------------

A implantação do Template Avançado para a hospedagem compartilhada é um pouco mais complicada do que o Template Básico, porque ele tem duas webroots, que servidores web da hospedagem compartilhada não suportam. Vamos precisar de ajustar a estrutura de diretórios.

### Mova os scripts de entrada para única raíz

Primeiro de tudo, precisamos de um diretório raíz. Crie um novo diretório e nomeie-o para corresponder à raíz de sua hospedagem, conforme descrito no [Renomear webroot](#renaming-webroot), por exemplo `www` ou `public_html` ou semelhante. Em seguida, crie a seguinte estrutura onde `www` é o diretório raíz de sua hospedagem que você acabou de criar:

```
www
    admin
backend
common
console
environments
frontend
...
```

`www` será nosso diretório frontend, então mova o conteúdo de `frontend/web` para ele. Mova o conteúdo de `backend/web` para `www/admin`. Em ambos os casos você precisará ajustar os caminhos em `index.php` and `index-test.php`.

### Sessões e cookies separados

Originalmente, o backend e frontend destinam-se a ser executado em diferentes domínios. Quando movemos tudo para o mesmo domínio, tanto frontend quanto backend estarão partilhando os mesmos cookies, criando um problema. Para corrigi-lo, ajuste sua configuração backend `backend/config/main.php` da seguinte forma:

```php
'components' => [
    'request' => [
        'csrfParam' => '_backendCSRF',
        'csrfCookie' => [
            'httpOnly' => true,
            'path' => '/admin',
        ],
    ],
    'user' => [
        'identityCookie' => [
            'name' => '_backendIdentity',
            'path' => '/admin',
            'httpOnly' => true,
        ],
    ],
    'session' => [
        'name' => 'BACKENDSESSID',
        'cookieParams' => [
            'path' => '/admin',
        ],
    ],
],
```
