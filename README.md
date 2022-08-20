Gallery 3.1+ (development version)
==================================

About
-----

Gallery 3 is a web based software product that lets you manage your
photos on your own website.  You must have your own website with PHP
and database support in order to install and use it.  With Gallery you
can easily create and share albums of photos via an intuitive
interface.

Intended Audience
-----------------

This version is intended for anybody who has a website.  We stand
ready to support the product and help you to make the most of it. We
welcome theme and module developers to play with this release and
start turning out slick new designs for our happy users.  If you have
questions or problems, you can get help in the Gallery forums:

  https://groups.google.com/forum/#!forum/gallery-3-users

Security
--------

Did you find a security flaw?  Please submit an issue in github:
https://github.com/bwdutton/gallery3/issues

Supported Configuration
-----------------------

 - Platform: Linux / Unix.
 - Web server: Apache 2.2 and newer.
 - PHP 7.4 and newer (PHP's safe_mode must be disabled and simplexml,
   filter, and json must be installed). All PHP 7.x versions should work but only 7.4 is tested
 - short_open_tag isn't required but additional modules and themes may rely on it.
 - Database: MySQL 5 and newer.

For complete system requirements, please refer to:

  http://codex.galleryproject.org/Gallery3:Requirements

Installing and Upgrading Instructions
-------------------------------------
**NOTE:** When upgrading from PHP 5 to PHP 7 you will need to change the database type from mysql to mysqli in var/database.php:
```php
$config['default'] = array(
  'benchmark'     => false,
  'persistent'    => false,
  'connection'    => array(
    'type'     => 'mysqli',
```

For docker installations:

  https://hub.docker.com/r/bwdutton/gallery3

For comprehensive instructions, The online User Guide is your best resource:

  http://codex.galleryproject.org/Gallery3:User_guide

There are also simple instructions below.  **NOTE:** You can upgrade from
beta 1 and beyond, but not from alpha releases.

### Installation via the web

Point your web browser at `gallery3/installer/` and follow the
instructions.

### Installation from the command line

```sh
php installer/index.php [-h host] [-u user] [-p pass] [-d dbname]
```

 Command line parameters:

```sh
 -h     Database host          (default: localhost)
 -u     Database user          (default: root)
 -p     Database user password (default: )
 -d     Database name          (default: gallery3)
 -x     Table prefix           (default: )
```

### Optional dependencies

Install composer dependencies to make all of the modules work (currently autorotate, phpmailer). In the top level gallery directory where the composer.json file exists run the following:

```sh
composer install
```

Bugs, Questions, Problems?
--------------------------

 - Check out the Gallery 3 FAQ: http://codex.galleryproject.org/Gallery3:FAQ
 - Try the support group: https://groups.google.com/forum/#!forum/gallery-3-users

### Forgot your password? Use the command line:

```sh
php index.php passwordreset <username>
```
