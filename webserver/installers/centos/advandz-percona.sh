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
PERCONA_ROOT_NEW_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);

yum -y remove mariadb* >> /dev/null 2>&1;
yum -y remove mysql* >> /dev/null 2>&1;
yum -y install http://www.percona.com/downloads/percona-release/redhat/0.1-3/percona-release-0.1-3.noarch.rpm >> /dev/null 2>&1;
yum -y install Percona-Server-server-57 >> /dev/null 2>&1;
chkconfig --levels 235 mysqld on >> /dev/null 2>&1;
systemctl enable mysql >> /dev/null 2>&1;
systemctl start mysql >> /dev/null 2>&1;

# Get Percona Password
PERCONA_ROOT_PASSWORD_TEMP=$(cat /var/log/mysqld.log |grep generated);
PERCONA_ROOT_PASSWORD_DELIMITER="#";
PERCONA_ROOT_PASSWORD_REPLACED=${PERCONA_ROOT_PASSWORD_TEMP/: /$PERCONA_ROOT_PASSWORD_DELIMITER};
PERCONA_ROOT_PASSWORD=$(cut -d "#" -f 2 <<< "$PERCONA_ROOT_PASSWORD_REPLACED");

mysql -uroot -p$PERCONA_ROOT_PASSWORD --connect-expired-password -e "SET GLOBAL validate_password_policy=LOW;";
mysql -uroot -p$PERCONA_ROOT_PASSWORD --connect-expired-password -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$PERCONA_ROOT_NEW_PASSWORD';";

echo $PERCONA_ROOT_NEW_PASSWORD;