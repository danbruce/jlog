#!/bin/bash
apt-add-repository -y ppa:ondrej/php5
apt-add-repository -y ppa:ondrej/mysql
apt-get -y update
apt-get -y dist-upgrade
apt-get -y autoremove
apt-get install php5-cli php5-mysql php5-curl php5-xdebug php-pear mysql-server mysql-client
pear config-set auto_discover 1
pear install pear.phpunit.de/PHPUnit
pear install phpunit/DbUnit
mysql -u root -ppassword -e "CREATE DATABASE jlog_test"
mysql -u root -ppassword jlog_test < 
sed "s/\${TABLE_PREFIX}//g" /vagrant/Storage/mysql/structure.sql > temp.sql
mysql -u root -ppassword jlog_test < temp.sql
rm temp.sql