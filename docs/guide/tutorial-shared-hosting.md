Shared Hosting Environment
==========================

Shared hosting environments are often quite limited about configuration and directory structure. Still in most cases you can run Yii 2.0 on a shared hosting environment with a few adjustements.

Deploying a basic application
---------------------------

Since in a shared hosting environment there's typically only one webroot, use the basic project template if you can. Refer to the [Installing Yii chapter](start-installation.md) and install the basic project template locally. After you have the application working locally, we'll make some adjustments so it can be hosted on your shared hosting server.

### Renaming webroot <span id="renaming-webroot"></span>

Connect to your shared host using FTP or by other means. You will probably see something like the following.
 
```
config
logs
www
```

In the above, `www` is your webserver webroot directory. It could be named differently. Common names are: `www`, `htdocs`, and `public_html`.

The webroot in our basic project template is named `web`. Before uploading the application to your webserver rename your local webroot to match your server, i.e., from `web` to `www`, `public_html` or whatever the name of your hosting webroot.

### FTP root directory is writeable

If you can write to the root level directory i.e. where `config`, `logs` and `www` are, then upload `assets`, `commands` etc. as is to the root level directory.

### Add extras for webserver <span id="add-extras-for-webserver"></span>

If your webserver is Apache you'll need to add an `.htaccess` file with the following content to `web` (or `public_html` or whatever) (where the `index.php` file is located):

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

### Check requirements

In order to run Yii, your webserver must meet its requirements. The very minimum requirement is PHP 5.4. In order to check the requirements copy `requirements.php` from your root directory into the webroot directory and run it via browser using
`http://example.com/requirements.php` URL. Don't forget to delete the file afterwards.

Deploying an advanced application
---------------------------------

Deploying an advanced application to shared hosting is a bit trickier than a basic application because it has two webroots, which shared hosting webservers don't support. We will need to adjust the directory structure.

### Move entry scripts into single webroot

First of all we need a webroot directory. Create a new directory and name it to match your hosting webroot name as described in [Renaming webroot](#renaming-webroot) above, e.g., `www` or `public_html` or the like. Then create the following structure where `www` is the hosting webroot directory you just created:

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

`www` will be our frontend directory so move the contents of `frontend/web` into it. Move the contents of `backend/web` into `www/admin`. In each case you will need to adjust the paths in `index.php` and `index-test.php`.

### Separate sessions and cookies

Originally the backend and frontend are intended to run at different domains. When we’re moving it all to the same domain the frontend and backend will be sharing the same cookies, creating a clash. It order to fix it, adjust backend application config
`backend/config/main.php` as follows:

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
