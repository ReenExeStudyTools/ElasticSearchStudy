#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install openjdk-7-jre

wget https://download.elastic.co/elasticsearch/elasticsearch/elasticsearch-1.7.2.deb
sudo dpkg -i elasticsearch-1.7.2.deb