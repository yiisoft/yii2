Instalando o Yii
================

Você pode instalar o Yii de duas maneiras: usando o gerenciador de pacotes [Composer](https://getcomposer.org/)
ou baixando um arquivo compactado. O primeiro modo é o preferido, já que permite
que você instale novas [extensões](structure-extensions.md) ou atualize o
Yii simplesmente executando um único comando.

A instalação padrão do Yii resulta no download e instalação tanto do framework
quanto de um template de projetos.
Um template de projeto é um projeto funcional do Yii que implementa alguns recursos básicos, tais como: autenticação, formulário de contato, etc.
Este código é organizado de uma forma recomendada. Portanto, ele pode servir
como ponto de partida para seus projetos.

Nesta e nas próximas seções, vamos descrever como instalar o *Template Básico
de Projetos* do Yii e como implementar novas funcionalidades sobre este template.
O Yii fornece ainda outro template chamado [Template Avançado de Projetos](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR/README.md) que é melhor usado em um ambiente de desenvolvimento em equipe e para desenvolver
aplicações com multiplas camadas.

> Info: O Template Básico de Projetos é adequado para o desenvolvimento de
cerca de 90% das aplicações Web. Ele difere do Template Avançado de
Projetos principalmente em como o seu código é organizado. Se você é
novo no Yii, recomendamos fortemente escolher o Template Básico de Projetos
pela sua simplicidade e por manter suficientes funcionalidades.


Instalando via Composer <span id="installing-via-composer"></span>
-----------------------

### Instalando o Composer

Se você ainda não tem o Composer instalado, você pode instalá-lo seguindo as instruções
em [getcomposer.org](https://getcomposer.org/download/). No Linux e no Mac OS X,
você executará os seguintes comandos:

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

No Windows, você baixará e executará o [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Por favor, consulte a seção de [Resolução de Problemas do Composer](https://getcomposer.org/doc/articles/troubleshooting.md) se você encontrar dificuldades. Se você é novo no assunto, nós também recomendamos que leia pelo menos a seção [Uso Básico](https://getcomposer.org/doc/01-basic-usage.md) na documentação do Composer.

Neste guia, todos os comandos do composer assumem que você o tem instaldo [globalmente](https://getcomposer.org/doc/00-intro.md#globally) de modo que ele seja acessível através do comando `composer`. Se em vez disso estiver usando o `composer.phar` no diretório local, você tem que ajustar os comandos de exemplo de acordo.

Se você já tem o Composer instalado, certifique-se de usar uma versão atualizada.
Você pode atualizar o Composer executando o comando `composer self-update`.

> Note: Durante a instalação do Yii, o Composer precisará solicitar muitas informações da API do Github.
> A quantidade de solicitações depende do número de dependências que sua aplicação possui e pode extrapolar a
> **taxa limite da API do Github**. Se você atingir esse limite, o Composer pode pedir a você suas credenciais de login para obter um
> token de acesso à API Github. Em conexões rápidas você pode atingir esse limite antes que o Composer consiga lidar com a situação, então, recomendamos
> configurar um toke de acesso antes de instalar o Yii.
> Por favor, consulte a [documentação do Composer sobre tokens da API Github](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens)
> para instruções de como fazer isso.


### Instalando o Yii <span id="installing-from-composer"></span>

Com o Composer instalado, você pode instalar o Yii executando o seguinte comando
em um diretório acessível pela Web:

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic basico
```

Isto vai instalar a versão estável mais recente do Yii em um diretório chamado `basico`.
Você pode especificar um nome de diretório diferente se quiser.

> Info: Se o comando `composer create-project` falhar, você pode consultar a
> [seção de Resolução de Problemas na documentação do Composer](https://getcomposer.org/doc/articles/troubleshooting.md)
> para verificar erros comuns. Quando tiver corrigido o erro, você pode continuar a instalação abortada por executar o comando
> `composer update` dentro do diretório `basico`.

> Tip: Se em disso você quiser instalar a versão em desenvolvimento mais recente do Yii, use o comando a seguir
> que adiciona uma [opção de estabilidade](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
> ```bash
> composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
> ```
>
> Note que a versão do Yii em desenvolvimento não deve ser usada em produção visto que pode quebrar seu código funcional.


Instalando a partir de um Arquivo Compactado <span id="installing-from-archive-file"></span>
--------------------------------------------

A instalação do Yii a partir de um arquivo compactado envolve três passos:

1. Baixe o arquivo compactado em [yiiframework.com](https://www.yiiframework.com/download/).
2. Descompacte o arquivo baixado em um diretório acessível pela Web.
3. Modifique o arquivo `config/web.php` informando uma chave secreta no item de
configuração `cookieValidationKey` (isto é feito automaticamente se você instalar
o Yii pelo Composer):

    ```php
   // !!! Informe a chave secreta no item a seguir (se estiver vazio) - isto é requerido para a validação do cookie
   'cookieValidationKey' => 'enter your secret key here',
   ```


Outras Opções de Instalação <span id="other-installation-options"></span>
---------------------------

As instruções de instalação acima mostram como instalar o Yii, que também cria
uma aplicação Web básica que funciona imediatamente sem qualquer configuração ou
modificação (*out of the box*).
Esta abordagem é um bom ponto de partida para a maioria dos projetos, seja ele
pequeno ou grande. É especialmente adequado se você acabou de começar a aprender Yii.

No entanto, existem outras opções de instalação disponíveis:

* Se você só quer instalar o núcleo (*core*) do framework e gostaria de construir uma aplicação
  inteira do zero, você pode seguir as instruções em
  [Construindo uma Aplicação a Partir do Zero](tutorial-start-from-scratch.md).
* Se você quiser começar com uma aplicação mais sofisticada, mais adequada ao
  ambiente de desenvolvimento em equipe, você pode considerar instalar o
  [Template Avançado de Projetos](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR/README.md).


Instalando Recursos Estáticos (Assets) <span id="installing-assets"></span>
-----------------

Yii utiliza os pacotes [Bower](https://bower.io/) e/ou [NPM](https://www.npmjs.com/) para a instalação das bibliotecas de recursos estáticos (CSS and JavaScript).
Ele usa composer para obter essa bibliotecas, permitindo que versões de pacotes PHP, CSS e Javascrtip possam ser definidas/instaladas ao mesmo tempo.
Isto é possível por usar ou [asset-packagist.org](https://asset-packagist.org) ou [composer asset plugin](https://github.com/fxpio/composer-asset-plugin).
Por favor, consulta a [documentação sobre Assets](structure-assets.md) para mais detalhes.

Você pode querer gerenciar assets através de clientes nativos do Bower ou NPM, pode querer utilizar CDNs ou até evitar completamente a instalação de recursos estáticos.
Para evitar que recursos estáticos sejam instalados via Composer, adicione o seguinte código ao seu `composer.json`:

```json
"replace": {
    "bower-asset/jquery": ">=1.11.0",
    "bower-asset/inputmask": ">=3.2.0",
    "bower-asset/punycode": ">=1.3.0",
    "bower-asset/yii2-pjax": ">=2.0.0"
},
```

> Note: caso a instalação de recursos estáticos via Composer seja evitada, caberá a você instalar e resolver conflitos de versão ao instalar recursos estáticos (assets).
> Esteja preparado para possíveis inconsistências entre arquivos de recursos estáticos de diferentes extensões.


Verificando a Instalação <span id="verifying-installation"></span>
------------------------

Após a instalação ser concluída, você pode tanto configurar seu servidor web (veja na próxima seção) como usar o
[servidor web embutido do PHP](https://www.php.net/manual/pt_BR/features.commandline.webserver.php) executando o seguinte comando de console no diretório `web`:

```bash
php yii serve
```

> Note: Por padrão o servidor HTTP vai ouvir na porta 8080. Contudo, se essa porta já estiver em uso ou se você pretende servir múltiplas aplicações desta forma, você pode querer especificar qual porta será usada. Para isso,
basta adicionar o argumento `--port`:

```bash
php yii serve --port=8888
```

Você pode usar seu navegador para acessar a aplicação instalada por meio da seguinte URL:

```
http://localhost:8080/
```

![Yii Instalado com Sucesso](images/start-app-installed.png)

Você deverá ver a página de parabenização acima em seu navegador. Se não a vir, por favor, verifique se sua instalação PHP satisfaz os requisitos do Yii. Você pode verificar se os requisitos mínimos são atingidos usando uma das seguintes abordagens:

* Copiar `/requirements.php` para `/web/requirements.php` e então usar um navegador para acessá-lo por meio da URL `http://localhost/requirements.php`
* Executar os seguintes comandos:

  ```bash
  cd basico
  php requirements.php
  ```

Você deve configurar sua instalação PHP de forma a atingir os requisitos mínimos do Yii. A versão mínima do PHP que você deve ter é a 5.4. Mas o ideal seria utilizar a versão mais recente, PHP 7.
Se sua aplicação precisa de um banco de dados, você também deve instalar a [Extensão PDO PHP](https://www.php.net/manual/pt_BR/pdo.installation.php) e o driver de banco de dados correspondente (tal como `pdo_mysql` para bancos de dados MySQL).


Configurando Servidores Web <span id="configuring-web-servers"></span>
------------------------------

> Info: Você pode pular essa subseção por enquanto se estiver fazendo somente um test drive do Yii sem a intenção de publicá-lo em um servidor de produção.

A aplicação instalada de acordo com as instruções acima deve funcionar imediatamente
com um [Servidor HTTP Apache](https://httpd.apache.org/) ou um [Servidor HTTP Nginx](https://nginx.org/),
no Windows, Mac OS X ou Linux usando PHP 5.4 ou superior. O Yii 2.0 também é compatível
com o [HHVM](https://hhvm.com/) do Facebook. No entanto, existem alguns casos extremos em que o HHVM se comporta diferentemente do PHP nativo, então você terá que ter um cuidado extra quando usar o HHVM.

Em um servidor de produção, você pode querer configurar o seu servidor Web de
modo que a aplicação possa ser acessada pela URL `https://www.example.com/index.php`
ao invés de `https://www.example.com/basico/web/index.php`. Tal configuração requer que
você aponte a raiz dos documentos de seu servidor Web para o diretório `basico/web`.
Você também pode querer ocultar o `index.php` da URL, conforme descrito na seção
[Roteamento e Criação de URL](runtime-routing.md). Nessa sub-seção, você
aprenderá como configurar o seu servidor Apache ou Nginx para atingir estes
objetivos.

> Info: Definindo `basico/web` como a raiz dos documentos, você também evita que
  usuários finais acessem o código privado de sua aplicação e os arquivos de
  dados sensíveis que estão armazenados em diretórios no mesmo nível de `basico/web`.
  Negar o acesso a estes outros diretórios é uma melhoria de segurança.

> Info: Se a sua aplicação rodará em um ambiente de hospedagem compartilhada
  onde você não tem permissão para alterar a configuração do seu servidor Web,
  você ainda pode ajustar a estrutura de sua aplicação para uma melhor segurança.
  Por favor, consulte a seção [Ambiente de Hospedagem Compartilhada](tutorial-shared-hosting.md)
  para mais detalhes.


### Configuração do Apache Recomendada <span id="recommended-apache-configuration"></span>

Use a seguinte configuração no arquivo `httpd.conf` do Apache ou em uma
configuração de virtual host. Perceba que você pode deve substituir `caminho/para/basico/web`
com o caminho real para `basico/web`.

```apache
# Torna "basico/web" a raíz de documentos
DocumentRoot "caminho/para/basico/web"

<Directory "caminho/para/basico/web">
    # Usa mod_rewrite para suporte a URLs amigáveis
    RewriteEngine on

    # Se $showScriptName for "false" no UrlManager, impede o acesso a URLs que tenham o nome do script (index.php)
    RewriteRule ^index.php/ - [L,R=404]

    # Se um arquivo ou diretório existe, usa a solicitação diretamente
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # Caso contrário, redireciona para index.php
    RewriteRule . index.php

    # ... outras configurações ...
</Directory>
```


### Configuração do Nginx Recomendada <span id="recommended-nginx-configuration"></span>

Para usar o [Nginx](https://wiki.nginx.org/), você deve ter instalado o PHP como um [FPM SAPI](https://www.php.net/manual/pt_BR/install.fpm.php). Use a seguinte configuração do Nginx,
substituindo `caminho/para/basico/web` com o caminho real para `basico/web` e `mysite.test` com o nome de host real a servir.

```nginx
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /caminho/para/basico/web;
    index       index.php;

    access_log  /caminho/para/basico/log/access.log;
    error_log   /caminho/para/basico/log/error.log;

    location / {
        # Redireciona tudo que não é um arquivo real para index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Descomente para evitar processar chamadas feitas pelo Yii a arquivos estáticos não existentes
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    # Nega acesso a arquivos php no diretório /assets
    location ~ ^/assets/.*\.php$ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~* /\. {
        deny all;
    }
}
```

Ao usar esta configuração, você também deve definir `cgi.fix_pathinfo=0` no arquivo `php.ini`
de modo a evitar muitas chamadas desnecessárias ao comando `stat()` do sistema.

Também perceba que ao rodar um servidor HTTPS, você precisa adicionar `fastcgi_param HTTPS on;`,
de modo que o Yii possa detectar adequadamente se uma conexão é segura.
