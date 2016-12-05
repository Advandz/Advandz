p#!/bin/bash

#
# Advandz Stack Installer
# NOTE: All the included software, names and trademarks are property
# of the respective owners. The Advandz Team not provides 
# support, advice or guarantee of the third-party software included
# in this package. Every software included in this package (Advandz Stack)
# is under their own license.
# 
# @package Advandz
# @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
# @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
# @author The Advandz Team <team@advandz.com>
# 

#
# Variables
#
SERVER_ARCHITECTURE=$(uname -m);
SERVER_RAM=$(grep MemTotal /proc/meminfo | awk '{print $2}');
SERVER_RAM_GB_INT=$(expr $SERVER_RAM / 1000000);
SERVER_HOSTNAME=$(hostname);
PERCONA_ROOT_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);
sleep 1;
POWERDNS_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);
sleep 1;
ADVANDZ_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);
OSV=$(rpm -q --queryformat '%{VERSION}' centos-release);

#
# Architecture Error
#
if [ ${SERVER_ARCHITECTURE} != 'x86_64' ]; then
    clear;
    echo "o------------------------------------------------------------------o";
    echo "| Advandz Web Server Installer                                v1.0 |";
    echo "o------------------------------------------------------------------o";
    echo "|                                                                  |";
    echo "|   This installer only works in x86_64 systems.                   |";
    echo "|                                                                  |";
    echo "o------------------------------------------------------------------o";
    exit;
fi

#
# Main Screen
#
clear;
echo "o------------------------------------------------------------------o";
echo "| Advandz Web Server Installer                                v1.0 |";
echo "o------------------------------------------------------------------o";
echo "|                                                                  |";
echo "|   What is your Operative System?                                 |";
echo "|                                                                  |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Opt | Type                     | Version                 |   |";
echo "|   ============================================================   |";
echo "|   | [1] | Ubuntu                   | 14.04/15.04/15.10/16.04 |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | [2] | CentOS/RHEL/Cloud Linux  | 7                       |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | [3] | Debian                   | 7/8                     |   |";
echo "|   ------------------------------------------------------------   |";
echo "|                                                                  |";
echo "o------------------------------------------------------------------o";
echo " ";
echo "Choose an option: "
read option;

# Validate option
until [ "${option}" = "1" ] || [ "${option}" = "2" ] || [ "${option}" = "3" ] || [ "${option}" = "4" ]; do
    echo "Please enter a valid option: ";
    read option;
done


#
# Confirmation Screen
#
clear;
echo "o------------------------------------------------------------------o";
echo "| Advandz Web Server Installer                                v1.0 |";
echo "o------------------------------------------------------------------o";
echo "|                                                                  |";
echo "|   The following software will be installed:                      |";
echo "|                                                                  |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Name                      | Type                         |   |";
echo "|   ============================================================   |";
echo "|   | Apache                    | Web Server                   |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | NGINX                     | Reverse Proxy                |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Percona Server 5.7        | MySQL Replacement            |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | HHVM                      | PHP Replacement              |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | PowerDNS                  | DNS Server                   |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Pure-FTPD                 | FTP Server                   |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Advandz Web Server        | Server Control Panel         |   |";
echo "|   ------------------------------------------------------------   |";
echo "|                                                                  |";
echo "|                                 ┌────────────┐ ┌─────────────┐   |";
echo "|                                 │ [C] Cancel │ │ [I] Install │   |";
echo "|                                 └────────────┘ └─────────────┘   |";
echo "|                                                                  |";
echo "o------------------------------------------------------------------o";
echo " ";
echo "Choose an option: "
read choose;

# Validate Option
until [ "${choose}" = "C" ] || [ "${choose}" = "I" ]; do
    echo "Please enter a valid option: ";
    read choose;
done

# Abort Installation
if [ "${choose}" = "C" ]; then
    exit;
fi

#
# Installation
#
if [ "${option}" = "1" ]; then
    ##########################################
    # Ubuntu
    ##########################################
    echo "Not Available";
