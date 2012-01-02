# Build php-5.3.0 - php-5.3.4-dev on Ubuntu server

This article contains a PHP build script and a quick tutorial on how
to compile and setup php-5.3.x version into your server

Steps:

- Downloading sources
- Making a configure script
- Building and deploying

[blog_reference]: http://gediminasm.org/article/build-php-5-3-0-php-5-3-4-dev-on-ubuntu-server "How to compile php 5.3.x on Ubuntu linux"

**Notice list:**

- The PHP was compiled on Ubuntu server 9.10 (Karmic Koala) linux version
- Tested compilation on **PHP-5.3.1, PHP-5.3.2, PHP-5.3.4-dev** versions
- Build script does not contain additional PDO drivers like PostgreSql, MssSql. If you need it search an information on how to include them
- It is possible that I missed to include some library in case if I had it before
- Last update date: **2010-08-16**

First of all we need to download the sources of PHP:

- Go to [PHP releases][1] take the sources of php 5.3.x version, better stable and make sure it is thread safe, uless you know what you are doing..
- Here I use the root user, because most operations will require privileges
- Download and then extract (tar xzvf php-5.3.x.tar.gz) into /root/ directory or somethere else..
- No we will need some additional libraries and the development tools before we begin

Run these commands to install tools:

```
apt-get install checkinstall

apt-get install apache2 apache2-mpm-prefork apache2-prefork-dev apache2-utils apache2.2-common

apt-get install mysql-client mysql-client-5.1 mysql-common mysql-server mysql-server-5.1 mysql-server-core-5.1

apt-get install apache2-threaded-dev

apt-get install libtidy-dev curl libcurl4-openssl-dev libcurl3 libcurl3-gnutls zlib1g zlib1g-dev libxslt1-dev libzip-dev libzip1 libxml2 libsnmp-base libsnmp15 libxml2-dev libsnmp-dev libjpeg62 libjpeg62-dev libpng12-0 libpng12-dev zlib1g zlib1g-dev libfreetype6 libfreetype6-dev libbz2-dev libxaw7-dev libmcrypt-dev libmcrypt4
```

Next, we will need to make a build script, run **cd php-5.3.x && vi build_php** and insert the configuration script:

```
./configure \
--with-apxs2=/usr/bin/apxs2 \
--with-mysql=/usr \
--with-mysqli=/usr/bin/mysql_config \
--with-tidy=/usr \
--with-curl=/usr/bin \
--with-openssl \
--with-openssl-dir=/usr \
--with-kerberos=/usr \
--with-zlib-dir=/usr \
--with-xpm-dir=/usr \
--with-pdo-mysql=/usr \
--with-xsl=/usr \
--with-xmlrpc \
--with-iconv-dir=/usr \
--with-snmp=/usr \
--enable-exif \
--enable-cli \
--enable-calendar \
--with-bz2=/usr \
--with-mcrypt=/usr \
--with-mhash \
--with-gd \
--with-jpeg-dir=/usr \
--with-png-dir=/usr \
--with-zlib-dir=/usr \
--with-freetype-dir=/usr \
--with-imap \
--with-imap-ssl \
--enable-zend-multibyte \
--enable-mbstring \
--enable-mbregex \
--enable-soap \
--enable-sockets \
--enable-sysvmsg \
--enable-sysvsem \
--enable-sysvshm \
--enable-zip \
--enable-ftp \
--with-pear \
--disable-debug
```

The build script contains usual tools, like ssl, imap, mbstring, soap..

Next make this script executable: **chmod +x build_php**

Now run the **./build_php** to configure our PHP

It can take few minutes, if there will be no errors then it went quite easy and we are allmost done, if there were errors.. then its even more interesting :)

Ok if you succeeded with configuration lets make the php lib, run:

```
make
```

That can take long, better take a cup of coffee, maybe two if you had problems with configuration

If you have not finished your coffee and want to wait much longer, then you can run tests:

```
make test
```

Now if the job is done, and make did not throw a fatal error, congrats! you got it!

Now few easy steps to use **Your New PHP**:

Create the new apache module: **vi /etc/apache2/mods-available/php53x.load** and place code to load your php library

```
LoadModule php5_module /root/src/php-5.3.x/libs/libphp5.so
```

Now we will need to configure this module **nano /etc/apache2/mods-available/php53x.conf** and place the code:

```
<IfModule mod_php5.c>
  AddType application/x-httpd-php .php .phtml .php3
  AddType application/x-httpd-php-source .phps
  PHPIniDir /root/src/php-5.3.x/libs
</IfModule>
```

**Note:** that we configured to load php.ini from specific directory

And at last and least, disable old php module and enable new. Restart apache after that:

```
a2dismod php5
a2enmod php53x
/etc/init.d/apache2 restart
```

Thats it! You should run the phpInfo(); and enjoy the result :)

 [1]: http://php.net/releases/index.php
