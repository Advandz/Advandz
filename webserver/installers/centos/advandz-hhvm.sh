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

# Install HHVM
yum update -y  >> /dev/null 2>&1;
rpm -Uvh http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-8.noarch.rpm  >> /dev/null 2>&1;

# HHVM Dependences
yum install cpp gcc-c++ cmake git psmisc {binutils,boost,jemalloc,numactl}-devel \
{ImageMagick,sqlite,tbb,bzip2,openldap,readline,elfutils-libelf,gmp,lz4,pcre}-devel \
lib{xslt,event,yaml,vpx,png,zip,icu,mcrypt,memcached,cap,dwarf}-devel \
{unixODBC,expat,mariadb}-devel lib{edit,curl,xml2,xslt}-devel \
glog-devel oniguruma-devel ocaml gperf enca libjpeg-turbo-devel openssl-devel \
mariadb mariadb-server make -y  >> /dev/null 2>&1;

yum install -y GeoIP  >> /dev/null 2>&1;
wget ftp://fr2.rpmfind.net/linux/epel/7/x86_64/l/libc-client-2007f-4.el7.1.x86_64.rpm  >> /dev/null 2>&1;
rpm -Uvh libc-client-2007f-4.el7.1.x86_64.rpm  >> /dev/null 2>&1;
rpm -Uvh http://mirrors.linuxeye.com/hhvm-repo/7/x86_64/hhvm-3.15.2-1.el7.centos.x86_64.rpm  >> /dev/null 2>&1;

mkdir /var/log/hhvm
echo " " > /var/log/hhvm/error.log

{
    echo "[Unit]";
    echo "Description=HHVM HipHop Virtual Machine (FCGI)";
    echo " ";
    echo "[Service]";
    echo "ExecStart=/usr/local/bin/hhvm --config /etc/hhvm/server.ini --user apache --mode daemon -vServer.Type=fastcgi -vServer.Port=9001";
    echo " ";
    echo "[Install]";
    echo "WantedBy=multi-user.target";
} >/usr/lib/systemd/system/hhvm.service

systemctl enable hhvm.service  >> /dev/null 2>&1;
systemctl start hhvm.service  >> /dev/null 2>&1;