Shared Hosting Environment
==========================

Shared hosting environments are often quite limited about configuration and directory structure. Still in most cases
you can run Yii 2.0 on these.

Deploying basic application
---------------------------

Since there's typically only one webroot it is recommended to use basic application template. Refer to
[Installing Yii chapter](start-installation.md) and install application template locally.

### Add extras for webserver <span id="add-extras-for-webserver"></span>

If webserver used is Apache you'll need to add `.htaccess` file with the following content to `web`
(where `index.php` is):

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

In case of nginx you should not need any extra config files.

### Renaming webroot <span id="renaming-webroot"></span>

If after connecting to your shared hosting via FTP or by other means you're seeing something like the following, you're
most probably lucky.
 
```
config
logs
www
```

In the above `www` is webserver directory root (i.e. webroot). It could be named differently. Common names are: `www`,
`htdocs`, `public_html`. Since we have webroot in our basic application template named `web` we need to rename it to
whatever hosting webroot is before uploading.

### FTP root directory is writeable

If you can write to the root level directory i.e. where `config`, `logs` and `www` are, just upload `assets`, `commands`
etc. as is.

### Check requirements

In order to run Yii hosting should meet its requirements. The very minimum requirement is PHP 5.4. In order to check
the rest copy `requirements.php` from root directory into webroot directory and run it via browser using
`http://example.com/requirements.php` URL. Don't forget to delete the file afterwards.


Deploying advanced application
------------------------------

Deploying advanced application to shared hosting is a bit trickier than doing it with basic application because it has
two webroots which shared hosting typically donesn't have. Because of that structure could be adjusted a bit.

### Move entry scripts into single webroot

First of all we need a webroot directory. Name it the way it matches your hosting webroot as described in
[Renaming webroot](#renaming-webroot) above. Then create the following structure:

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

`www` will be our frontend directory so move contents of `frontend/web` into it. Do the same with `backend/web` moving
its contents into `www/admin`. In each case you need to adjust paths in `index.php` and `index-test.php`.

### Separate sessions and cookies

Originally backend and frontend are intended to run at different domains. When we're moving it all to the same domain it
is starting to share the same cookies creating a clash. It order to fix it adjust backend application config
`backend/config/main.php` like the following:

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
