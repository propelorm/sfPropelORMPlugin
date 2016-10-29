#!/usr/bin/env bash

if [ -z $SYMFONY_REPOSITORY ] ; then
    SYMFONY_REPOSITORY="https://github.com/rozwell/symfony1.git"
fi

if [ -z $SYMFONY_BRANCH ] ; then
    SYMFONY_BRANCH="propel"
fi

PROJECT_NAME=mockproject

if [ -d "$PROJECT_NAME" ] ; then
    rm -rf "$PROJECT_NAME"
fi

git submodule update --init --recursive

mkdir "$PROJECT_NAME"
cd "$PROJECT_NAME"

mkdir -p lib/vendor/
git clone --branch=$SYMFONY_BRANCH $SYMFONY_REPOSITORY lib/vendor/symfony
cd lib/vendor/symfony
git submodule update --init --recursive
cd ../../../

php ./lib/vendor/symfony/data/bin/symfony generate:project --installer=../test/bin/installer.php --orm=none "$PROJECT_NAME"
php ./lib/vendor/symfony/data/bin/symfony propel:install
