Installare Yii
==============

Puoi installare Yii in due modi, usando [Composer](https://getcomposer.org/) o scaricando un archivio.
Il metodo preferito è il primo, perché ti consente di installare [estensioni](structure-extensions.md) o aggiornare il core di Yii 
semplicemente eseguendo un comando.

> Nota: diversamente da Yii 1, le installazioni standard di Yii 2 comportano il download e l'installazione sia del framework che dello scheletro dell'applicazione


Installazione via Composer <span id="installing-via-composer"></span>
--------------------------

Se non hai già installato Composer puoi farlo seguendo le istruzioni al sito 
[getcomposer.org](https://getcomposer.org/download/). Su Linux e Mac OS X puoi installare Composer con questo comando:

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

Su Windows devi scaricare ed eseguire [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Fai riferimento alla [documentazione di Composer](https://getcomposer.org/doc/) in casodi errori o se vuoi apprendere maggiori
informazioni sull'uso di Composer.

Se hai già Composer installato assicurati di avere una versione aggiornata. Puoi aggiornare Composer con il comando
`composer self-update`.

Una volta installato Composer, puoi installare Yii eseguendo questo comando in una directory accessbile via web:

    composer global require "fxp/composer-asset-plugin:^1.4.1"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

Il primo comando installa il [plugin composer asset](https://github.com/fxpio/composer-asset-plugin)
che consente di gestire le dipendenze di bower e npm tramite Composer. Devi eseguire questo comando solo una volta. Il secondo 
installa Yii in una directory di nome `basic`. Puoi scegliere un nome diverso, se preferisci.

> Nota: durante l'installazione potrebbe essere che Composer ti chieda le credenziali di Github, per superato limite di utilizzo
> delle API di Github. Questa situazione è normale perché Composer deve scaricare molte informazioni per tutti i pacchetti da Github.
> Accedendo a Github aumenterà il limite di utilizzo delle API, consentendo a Composer di completare il suo lavoro. Per maggiori 
> dettagli fai riferimento alla 
> [documentazione di Composer](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens).

> Suggerimento: se vuoi installare l'ultima versione di sviluppo di Yii, puoi usare questo comando che aggiunge una 
> [opzione di stabilità](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> Considera che la versione di sviluppo di Yii non dovrebbe essere utilizzata per siti di produzione perché potrebbe rendere instabile
> il tuo codice.


Installazione da un archivio <span id="installing-from-archive-file"></span>
----------------------------

L'installazione da un archivio compresso comporta tre passaggi:

1. Scaricare l'archivio da [yiiframework.com](https://www.yiiframework.com/download/).
2. Scompattare l'archivio in una directory accessible via web.
3. Modificare il file `config/web.php` inserendo una chiave segreta per il parametro di configurazione `cookieValidationKey` 
   (questa operazione viene fatta automaticamente se installi tramite Composer):

   ```php
   // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
   'cookieValidationKey' => 'enter your secret key here',
   ```


Altre modalità di installazione <span id="other-installation-options"></span>
-------------------------------

Le istruzioni sopra elencate mostrano come installare Yii, e creano inoltre un'applicazione web base funzionante.
Questo approccio è un ottimo punto di partenza per progetti minori, o se stai imparando Yii.

Ma ci sono altre opzioni disponibili per l'installazione:

* se vuoi installare solo il core e costruire l'applocazione da zero puoi seguire le istruzioni della sezione
  [costruire un'applicazione da zero](tutorial-start-from-scratch.md).
* se vuoi avviare un'applicazione più sofisticata, che meglio si sposa per uno sviluppo di gruppo, puoi considerare l'insallazione del
  [template di applicazione avanzata](tutorial-advanced-app.md).


Verifica dell'installazione <span id="verifying-installation"></span>
---------------------------

Dopo l'installazione puoi usare il tuo browser per accedere all'applicazione Yii installata con l'URL seguente:

```
http://localhost/basic/web/index.php
```

Questo indirizzo assume che hai installato Yii in una directory di nome `basic`, direttamente nella *root* del tuo webserver,
e che il webserver è in esecuzione sulla tua macchina locale (`localhost`). Potresti doverlo modificare a seconda del tuo ambiente
di installazione.

![Installazione di Yii completata con successo](images/start-app-installed.png)

Dovresti vedere la pagina sopra di congratulazioni. In caso contrario verifica se la tua installazione di PHP soddisfa i requisiti minimi
di Yii. Puoi verificare le richieste in due modi:

* accedere all'indirizzo `http://localhost/basic/requirements.php` tramite browser;
* lanciando questi comandi:

  ```
  cd basic
  php requirements.php
  ```

Devi configurare la tua installazione di PHP in modo che soddisfi le richieste minime di Yii. E' molto importante che tu stia usando 
PHP 5.4 o successivo. Devi inoltre installare le [estensioni PDO di PHP](https://www.php.net/manual/en/pdo.installation.php) e un driver
di database di PDO (come ad esempio `pdo_mysql` per i database MySQL), se la tua applicazione richiede un database.


Configurazione del webserver <span id="configuring-web-servers"></span>
----------------------------

> Informazione: puoi saltare questa parte per ora se stai solo provando Yii e non hai intenzione di installarlo su un server di produzione.

L'applicazione installata secondo le istruzioni sopra dovrebbe funzionare senza problemi su un server 
[Apache](https://httpd.apache.org/) o [Nginx](https://nginx.org/), su Windows, Mac OS X, or Linux equipaggiati con PHP 5.4 o successivo. 
Yii 2.0 è anche compatibile con le librerie [HHVM](https://hhvm.com/) di Facebook, tuttavia ci sono alcuni casi limite dove HHVM si
comporta diversamente dal PHP nativo, quindi devi avere maggiore cura se intendi usare HHVM.

Su un server di produzione vorrai probabilmente che la tua applicazione sia accessibile tramite l'url 
`https://www.example.com/index.php` invece di `https://www.example.com/basic/web/index.php`. Questo risultato richiede che punti la
*document root* del tuo webserver nella directory `basic/web`. Vorrai magari anche nascondere `index.php` dall'URL, come descritto
nella sezione [analizzare e generare URL](runtime-url-handling.md).
In questa parte vedrai configurare il tuo server Apache o Nginx per ottenere questo risultato.

> Informazione: impostando `basic/web` come *document root* impedisci agli utenti finali di accedere al codice e a file/informazioni
riservate memorizzate nelle directory superiori a `basic/web`. Negare l'accesso a queste altre cartelle è sicuramente un vantaggio
per la sicurezza.

> Informazione: se la tua applicazione andrà installata su un servizio di hosting condiviso non avrai il permesso di modificare la
configurazione del webserver, ma dovrai comunque correggere la struttura della tua applicazione per migliorare la sicurezza. Fai 
riferimento alla sezione [ambienti di hosting condiviso](tutorial-shared-hosting.md) per maggiori dettagli.


### Configurazione consigliata di Apache <span id="recommended-apache-configuration"></span>

Usa questa configurazione nel file `httpd.conf` di Apache o nella definizione del tuo *VirtualHost*. Tieni presente che dovrai
modificare `path/to/basic/web` con il percorso reale della tua `basic/web`.

```
# Imposta *DocumentRoot* per essere "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # usa mod_rewrite per gli url SEF
    RewriteEngine on
    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # ...altre impostazioni...
</Directory>
```


### Configurazione consigliata di Nginx <span id="recommended-nginx-configuration"></span>

Devi aver installato PHP con il demone [FPM](https://www.php.net/install.fpm) per usare [Nginx](https://wiki.nginx.org/).
Usa questa configurazione per Nginx, sostituendo `path/to/basic/web` con il percorso reale di `basic/web` e `mysite.test` con
il nome reale del server web.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log main;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

Usando questa configurazione dovresti anche impostare `cgi.fix_pathinfo=0` in `php.ini` per evitare molte chiamate di sistema `stat()` 
inutili.

Inoltre considera che nel caso di server HTTPS dovrai aggiungere `fastcgi_param HTTPS on;` così che Yii possa correttamente rilevare
se la connessione è sicura.
