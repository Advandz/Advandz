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

#
# Usage:
#   ./add-domain.sh --[server] --[reverse proxy] --[domain] --[port]
# Example:
# ./add-domain.sh --apache --nginx-reverse-proxy google.com 8080
#

#
# Adds a domain and creates a virtual host in Apache
# @param string Domain
# @param integer Apache Port
# 
function apache_add_domain {
    APACHE_DOMAIN=$1;
    APACHE_PORT=$2;
    if [ -z "$APACHE_PORT" ]; then
        APACHE_PORT="8080";
    fi

    # Generate Domain User
    USER_DELIMITER="";
    DOMAIN_USER=$(echo ${APACHE_DOMAIN/./$USER_DELIMITER});
    OS_USER=$(echo $DOMAIN_USER|cut -c1-30);

    # Add user
    adduser $OS_USER;

    # Create Folder
    DIRECTORY="/etc/advandz/domains/$APACHE_DOMAIN";
    if [ -d "$DIRECTORY" ]; then
        echo "ERROR : The entered domain already exists. : $APACHE_DOMAIN";
        exit;
    fi
    mkdir /etc/advandz/domains/$APACHE_DOMAIN;
    mkdir /etc/advandz/domains/$APACHE_DOMAIN/public_html;
    mkdir /etc/advandz/domains/$APACHE_DOMAIN/logs;
    chown -R $OS_USER:$OS_USER /etc/advandz/domains/$APACHE_DOMAIN;
    {
        echo "<html>";
        echo "<head>";
        echo "    <title>Welcome to $APACHE_DOMAIN!</title>";
        echo "</head>";
        echo "<body>";
        echo "    <h1>Success! The $APACHE_DOMAIN domain is working!</h1>";
        echo "</body>";
        echo "</html>";
    } >/etc/advandz/domains/$APACHE_DOMAIN/public_html/index.html

    # Create Domain Vhost
    {
        echo "Listen $APACHE_PORT";
        echo " ";
        echo "<VirtualHost *:$APACHE_PORT>";
        echo "  User $OS_USER";
        echo "  Group $OS_USER";
        echo "  ServerName www.$APACHE_DOMAIN";
        echo "  ServerAlias $APACHE_DOMAIN";
        echo "  RewriteEngine On";
        echo "  DocumentRoot /etc/advandz/domains/$APACHE_DOMAIN/public_html";
        echo "  ErrorLog /etc/advandz/domains/$APACHE_DOMAIN/logs/error.log";
        echo "  CustomLog /etc/advandz/domains/$APACHE_DOMAIN/logs/requests.log combined";
        echo " ";
        echo "  <Directory \"/etc/advandz/domains/$APACHE_DOMAIN/public_html\">";
        echo "    Options Indexes MultiViews FollowSymLinks";
        echo "    AllowOverride None";
        echo "    Order allow,deny";
        echo "    Allow from all";
        echo "  </Directory>";
        echo "</VirtualHost>";
    } >/etc/httpd/sites-available/$APACHE_DOMAIN.conf

    echo "SUCCESS : Domain has been added succesfully. : $OS_USER";

    service httpd restart >> /dev/null 2>&1;
}

#
# Script
#

# Add Domain
SERVER=$1;
REVERSE_PROXY=$2;
APACHE_PORT=$3;

# Add to Apache
if [ "${SERVER}" = "--apache" ] && [ "${REVERSE_PROXY}" != "--nginx-reverse-proxy" ]; then
    DOMAIN=$(echo $2| grep -P '(?=^.{5,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})$)');
    if [ -z "$DOMAIN" ]; then
        echo "ERROR : The entered domain is not a valid. : $2";
        exit;
    else
        apache_add_domain $DOMAIN $APACHE_PORT
    fi
fi

# Add to Apache with Reverse Proxy
if [ "${SERVER}" = "--apache" ] && [ "${REVERSE_PROXY}" = "--nginx-reverse-proxy" ]; then
    DOMAIN=$(echo $3| grep -P '(?=^.{5,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})$)');
    APACHE_PORT=$4;
    if [ -z "$DOMAIN" ]; then
        echo "ERROR : The entered domain is not a valid. : $3";
        exit;
    else
        echo "Add Domain";
    fi
fi

# Add to Nginx
if [ "${SERVER}" = "--nginx" ] && [ "${REVERSE_PROXY}" != "--nginx-reverse-proxy" ]; then
    DOMAIN=$(echo $2| grep -P '(?=^.{5,254}$)(^(?:(?!\d+\.)[a-zA-Z0-9_\-]{1,63}\.?)+(?:[a-zA-Z]{2,})$)');
    if [ -z "$DOMAIN" ]; then
        echo "ERROR : The entered domain is not a valid. : $2";
        exit;
    else
        echo "Add Domain";
    fi
fi