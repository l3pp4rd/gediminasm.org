# My blog

[My blog](http://gediminasm.org) is powered by [hugo](https://github.com/spf13/hugo/releases) static site generator.

## Install

First, install [hugo](https://github.com/spf13/hugo/releases). And put it in your **$PATH**.

Install the theme and check necessary dependencies.

    make deps

Update config:

    cp config.toml.dist config.toml

Serve it on **:1313** port:

    make

## Release

    make release
    ansible-playbook -i ansible/blog ansible/playbook.yml
