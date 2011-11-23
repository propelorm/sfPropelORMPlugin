#!/bin/bash
SYMFONY="symfony-1.4.15"

mkdir mockproject
cd mockproject

wget "http://www.symfony-project.org/get/$SYMFONY.tgz"
tar xvf "$SYMFONY.tgz"
mkdir -p lib/vendor/
mv "$SYMFONY" lib/vendor/symfony


php ./lib/vendor/symfony/data/bin/symfony generate:project --installer=../test/bin/installer.php --orm=Propel mockproject

rm -r "$SYMFONY.tgz"
