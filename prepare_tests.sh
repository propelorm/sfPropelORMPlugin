#!/bin/bash

if [ -z $SYMFONY_VERSION ] ; then
    SYMFONY_VERSION="symfony-1.4.16"
fi

mkdir mockproject
cd mockproject

wget "http://www.symfony-project.org/get/$SYMFONY_VERSION.tgz"
tar xvf "$SYMFONY_VERSION.tgz"
mkdir -p lib/vendor/
mv "$SYMFONY_VERSION" lib/vendor/symfony


php ./lib/vendor/symfony/data/bin/symfony generate:project --installer=../test/bin/installer.php --orm=Propel mockproject

rm -r "$SYMFONY_VERSION.tgz"
