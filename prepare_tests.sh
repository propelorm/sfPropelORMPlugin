#!/usr/bin/env bash

if [ -z $SYMFONY_VERSION ] ; then
    SYMFONY_VERSION="symfony-1.4.17"
fi

PROJECT_NAME=mockproject

if [ -d "$PROJECT_NAME" ] ; then
    rm -rf "$PROJECT_NAME"
fi

git submodule update --init

mkdir "$PROJECT_NAME"
cd "$PROJECT_NAME"

if [ ! -f "$SYMFONY_VERSION.tgz" ] ; then
    wget "http://www.symfony-project.org/get/$SYMFONY_VERSION.tgz"
fi

tar xvf "$SYMFONY_VERSION.tgz"
rm "$SYMFONY_VERSION.tgz"

mkdir -p lib/vendor/
mv "$SYMFONY_VERSION" lib/vendor/symfony

php ./lib/vendor/symfony/data/bin/symfony generate:project --installer=../test/bin/installer.php --orm=Propel "$PROJECT_NAME"
