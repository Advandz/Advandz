#!/bin/bash

#
# Advandz Web Server Installer
# NOTE: All the included software, names and trademarks are property
# of the respective owners. The Advandz Team not provides 
# support, advice or guarantee of the third-party software included
# in this package. Every software included in this package
# is under their own license.
# 
# @package Advandz
# @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
# @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
# @author The Advandz Team <team@advandz.com>
# 

# Install Percona Server
PERCONA_ROOT_PASSWORD=$1;
POWERDNS_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);

mysql -u root -p$PERCONA_ROOT_PASSWORD -e "CREATE DATABASE powerdns;" >> /dev/null 2>&1;
mysql -u root -p$PERCONA_ROOT_PASSWORD -e "GRANT ALL ON powerdns.* TO 'powerdns'@'localhost' IDENTIFIED BY '$POWERDNS_PASSWORD';" >> /dev/null 2>&1;
mysql -u root -p$PERCONA_ROOT_PASSWORD -e "FLUSH PRIVILEGES;" >> /dev/null 2>&1;
{
    echo "CREATE TABLE domains (";
    echo "id INT auto_increment,";
    echo "name VARCHAR(255) NOT NULL,";
    echo "master VARCHAR(128) DEFAULT NULL,";
    echo "last_check INT DEFAULT NULL,";
    echo "type VARCHAR(6) NOT NULL,";
    echo "notified_serial INT DEFAULT NULL,";
    echo "account VARCHAR(40) DEFAULT NULL,";
    echo "primary key (id)";
    echo ");";
    echo " ";
    echo "CREATE UNIQUE INDEX name_index ON domains(name);";
    echo " ";
    echo "CREATE TABLE records (";
    echo "id INT auto_increment,";
    echo "domain_id INT DEFAULT NULL,";
    echo "name VARCHAR(255) DEFAULT NULL,";
    echo "type VARCHAR(6) DEFAULT NULL,";
    echo "content VARCHAR(255) DEFAULT NULL,";
    echo "ttl INT DEFAULT NULL,";
    echo "prio INT DEFAULT NULL,";
    echo "change_date INT DEFAULT NULL,";
    echo "primary key(id)";
    echo ");";
    echo " ";
    echo "CREATE INDEX rec_name_index ON records(name);";
    echo "CREATE INDEX nametype_index ON records(name,type);";
    echo "CREATE INDEX domain_id ON records(domain_id);";
    echo " ";
    echo "CREATE TABLE supermasters (";
    echo "ip VARCHAR(25) NOT NULL,";
    echo "nameserver VARCHAR(255) NOT NULL,";
    echo "account VARCHAR(40) DEFAULT NULL";
    echo ");";
} >powerdns.sql
mysql -u root -p$PERCONA_ROOT_PASSWORD "powerdns" < "powerdns.sql" >> /dev/null 2>&1;
rm -rf powerdns.sql >> /dev/null 2>&1;
rpm -Uhv http://mirror.cc.columbia.edu/pub/linux/epel/7/x86_64/e/epel-release-7-8.noarch.rpm >> /dev/null 2>&1;
yum -y install pdns-backend-mysql pdns >> /dev/null 2>&1;
chkconfig --levels 235 pdns on >> /dev/null 2>&1;
{
    echo "# MySQL Configuration file";
    echo " ";
    echo "launch=gmysql";
    echo " ";
    echo "gmysql-host=localhost";
    echo "gmysql-dbname=powerdns";
    echo "gmysql-user=powerdns";
    echo "gmysql-password=$POWERDNS_PASSWORD";
} >/etc/pdns/pdns.conf

systemctl enable pdns.service >> /dev/null 2>&1;
systemctl start pdns.service >> /dev/null 2>&1;

echo $POWERDNS_PASSWORD;
