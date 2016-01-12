#!/usr/bin/env bash
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password empty"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password empty"
sudo aptitude install -q -y -f  mysql-server mysql-client php5-mysql

mysql -uroot -pempty -e 'CREATE DATABASE IF NOT EXISTS `elastic` CHARACTER SET `utf8` COLLATE `utf8_bin`;'