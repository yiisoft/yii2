Instalando o Yii
================

Você pode instalar o Yii de duas maneiras, usando o gerenciador de pacotes [Composer](https://getcomposer.org/)
ou baixando um arquivo compactado. O primeiro modo é o preferido, já que permite
que você instale novas [extensões](structure-extensions.md) ou atualize o
Yii simplesmente executando um único comando.

A instalação do Yii padrão resulta no download e instalação tanto do framework 
quanto de um template de projetos.
Um template de projetos é uma aplicação do Yii implementando algumas recursos básicos,
como a autenticação, o formulário de contato, etc.
Este código é organizado de uma forma recomendada. No entanto, ele pode servir 
como ponto de partida para seus projetos.

Nesta e nas próximas seções, iremos descrever como instalar o *Template Básico 
de Projetos* do Yii e como implementar novas funcionalidades em cima deste template.
O Yii também fornece um outro template chamado de [Template Avançado de Projetos](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR/README.md) que é melhor usado em uma equipe de desenvolvimento que desenvolvem 
aplicações de multiplas camadas.

> Informação: O Template Básico de Projetos é adequado para o desenvolvimento de 
cerca de 90% das aplicações Web. Este template difere do Template Avançado de 
Projetos principalmente na forma de como o seu código é organizado. Se você é 
novo no Yii, recomendamos fortemente em escolher o Template Básico de Projetos 
pela sua simplicidade além de ter funcionalidades o suficiente.


Instalando via Composer <span id="installing-via-composer"></span>
-----------------------

Se você já não tiver o Composer instalado, você pode fazê-lo seguindo as instruções
em [getcomposer.org](https://getcomposer.org/download/). No Linux e no Mac OS X,
você executará os seguintes comandos:

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

No Windows, você baixará e executará o [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Por favor consulte a [Documentação do Composer](https://getcomposer.org/doc/) se
você encontrar quaisquer problemas ou se quiser aprender mais sobre o uso do Composer.

Se você já estiver com o Composer instalado, certifique-se de usar uma versão atualizada. 
Você pode atualizar o Composer executando o comando `composer self-update`.

Com o Composer instalado, você pode instalar o Yii executando o seguinte comando
em um diretório acessível pela Web:

    composer global require "fxp/composer-asset-plugin:^1.3.1"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

O primeiro comando instala o [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/)
o que permite gerenciar dependências via bower e npm package por meio do Composer.
Você apenas precisa rodar este comando uma vez. O segundo comando instala o Yii
em um diretório chamado `basic`. Você pode escolher um diretório diferente se quiser.

> Observação: Durante a instalação, o Composer pode pedir suas credenciais de login 
> do Github. Isto é normal, pelo fato do Composer precisar obter a taxa limite 
> (rate-limit) da API para recuperar as informações de dependência de pacotes do
> Github. Para mais detalhes, consulte a [documentação do Composer](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Dica: Se você quiser instalar a última versão de desenvolvimento do Yii, você
> pode usar o seguinte comando, que adiciona uma [opção de estabilidade](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Perceba que a versão de desenvolvimento do Yii não deve ser usada para produção,
> uma vez que ela pode quebrar o seu código que está rodando.


Instalando a partir de um Arquivo Compactado <span id="installing-from-archive-file"></span>
--------------------------------------------

A instalação do Yii a partir de um arquivo compactado envolve três passos:

1. Baixe o arquivo compactado em [yiiframework.com](http://www.yiiframework.com/download/).
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

* Se você só quer instalar o *core* do framework e gostaria de construir uma aplicação
  inteira do zero, você pode seguir as instruções conforme explicadas em
  [Construindo uma Aplicação a Partir do Zero](tutorial-start-from-scratch.md).
* Se você quiser começar com uma aplicação mais sofisticada, mais adequada ao
  ambiente de desenvolvimento de equipes, você pode considerar instalar o
  [Template Avançado de Projetos](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-pt-BR/README.md).


Verificando a Instalação <span id="verifying-installation"></span>
------------------------

Após a instalação, você pode usar o seu navegador para acessar a aplicação do Yii
instalada através da seguinte URL:

```
http://localhost/basic/web/index.php
```

Essa URL presume que você tenha instalado o Yii em um diretório chamado de `basic`,
diretamente no diretório raiz do servidor Web, e que o servidor Web está rodando
em sua máquina local (`localhost`). Você pode precisar ajustá-la ao seu ambiente
de instalação.

![Instalação do Yii com Sucesso](images/start-app-installed.png)

Você deve ver acima a página de "Congratulations!" em seu navegador. Se não vê-la,
por favor verifique se a sua instalação do PHP satisfaz os requisitos do Yii. Você
pode verificar se os requisitos mínimos são atendidos através de um dos seguintes modos:

* Use um navegador para acessar a URL `http://localhost/basic/requirements.php`
* Execute os seguintes comandos:

  ```
  cd basic
  php requirements.php
  ```

Você deve configurar a sua instalação do PHP de modo que ela atenda aos requisitos
mínimos do Yii. Mais importante ainda, você deve ter o PHP 5.4 ou superior. Você
também deve instalar a [Extensão PDO do PHP](http://www.php.net/manual/en/pdo.installation.php) 
e o driver do banco de dados correspondente (tal como `pdo_mysql` para bancos de
dados MySQL), se a sua aplicação precisar de um banco de dados.


Configurando os Servidores Web <span id="configuring-web-servers"></span>
------------------------------

> Informação: Você pode pular essa subseção se só estiver fazendo um test drive do Yii
  sem a intenção de publicá-lo em um servidor de produção.

A aplicação instalada de acordo com as instruções acima deve funcionar imediatamente 
com um [Servidor HTTP Apache](http://httpd.apache.org/) ou um [Servidor HTTP Nginx](http://nginx.org/),
no Windows, Mac OS X ou Linux usando PHP 5.4 ou superior. O Yii 2.0 também é compatível
com o [HHVM](http://hhvm.com/) do Facebook, no entanto, existem alguns casos extremos
que o HHVM se comporta diferente no PHP nativo, então, terá que tomar um cuidado 
extra quando usar o HHVM.

Em um servidor de produção, você pode querer configurar o seu servidor Web de
modo que a aplicação possa ser acessada pela URL `http://www.example.com/index.php`
ao invés de `http://www.example.com/basic/web/index.php`. Tal configuração requer que
você aponte a raiz dos documentos de seu servidor Web para o diretório `basic/web`.
Você também pode querer ocultar o `index.php` da URL, conforme descrito na seção
[Roteamento e Criação de URL](runtime-routing.md). Nesta sub-seção, você
aprenderá como configurar o seu servidor Apache ou Nginx para atingir estes
objetivos.

> Informação: Definindo `basic/web` como a raiz dos documentos, você também previne os
  usuários finais de acessarem o código privado de sua aplicação e os arquivos de
  dados sensíveis que estão armazenados em diretórios irmãos de `basic/web`.
  Negar o acesso a estes outros diretórios é uma melhoria de segurança.

> Informação: Se a sua aplicação rodará em um ambiente de hospedagem compartilhada
  onde você não tem permissão para alterar a configuração do seu servidor Web,
  você ainda pode ajustar a estrutura de sua aplicação para uma melhor segurança.
  Por favor consulte a seção [Ambiente de Hospedagem Compartilhada](tutorial-shared-hosting.md)
  para mais detalhes.


### Configuração do Apache Recomendada <span id="recommended-apache-configuration"></span>

Use a seguinte configuração no arquivo `httpd.conf` do Apache ou em uma
configuração de virtual host. Perceba que você pode deve substituir `path/to/basic/web`
com o caminho real para `basic/web`.

```
# Define a raiz dos documentos como sendo "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # Utilize o mod_rewrite para suporte a URL amigável
    RewriteEngine on
    # Se um diretório ou arquivo existe, usa a requisição diretamente
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Caso contrário, encaminha a requisição para index.php
    RewriteRule . index.php

    # ...outras configurações...
</Directory>
```


### Configuração do Nginx Recomendada <span id="recommended-nginx-configuration"></span>

Você deve ter instalado o PHP como um [FPM SAPI](http://php.net/install.fpm) para
usar o [Nginx](http://wiki.nginx.org/). Use a seguinte configuração do Nginx,
substituindo `path/to/basic/web` com o caminho real para `basic/web` e `mysite.local`
com o nome de host real a servidor.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redireciona tudo que não é um arquivo real para index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # descomente para evitar o processamento de chamadas a arquivos estáticos não existentes pelo Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Ao usar esta configuração, você também deve definir `cgi.fix_pathinfo=0` no arquivo `php.ini`
de modo a evitar muitas chamadas desnecessárias ao comando `stat()` do sistema.

Também perceba que ao rodar um servidor HTTPS, você precisa adicionar `fastcgi_param HTTPS on;`,
de modo que o Yii possa detectar adequadamente se uma conexão é segura.
