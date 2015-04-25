Gallery 3.0.10 (Alamo)
===========================

[![Build Status](https://travis-ci.org/gallery/gallery3.png?branch=master)](https://travis-ci.org/gallery/gallery3)

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

  http://galleryproject.org/forum/96

Security
--------

We've contracted a professional security audit, received their results
and resolved all the issues they found.

Did you find a security flaw?  Please email security@galleryproject.org
with the details and we'll fix it ASAP!

Supported Configuration
-----------------------

 - Platform: Linux / Unix.
 - Web server: Apache 2.2 and newer.
 - PHP 5.2.3 and newer (PHP's safe_mode must be disabled and simplexml,
   filter, and json must be installed).
 - Database: MySQL 5 and newer.

For complete system requirements, please refer to:

  http://codex.galleryproject.org/Gallery3:Requirements

Installing and Upgrading Instructions
-------------------------------------

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

Bugs?
-----

Go to http://apps.sourceforge.net/trac/gallery/ click the "login" link
and log in with your SourceForge username and password, then click the
"new ticket" button.

Questions, Problems
-------------------

 - Check out the Gallery 3 FAQ: http://codex.galleryproject.org/Gallery3:FAQ
 - Post to the Gallery 3 forums: http://galleryproject.org/forum/96
 - Email gallery-devel@lists.sourceforge.net
