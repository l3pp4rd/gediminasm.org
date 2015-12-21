# My blog

Powered by [hugo](https://github.com/spf13/hugo/releases) static site generator.

## Install

First, install [hugo](https://github.com/spf13/hugo/releases). And put it in your **$PATH**.

Install the theme and check necessary dependencies.

    make deps

Update config:

    cp config.toml.dist config.toml

Serve it:

    hugo server --theme=hyde-x --buildDrafts --watch

## Release

    make release
    ansible-playbook -i ansible/blog ansible/playbook.yml
