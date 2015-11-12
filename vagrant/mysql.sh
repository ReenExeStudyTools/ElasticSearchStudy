#!/usr/bin/env bash
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password filtration"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password filtration"
sudo aptitude install -q -y -f  mysql-server mysql-client php5-mysql
mysql -uroot -pfiltration -e 'CREATE DATABASE `test` CHARACTER SET `utf8` COLLATE `utf8_bin`;'