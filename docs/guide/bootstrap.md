Bootstrap with Yii
==================

A ready-to-use Web application is distributed together with Yii. You may find
its source code under the `app` folder after you expand the Yii release file.
If you have installed Yii under a Web-accessible folder, you should be able to
access this application through the following URL:

~~~
http://localhost/yii/apps/bootstrap/index.php
~~~


As you can see, the application has four pages: the homepage, the about page,
the contact page and the login page. The contact page displays a contact
form that users can fill in to submit their inquiries to the webmaster,
and the login page allows users to be authenticated before accessing privileged contents.


The following diagram shows the directory structure of this application.

~~~
app/
   index.php                 Web application entry script file
   index-test.php            entry script file for the functional tests
   assets/                   containing published resource files
   css/                      containing CSS files
   img/                      containing image files
   themes/                   containing application themes
   protected/                containing protected application files
      yiic                   yiic command line script for Unix/Linux
      yiic.bat               yiic command line script for Windows
      yiic.php               yiic command line PHP script
      commands/              containing customized 'yiic' commands
      components/            containing reusable user components
      config/                containing configuration files
         console.php         the console application configuration
         main.php            the Web application configuration
      controllers/           containing controller class files
         SiteController.php  the default controller class
      data/                  containing the sample database
         schema.mysql.sql    the DB schema for the sample MySQL database
         schema.sqlite.sql   the DB schema for the sample SQLite database
         bootstrap.db        the sample SQLite database file
      vendor/                containing third-party extensions and libraries
      messages/              containing translated messages
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
~~~


TBD