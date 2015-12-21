---
date: "2010-08-16T22:26:47+02:00"
Description: "How to compile php5.5 and run it on apache2 or nginx, install mongo extension"
Section: posts
Slug: compile-php
Title: "Compile php on your own"
---

This article contains a PHP build script and a quick tutorial on how
to compile and setup php-5.[2-5].x version into your server. The article
is intended for beginners and has detailed explanations for setting up
dependencies and understanding in general how to modify it to your needs.

Steps:

- Get sources
- Prepare a build script
- Prepare used library dependencies
- Build and install
- Run on Apache2 webserver
- Run on Nginx
- Installation of additional php extensions

<!--more-->

**Before we begin:**

- This post targets linux machines, if you use windows, a setup will be different and it will not be covered in this
tutorial
- PHP used in this example was compiled on Ubuntu 12.04 LTS (Precise Pangolin) linux version
- The same script should compile **PHP-5.3.x, PHP-5.4.x**, **PHP-5.5.x** versions
- Build script does not contain other extensions like LDAP and other. If you need something extra, look into the manual for
configuration options by running **./configure --help** inside php src directory
- Last update date: **2013-07-18**

## Get sources

Go to [PHP downloads](http://www.php.net/downloads.php), choose the version wanted and copy a link from a mirror you
prefere. **php-5.5.0** version will be used in current example.

    mkdir ~/compile && cd ~/compile
    wget http://lt1.php.net/get/php-5.5.0.tar.bz2/from/this/mirror -O - | tar jxf -

Change the url to your prefered source mirror, be sure a link points to an archive package. This should extract the
download stream into **~/compile/php-5.5.0** directory. Based on your chosen version, this may differ.

If you prefer a tar.gz archive, an extraction command is different accordingly:

    wget http://lt1.php.net/get/php-5.5.0.tar.gz/from/this/mirror -O - | tar zxpf -

## Prepare a build script

Since you most probably will want some extensions or apache support, it will be a convenient choice to create
a configuration/build shell script for the php sources to be compiled the way we want. Etc.: you may wish to define
an installation binary path and shared module location. Or later even have some incremental adaptions based on php
version you are compiling.

I personally have 3 or 4 versions to switch during the development. One is for php extension development, another is
for project which requires an older version, next one is the latest in order to grasp new features or use it for my own
projects. This post will intentionally describe how to make such environment, so you can switch between php versions and
compile the latest ones whenever needed.

    vim build_php.sh

You can use a different editor of course.

    #!/bin/sh

    # usage - ./build_php.sh php-5.5.0
    # where php-5.5.0 is a PHP source directory

    # current directory
    DIR="$( cd "$( dirname "$0" )" && pwd )"

    # requires a php source directory as a first argument
    if [ ! -d "$1" ]
    then
        echo "Php source is not a valid directory"
        exit 1
    fi

    # Ubuntu users only, a quirk to locate libpcre
    if [ ! -f "/usr/lib/libpcre.a" ]; then
        if [ -f "/usr/lib/i386-linux-gnu/libpcre.a" ]; then
            sudo ln -s /usr/lib/i386-linux-gnu/libpcre.a /usr/lib/libpcre.a
        elif [ -f "/usr/lib/x86_64-linux-gnu/libpcre.a" ]; then
            sudo ln -s /usr/lib/x86_64-linux-gnu/libpcre.a /usr/lib/libpcre.a
        fi
    fi

    # define full path to php sources
    SRC="$DIR/$1"

    # Here follows paths for installation binaries and general settings
    PREFIX="$HOME/php" # will install binaries in ~/php/bin directory, make sure it is exported in your $PATH for executables
    SBIN_DIR="$HOME/php" # all binaries will go to ~/php/bin
    CONF_DIR="$HOME/php" # will use php.ini located here as ~/php/php.ini
    CONFD_DIR="$HOME/php/conf.d" # will load all extra configuration files from ~/php/conf.d directory
    MAN_DIR="$HOME/php/share/man" # man pages goes here

    EXTENSION_DIR="$HOME/php/share/modules" # all shared modules will be installed in ~/php/share/modules phpize binary will configure it accordingly
    export EXTENSION_DIR
    PEAR_INSTALLDIR="$HOME/php/share/pear" # pear package directory
    export PEAR_INSTALLDIR

    if [ ! -d "$CONFD_DIR" ]; then
        mkdir -p $CONFD_DIR
    fi

    # here follows a main configuration script
    PHP_CONF="--config-cache \
        --prefix=$PREFIX \
        --sbindir=$SBIN_DIR \
        --sysconfdir=$CONF_DIR \
        --localstatedir=/var \
        --with-layout=GNU \
        --with-config-file-path=$CONF_DIR \
        --with-config-file-scan-dir=$CONFD_DIR \
        --disable-rpath \
        --mandir=$MAN_DIR \
    "

    # enter source directory
    cd $SRC

    # build configure, not included in git versions
    if [ ! -f "$SRC/configure" ]; then
        ./buildconf --force
    fi

    # Additionally you can add these, if they are needed:
    #   --enable-ftp
    #   --enable-exif
    #   --enable-calendar
    #   --with-snmp=/usr
    #   --with-pspell
    #   --with-tidy=/usr
    #   --with-xmlrpc
    #   --with-xsl=/usr
    # and any other, run "./configure --help" inside php sources

    # define extension configuration
    EXT_CONF="--enable-mbstring \
        --enable-mbregex \
        --enable-phar \
        --enable-posix \
        --enable-soap \
        --enable-sockets \
        --enable-sysvmsg \
        --enable-sysvsem \
        --enable-sysvshm \
        --enable-zip \
        --enable-inline-optimization \
        --enable-intl \
        --with-icu-dir=/usr \
        --with-curl=/usr/bin \
        --with-gd \
        --with-jpeg-dir=/usr \
        --with-png-dir=shared,/usr \
        --with-xpm-dir=/usr \
        --with-freetype-dir=/usr \
        --with-bz2=/usr \
        --with-gettext \
        --with-iconv-dir=/usr \
        --with-mcrypt=/usr \
        --with-mhash \
        --with-zlib-dir=/usr \
        --with-regex=php \
        --with-pcre-regex=/usr \
        --with-openssl \
        --with-openssl-dir=/usr/bin \
        --with-mysql-sock=/var/run/mysqld/mysqld.sock \
        --with-mysqli=mysqlnd \
        --with-sqlite3=/usr \
        --with-pdo-mysql=mysqlnd \
        --with-pdo-sqlite=/usr
    "

    # adapt fpm user and group if different wanted
    PHP_FPM_CONF="--enable-fpm \
        --with-fpm-user=www-data \
        --with-fpm-group=www-data
    "

    # CLI, php-fpm and apache2 module
    ./configure $PHP_CONF \
        --disable-cgi \
        --with-readline \
        --enable-pcntl \
        --enable-cli \
        --with-apxs2=/usr/bin/apxs2 \
        --with-pear \
        $PHP_FPM_CONF \
        $EXT_CONF

    # CGI and FastCGI
    #./configure $PHP_CONF --disable-cli --enable-cgi $EXT_CONF

    # build sources
    make

**Note:** if you compile php from [git sources](https://github.com/php/php-src/tree/PHP-5.5), you may need
[other dependencies](http://www.php.net/manual/en/install.unix.php)

Ok, so which extensions we will have bundled together with php and what the config general stuff we need to know about:

- **--enable-mbstring, --enable-mbregex** - multibyte encoding support for string functions and regexes
- **--enable-intl** - internationalization functions for php
- **--enable-fpm** - builds php-fpm
- **--with-apxs2** - will build apache module for use with our php
- **--with-iconv-dir** - character set conversion toolset, also provides transliteration. (libiconv should be installed in base system)
- **--with-icu-dir** - libicu, general utf8, unicode support library
- **--with-curl** - curl is a most commonly used extension for php remote request operations
- **--with-gd, --with-jpeg-dir, --with-png-dir, --with-xpm-dir** - are general image manipulation extensions
- **--with-freetype-dir** - font manipulation library
- **--with-gettext** - GNU translation management library (should be installed in base system)
- **--with-openssl** - will enable secured protocol TLS, SSL.. support
- **--with-mcrypt** - library [libmcrypt](http://sourceforge.net/projects/mcrypt/). Encryption algorithms
- **--with-mhash** - library [libmhash](http://sourceforge.net/projects/mhash/). Hashing algorithms
- **--with-pcre-regex** - libpcre, regular expression library
- **--with-mysqli** - MySQL extension. **mysqlnd** is a native driver
- **--with-sqlite3** - Sqlite3 extension.

**NOTE:** If you need CGI binaries, uncomment those instead.

## Prepare used library dependencies

Install the general build tools, you will need **GNU gcc compiler**, **libc** and **make**.
**Ubuntu users:** install dependent libraries:

    sudo apt-get install abuild-essential apache2-threaded-dev apache2-mpm-prefork apache2-prefork-dev libcurl4-openssl-dev
    sudo apt-get install libsqlite3-dev sqlite3 mysql-server libmysqlclient-dev libreadline-dev libzip-dev libxslt1-dev
    sudo apt-get install libicu-dev libmcrypt-dev libmhash-dev libpcre3-dev libjpeg-dev libpng12-dev libfreetype6-dev libbz2-dev libxpm-dev

For other distribution users, install the general build tools, you will need **GNU gcc compiler**, **libc** and **make**.
Look for libraries to install based on their names. When you compile PHP in case if header
files will not be found, you will receive an error, googling it you should manage to find required libraries for
installation on your specific distribution. Comment the last part of build script, which runs **make**, that should help
to debug missing library headers faster. If you are willing to share, you can contribute by updating this
[blog post](https://github.com/l3pp4rd/gediminasm.org/blob/master/resources/posts/compile-php/content.md) with
a specific command line to install dependencies for other types of linux distributions.

## Build sources and install

    chmod +x build_php.sh
    ./build_php.sh php-5.5.0

This build will take some time, if you think you managed dependent libraries, have a cup of coffee.
Maybe two if you had problems with configuration.

Now if the job is done, and make did not throw a fatal error, congrats! you got it! All what is left is to setup
your apache or nginx to run with our php build.

    cd php-5.5.0 && sudo make install

Make sure your php binaries are in your $PATH

    export PATH=$PATH:$HOME/php/bin

A better idea is to have it in your shell rc file:

    echo "export PATH=\$PATH:\$HOME/php/bin" >> ~/.bashrc

Now you should be able to see a php version:

    php --version

On ubuntu, after running **make install** it should have placed apache module in the right place. Also most probably has
enabled it.

You should setup the default configuration file **~/php/php.ini**:

    ;you may copy it from ~/compile/php-5.5.0/php.ini-production and adapt
    ;or trust php defaults and only override what you need:

    error_reporting = E_ALL
    display_errors = On
    display_startup_errors = On

    memory_limit = 256M
    post_max_size = 32M
    upload_max_filesize = 32M

    date.timezone = UTC

    ;change it to suite your installation
    include_path = ".:/home/gedi/php/share/pear"

## Run on Apatche2

Create a php file **/var/www/php-project/index.php**:

    sudo mkdir /var/www/php-project
    sudo nano /var/www/php-project/index.php

With contents:

    <?php

    echo "Voila, running PHP script on " . phpversion();

Ensure that it is accessible for apache:

    sudo chown -R www-data:www-data /var/www/php-project
    sudo chmod -R 775 /var/www/php-project

Now lets create a virtual host:

    sudo nano /etc/apache2/sites-available/php-project

configure it as:

    <VirtualHost *:80>
        DocumentRoot "/var/www/php-project"
        ServerName php-project.local

        ErrorLog ${APACHE_LOG_DIR}/php-project-error.log
        CustomLog ${APACHE_LOG_DIR}/php-project-access.log combined
    </VirtualHost>

Now enable it and add host:

    sudo a2ensite php-project
    sudo service apache2 restart

Append a line `127.0.0.1    php-project.local` to **/etc/hosts**

Now open your browser at **http://php-project.local** or use **wget**
You should see a result as "Voila, running PHP script on 5.5.0"

## Run on Nginx

Install Nginx:

    sudo apt-get install nginx

We will need rc init script, to make it more simple, lets use current ubuntu php-fpm package and make some replacements
so that the paths would be correct, run these commands:

    cd ~/php && aptitude download php5-fpm
    dpkg-deb -x *.deb src
    mv src/etc/init.d/php5-fpm php-fpm.init
    mv src/etc/php5/fpm/php-fpm.conf php-fpm.conf
    mv src/etc/php5/fpm/pool.d fpm.conf.d
    sed s@/usr/sbin/\$NAME@${HOME}/php/\$NAME@ php-fpm.init > temp; mv temp php-fpm.init
    sed s@php5-fpm@php-fpm@ php-fpm.init > temp; mv temp php-fpm.init
    sed s@/etc/php5/fpm/php-fpm.conf@${HOME}/php/php-fpm.conf@ php-fpm.init > temp; mv temp php-fpm.init
    sed s@/etc/php5/fpm/pool.d@${HOME}/php/fpm.conf.d@ php-fpm.conf > temp; mv temp php-fpm.conf

These commands have modified paths in order to work with our $HOME/php installation location. We could have used
standard location as /etc/php, you can update script if you like it afterwards, all it would take is to recompile.

Now lets install our **init.d** script for php-fpm:

    sudo cp ~/php/php-fpm.init /etc/init.d/php-fpm
    sudo chmod +x /etc/init.d/php-fpm

Now start the **php-fpm** service:

    sudo service php-fpm start

you may also wish to add this service to automatically start on each boot;

    sudo update-rc.d php-fpm defaults

We should have it running, check with:

    ps aux | grep php-fpm

There should be around four threads running. For other distributions, you should find ways how to deamonize the php-fpm.
Now lets proceed to Nginx virtual host, which will run our previously created **/var/www/php-project**. If you have skipped
the apache2 part, add the php-project.local to hosts and create an index.php file in /var/www.

Next, create a virtual host config for Nginx server:

    sudo nano /etc/nginx/sites-available/php-project

Put the contents:

    server {
        listen 80;
        server_name php-project.local;
        root /var/www/php-project;

        error_log /var/log/nginx/php-project.error.log;
        access_log /var/log/nginx/php-project.access.log;

        # first check if its a static file, otherwise run through @handler
        location / {
            index index.php;
            try_files $uri @handler;
            #expires 24h;
        }

        # if it was not a static file, execute through index.php
        location @handler {
            rewrite ^(.*)$ /index.php last; # force index.php if it was not a file
        }

        # pass the PHP scripts to fpm socket, NOTE: php-fpm required, otherwise use fastcgi
        location ~ \.php$ {
            fastcgi_pass                    127.0.0.1:9000;
            fastcgi_index                   index.php;
            include                         fastcgi_params;
            fastcgi_param SCRIPT_FILENAME   $document_root$fastcgi_script_name;
            fastcgi_param HTTPS             off;
        }
    }

Now enable the site and run nginx

    sudo ln -s /etc/nginx/sites-available/php-project /etc/nginx/sites-enabled/php-project
    sudo service apache2 stop
    sudo service nginx restart

And here you have it. Open the browser at **http://php-project.local**

## Installation of additional php extensions

Now lets say at some point we need a mongodb in our project. Lets install php mongo db extension.
If you do not have **git** yet, install it.

    sudo apt-get install git mongodb autoconf
    cd ~/compile
    git clone git://github.com/mongodb/mongo-php-driver.git mongo
    cd mongo
    phpize
    ./configure
    make install

Autoconf is needed for **phpize**. You may checkout mongo at a specific stable release version, that may be safer. In
result, we should have a **mongo.so** available at our module directory: **~/php/share/modules**. Since we have compiled
php5.5 there should be **opcache.so** as well. This is a bundled php version of memory based cache. You may prefer to
use it instead of **apc**, or **memcached**. Now lets hook the **mongo.so** module:

    echo "extension=mongo.so" > ~/php/conf.d/mongo.ini

You should have it available now.

    echo "<?php echo 'hi, mongo version is: '.phpversion('mongo');" | php

You should have a similar output like "hi, mongo version is: 1.5.0dev". You should understand now how to hook other
extensions. Compiled php from sources gives you some advantages:

- it will be optimized for your CPU
- you may drop some built in crap, you never use
- you can always stick to most recent versions, especially in **debian** world
- you can have any number of php versions compiled, waiting to be installed on demand
- it also expands your engineering skills