elif [ "${option}" = "2" ]; then
    ##########################################
    # CentOS Installation
    ##########################################

    # Pre-configuring Environment
    clear;
    echo "==================================";
    echo " Pre-configuring Environment..."
    echo "==================================";
    echo " ";
    echo "Progress [==                 ] 10%";
    ./installers/centos/advandz-preconf.sh

    # Installing HHVM
    clear;
    echo "==================================";
    echo " Installing HHVM..."
    echo "==================================";
    echo " ";
    echo "Progress [====               ] 20%";
    ./installers/centos/advandz-hhvm.sh

    # Installing Apache
    clear;
    echo "==================================";
    echo " Installing Apache..."
    echo "==================================";
    echo " ";
    echo "Progress [======             ] 30%";
    ./installers/centos/advandz-apache.sh

    # Installing Nginx
    clear;
    echo "==================================";
    echo " Installing Nginx..."
    echo "==================================";
    echo " ";
    echo "Progress [========           ] 40%";
    ./installers/centos/advandz-nginx.sh

    # Installing Percona Server
    clear;
    echo "==================================";
    echo " Installing Percona Server..."
    echo "==================================";
    echo " ";
    echo "Progress [==========         ] 50%";
    PERCONA_ROOT_PASSWORD=$(./installers/centos/advandz-percona.sh);

    # Installing PowerDNS
    clear;
    echo "==================================";
    echo " Installing PowerDNS..."
    echo "==================================";
    echo " ";
    echo "Progress [============       ] 60%";
    POWERDNS_PASSWORD=$(./installers/centos/advandz-powerdns.sh $PERCONA_ROOT_PASSWORD);

    # Installing Pure-FTPD
    clear;
    echo "==================================";
    echo " Installing Pure-FTPD..."
    echo "==================================";
    echo " ";
    echo "Progress [==============     ] 70%";
    ./installers/centos/advandz-pure-ftpd.sh

    # Installing Advandz
    clear;
    echo "==================================";
    echo " Installing Advandz..."
    echo "==================================";
    echo " ";
    echo "Progress [================   ] 80%";
    # TODO 

    # Setting up
    clear;
    echo "==================================";
    echo " Setting up..."
    echo "==================================";
    echo " ";
    echo "Progress [================== ] 90%";
    ./installers/centos/advandz-configure.sh

elif [ "${option}" = "3" ]; then
    ##########################################
    # Debian Installation
    ##########################################
    echo "Not Available";
fi

#
# Final Screen
#
clear;
echo "o------------------------------------------------------------------o";
echo "| Advandz Web Server Installer                                v1.0 |";
echo "o------------------------------------------------------------------o";
echo "|                                                                  |";
echo "|   Advandz Stack  has been installed succesfully.                 |";
echo "|   Please copy and save the following data in a safe place.       |";
echo "|                                                                  |";
echo "|   Advandz Control Panel User: admin                              |";
echo "|   Advandz Control Panel Password: $ADVANDZ_PASSWORD                   |";
echo "|   Advandz Control Panel Port: 2083                               |";
echo "|   Advandz Control Panel Port (SSL): 2087                         |";
echo "|                                                                  |";
echo "|   HHVM Socket: /var/run/hhvm/server.sock                         |";
echo "|   HHVM FastCGI Port: 9001                                        |";
echo "|                                                                  |";
echo "|   Percona Root User: root                                        |";
echo "|   Percona Root Password: $PERCONA_ROOT_PASSWORD                            |";
echo "|                                                                  |";
echo "|   PowerDNS Database User: powerdns                               |";
echo "|   PowerDNS Database Name: powerdns                               |";
echo "|   PowerDNS Database Password: $POWERDNS_PASSWORD                       |";
echo "|                                                                  |";
echo "|   You can access to http://$SERVER_HOSTNAME:2083/  ";
echo "|                                                                  |";
echo "|   NOTE: Before restart your server we recommend execute          |";
echo "|   \"mysql_secure_installation\" for secure your installation.      |";
echo "|                                                                  |";
echo "o------------------------------------------------------------------o";
