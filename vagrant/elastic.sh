#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install openjdk-7-jre

wget https://download.elasticsearch.org/elasticsearch/release/org/elasticsearch/distribution/tar/elasticsearch/2.0.0/elasticsearch-2.0.0.tar.gz
tar -zxvf elasticsearch-2.0.0.tar.gz
mv elasticsearch-2.0.0 elasticsearch