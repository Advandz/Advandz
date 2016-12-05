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

# Pre-Configuring Advandz Environment
yum -y install unzip >> /dev/null 2>&1;
yum -y clean all >> /dev/null 2>&1;
yum -y remove nginx >> /dev/null 2>&1;
yum -y remove httpd >> /dev/null 2>&1;
yum -y remove lighttpd >> /dev/null 2>&1;
yum -y remove openlitespeed >> /dev/null 2>&1;
yum -y remove litespeed >> /dev/null 2>&1;
yum -y erase httpd nginx lighttpd openlitespeed litespeed >> /dev/null 2>&1;
yum -y update >> /dev/null 2>&1;