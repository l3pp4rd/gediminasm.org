# http://gediminasm.org blog

This blog is built on Symfony2 using [symfony-standard edition][symfony_standard]. The purpose
of this product being as open source blog, is that people could see how the
[demo][gedmo_demo] of [gedmo extensions][gedmo_extensions] is made and 
could reuse it or have a basic understanding on how to use it in any project.

[gedmo_extensions]: https://github.com/l3pp4rd/DoctrineExtensions "Gedmo behavioral doctrine2 extensions"
[gedmo_demo]: http://gediminasm.org/test "Test extensions on this blog"
[symfony_standard]: http://gediminasm.org/test "Symfony2 standard edition"

## Setup

Run the following commands:

    git clone http://github.com/l3pp4rd/gediminasm.org.git blog
    cd blog
    rm -rf .git
    mkdir app/cache app/logs
    cp app/config/parameters.yml.dist app/config/parameters.yml

Configure your database connection settings in: **app/config/parameters.yml**

Proceed with installation:

    php bin/vendors install
    php app/console assetic:dump -e dev
    php app/console gedmo:blog:update
    php app/console gedmo:demo:reload

Now when you visit **/app_dev.php/demo** you should see extension demo page

## Optional

To override default configuration options use:

    cp app/config/config_dev.yml app/config/config_dev.local.yml 
