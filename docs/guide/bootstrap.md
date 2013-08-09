Bootstrap with Yii
==================

Yii provides a few ready-to-use application templates. Based on your needs, you may
choose one of them to bootstrap your project.

In the following, we describe how to get started with the "Yii 2 Basic Application Template".


### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may download it from
[http://getcomposer.org/](http://getcomposer.org/) or run the following command on Linux/Unix/MacOS:

~~~
curl -s http://getcomposer.org/installer | php
~~~

You can then install the Bootstrap Application using the following command:

~~~
php composer.phar create-project --stability=dev yiisoft/yii2-app-basic yii-basic
~~~

Now you should be able to access the Bootstrap Application using the URL `http://localhost/yii-basic/web/`,
assuming `yii-basic` is directly under the document root of your Web server.


As you can see, the application has four pages: the homepage, the about page,
the contact page and the login page. The contact page displays a contact
form that users can fill in to submit their inquiries to the webmaster,
and the login page allows users to be authenticated before accessing privileged contents.


The following diagram shows the directory structure of this application.

~~~
yii-basic/
   yii                    yii command line script for Unix/Linux
   yii.bat                yii command line script for Windows
   requirements.php       the requirement checker script
   commands/              containing customized yii console commands
   config/                containing configuration files
      console.php         the console application configuration
      main.php            the Web application configuration
   controllers/           containing controller class files
      SiteController.php  the default controller class
   vendor/                containing third-party extensions and libraries
   models/                containing model class files
      User.php            the User model
      LoginForm.php       the form model for 'login' action
      ContactForm.php     the form model for 'contact' action
   runtime/               containing temporarily generated files
   views/                 containing controller view and layout files
      layouts/            containing layout view files
         main.php         the base layout shared by all pages
      site/               containing view files for the 'site' controller
         about.php        the view for the 'about' action
         contact.php      the view for the 'contact' action
         index.php        the view for the 'index' action
         login.php        the view for the 'login' action
   web/                   containing Web-accessible resources
     index.php            Web application entry script file
     assets/              containing published resource files
     css/                 containing CSS files
~~~


TBD
