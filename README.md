# SnowTricksSymfony5
Collaborative snowboard site - Symfony 5.1 - student project n°6 - OpenClassrooms  diploma course "Développeur d'applications - PHP/Symfony"

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/7ee9cfc490a74bc78aa4a9e35937cec2)](https://www.codacy.com/manual/CarolineDirat/SnowTricksSymfony5?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=CarolineDirat/SnowTricksSymfony5&amp;utm_campaign=Badge_Grade)

## Requirements

### SnowTricks installation needs in command line
  - **composer**:  [getcomposer.org/](https://getcomposer.org/)
  - **Git**: [git-scm.com/](https://git-scm.com/) to clone the project

### SnowTricks use

  - **PHP** version: 7.4.* (server and terminal),[www.php.net/](https://www.php.net/).

  - **MySQL** database that you can manage with a **database tool** (as [phpmyadmin](https://www.phpmyadmin.net/) or [DBeaver](https://dbeaver.io/) ...).

  - **URL rewriting**, so on **Apache**, you must active **rewrite_module** module in Apache **http.conf** file.

  - **[GD](https://www.php.net/manual/en/book.image.php)** library of PHP (to resize images)

  => in **php.ini** : upload_max_filesize = 10M
  - **[ramsey\uuid](https://github.com/ramsey/uuid)** for uuid management
  This package require PHP +7.2 and PHP extensions:
    - ext-json
    - ext-ctype
    - ext-gmp
    - ext-bcmath

  - **[symfony requirements](https://symfony.com/doc/current/setup.html#technical-requirements)**:
  For example in **php.ini**:
    - memory_limit = 128M
    - realpath_cache_size = 5M
    - activation of [opcache] PHP extension:
      - opcache.enable=On
      - opcache.enable_cli=On

## Installation on a local server

The following instructions guide you to install the project locally, on HTTP server Apache (for example : Wampserver). [See Symfony documentation](https://symfony.com/doc/current/setup.html#setting-up-an-existing-symfony-project) 

1. **Clone the project** from Github 
   At the root of your local serveur, with command line
   > `git clone  https://github.com/CarolineDirat/SnowTricksSymfony5.git` [**directory**]

**directory** is the name of a new directory to clone into. 
If you don't use it, the project is cloned in *SnowTricksSymfony5* directory. And we obtain for example: C:/wamp/www/SnowTricksSymfony5

--------
2. Create your **virtualhost** on Wampserver.

Be careful, virtualhost must point to the public directory
**_For example:_** C:/wamp/www/SnowTricksSymfony5/public

--------
3. At the root of the project directory, use composer to **load vendor** and **var** directories with:
   > `composer install`
   
--------
4. **[Overriding Environment Values via .env.local](https://symfony.com/doc/current/configuration.html#overriding-environment-values-via-env-local)**

Create a **.env.dev.local** file, at the root of the project directory, to define (see .env file) :
- DATABASE_URL value ([see Symfony documentation](https://symfony.com/doc/current/doctrine.html#configuring-the-database))
- MAILER_DSN value (if you need)

_For example:_
  - DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name_dev?serverVersion=5.7" (see .env file)
  - If you use **[MailHog](https://github.com/mailhog/MailHog)**: MAILER_DSN=smtp://loaclhost:1025

--------
5. Now that your connection parameters are setup, Doctrine can **create** the db_name_dev **database** for you:
   > php bin/console doctrine:database:create

That's create a database with the "db_name_dev" which name you defined in DATABASE_URL value.

Then, you can [create the database **tables**/schema](https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-schema):
   
   > php bin/console doctrine:migrations:migrate

**Answer _yes_ to the question**: "_WARNING! You are about to execute a database migration that could result in schema changes and data loss. Are you sure you wish to continue?" (yes/no) [yes]:_"

--------
6. **Load initial data** (from src/DataFixtures/AppFixtures.php) with line command:
   > php bin/console doctrine:fixtures:load

**Answer _yes_ to the question**: _Careful, database "db_name_dev" will be purged. Do you want to continue? (yes/no) [no]:"_

There is **3 users** (and **10 tricks** with some comments, pictures and videos):
  - user1 | user1@domain.com | password | with ROLE_VERIFY
  - user2 | user2@domain.com | password | with ROLE_VERIFY
  - user3 | user3@domain.com | password | **without** ROLE_VERIFY (So he can't edit or delete tricks, photos and videos, or comment a trick)

--------
7. _**That's all folks!**_ 








