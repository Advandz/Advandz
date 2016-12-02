#!/bin/bash

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
# @license https://opensource.org/licenses/MIT The MIT License (MIT)
# @author The Advandz Team <team@advandz.com>
# 

#
# Variables
#
SERVER_RAM=$(grep MemTotal /proc/meminfo | awk '{print $2}');
SERVER_RAM_GB_INT=$(expr $SERVER_RAM / 1000000);
SERVER_HOSTNAME=$(hostname);
PERCONA_ROOT_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);
sleep 1;
POWERDNS_PASSWORD=$(date +%s | sha256sum | base64 | head -c 12 ; echo);

#
# Main Screen
#
clear;
echo "o------------------------------------------------------------------o";
echo "| Advandz Stack Installer                                     v1.0 |";
echo "o------------------------------------------------------------------o";
echo "|                                                                  |";
echo "|   What is your Operative System?                                 |";
echo "|                                                                  |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Opt | Type                     | Version                 |   |";
echo "|   ============================================================   |";
echo "|   | [1] | Ubuntu                   | 14.04/15.04/15.10/16.04 |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | [2] | CentOS/RHEL/Oracle Linux | 7                       |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | [4] | Debian                   | 8                       |   |";
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
echo "| Advandz Stack Installer                                     v1.0 |";
echo "o------------------------------------------------------------------o";
echo "|                                                                  |";
echo "|   The following software will be installed:                      |";
echo "|                                                                  |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Name                                | Type               |   |";
echo "|   ============================================================   |";
echo "|   | Lighttpd                            | Web Server         |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | Percona Server                      | MySQL Server       |   |";
echo "|   ------------------------------------------------------------   |";
if [ "${option}" = "2" ]; then
echo "|   | PHP 7.0                             | PHP                |   |";
else
echo "|   | HHVM                                | PHP Replacement    |   |";
fi
echo "|   ------------------------------------------------------------   |";
echo "|   | PowerDNS                            | DNS Server         |   |";
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

# Validate option
until [ "${choose}" = "C" ] || [ "${choose}" = "I" ]; do
    echo "Please enter a valid option: ";
    read choose;
done

# Abort installation
if [ "${choose}" = "C" ]; then
    exit;
fi

# Option actions
if [ "${option}" = "1" ]; then
    #
    # Ubuntu Installation
    #
    
    # Install HHVM
    clear;
    echo "==================================";
    echo " Installing HHVM...";
    echo "==================================";
    sudo apt-get install software-properties-common
    sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
    sudo add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"
    sudo apt-get update
    sudo apt-get -y install hhvm

    # Install Lighttpd
    clear;
    echo "==================================";
    echo " Installing Lighttpd...";
    echo "==================================";
    apt-get -y remove apache2*
    apt-get -y install lighttpd

    # Calculate Max FCGI processes
    MAX_FCGI_PROCESS = $(expr $SERVER_RAM_GB_INT * 10);
    if [ $SERVER_RAM_GB_INT = 0 ]; then
        MAX_FCGI_PROCESS = 5;
    fi
    echo "> This server is capable to run up to $MAX_FCGI_PROCESS FCGI processes with 6 Childs everyone.";

    # Configuring HHVM in Lighttpd
    clear;
    echo "==================================";
    echo " Configuring HHVM in Lighttpd...";
    echo "==================================";
    {
        echo "# -*- depends: fastcgi -*-";
        echo "# http://redmine.lighttpd.net/projects/lighttpd/wiki/Docs:ConfigurationOptions#mod_fastcgi-fastcgi";
        echo " ";
        echo "fastcgi.map-extensions = (\".php3\" => \".php\", \".php4\" => \".php\", \".hh\" => \".php\")";
        echo " ";
        echo "## Start an FastCGI server for hhvm";
        echo "fastcgi.server += (\".php\" => ";
        echo "    ((";
        echo "        \"socket\" => \"/var/run/hhvm/server.sock\",";
        echo "        \"max-procs\" => 100,";
        echo "        \"bin-environment\" => ( ";
        echo "            \"PHP_FCGI_CHILDREN\" => \"5\",";
        echo "            \"PHP_FCGI_MAX_REQUESTS\" => \"10000\"";
        echo "        ),";
        echo "        \"bin-copy-environment\" => (";
        echo "            \"PATH\", \"SHELL\", \"USER\"";
        echo "        ),";
        echo "        \"broken-scriptfilename\" => \"enable\"";
        echo "    ))";
        echo ")";
    } >/etc/lighttpd/conf-available/15-fastcgi-hhvm.conf
    {
        echo "server.modules = (";
        echo "        \"mod_access\",";
        echo "        \"mod_alias\",";
        echo "        \"mod_compress\",";
        echo "        \"mod_redirect\",";
        echo "        \"mod_rewrite\",";
        echo ")";
        echo "";
        echo "server.document-root        = \"/var/www/html\"";
        echo "server.upload-dirs          = ( \"/var/cache/lighttpd/uploads\" )";
        echo "server.errorlog             = \"/var/log/lighttpd/error.log\"";
        echo "server.pid-file             = \"/var/run/lighttpd.pid\"";
        echo "server.username             = \"www-data\"";
        echo "server.groupname            = \"www-data\"";
        echo "server.port                 = 80";
        echo "";
        echo "index-file.names            = ( \"index.php\", \"index.html\", \"index.hh\" )";
        echo "url.access-deny             = ( \"~\", \".inc\" )";
        echo "static-file.exclude-extensions = ( \".php\", \".pl\", \".fcgi\", \".hh\" )";
        echo "";
        echo "compress.cache-dir          = \"/var/cache/lighttpd/compress/\"";
        echo "compress.filetype           = ( \"application/javascript\", \"text/css\", \"text/html\", \"text/plain\" )";
        echo "";
        echo "# default listening port for IPv6 falls back to the IPv4 port";
        echo "## Use ipv6 if available";
        echo "#include_shell \"/usr/share/lighttpd/use-ipv6.pl \" + server.port";
        echo "include_shell \"/usr/share/lighttpd/create-mime.assign.pl\"";
        echo "include_shell \"/usr/share/lighttpd/include-conf-enabled.pl\"";
    } >/etc/lighttpd/lighttpd.conf
    sudo lighttpd-enable-mod fastcgi-hhvm
    sudo lighttpd-disable-mod fastcgi-php
    rm -rf /var/www/html/index.lighttpd.html
    {
        echo "
        <html>
        <head>
            <title>Advandz Stack</title>
            <link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\">
        </head>

        <body>
            <div class=\"container\" style=\"padding-top: 50px;\">
                <div class=\"panel panel-default\">
                    <div class=\"panel-heading\">
                        <h3 class=\"panel-title\">Advandz Stack</h3>
                    </div>
                    <div class=\"panel-body\">
                        <h5>It is possible you have reached this page because:</h5>
                        <ul class=\"list-group\">
                            <li class=\"list-group-item\">
                                <span class=\"glyphicon glyphicon-random\" aria-hidden=\"true\"></span> <b>The IP address has changed.</b>
                                <br>
                                <small>The IP address for this domain may have changed recently. Check your DNS settings to verify that the domain is set up correctly.</small>
                            </li>
                            <li class=\"list-group-item\">
                                <span class=\"glyphicon glyphicon-warning-sign\" aria-hidden=\"true\"></span> <b>There has been a server misconfiguration.</b>
                                <br>
                                <small>You must verify that your hosting provider has the correct IP address configured for your Lighttpd settings and DNS records.</small>
                            </li>
                            <li class=\"list-group-item\">
                                <span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> <b>The site may have moved to a different server.</b>
                                <br>
                                <small>The IP address for this domain may have changed recently. Check your DNS settings to verify that the domain is set up correctly.</small>
                            </li>
                        </ul>
                    </div>
                    <div class=\"panel-footer\">Copyright (c) <?php echo date('Y'); ?> <a href=\"http://advandz.com/\" target=\"_blank\">The Advandz Team</a>.</div>
                </div>
                <center>
                    <img style=\"max-width: 150px; margin-top: 15px; margin-bottom: 35px;\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAABJCAYAAACHMxsoAAAAAXNSR0IArs4c6QAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAACXBIWXMAAAsTAAALEwEAmpwYAAARsGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgICAgICAgICAgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiCiAgICAgICAgICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICAgICAgICAgIHhtbG5zOmV4aWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vZXhpZi8xLjAvIgogICAgICAgICAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICAgICAgICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyI+CiAgICAgICAgIDx4bXA6TW9kaWZ5RGF0ZT4yMDE2LTExLTI3VDExOjQyOjQ2LTA2OjAwPC94bXA6TW9kaWZ5RGF0ZT4KICAgICAgICAgPHhtcDpDcmVhdGVEYXRlPjIwMTYtMTEtMjdUMTE6MzA6NTctMDY6MDA8L3htcDpDcmVhdGVEYXRlPgogICAgICAgICA8eG1wOk1ldGFkYXRhRGF0ZT4yMDE2LTExLTI3VDExOjQyOjQ2LTA2OjAwPC94bXA6TWV0YWRhdGFEYXRlPgogICAgICAgICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1IChNYWNpbnRvc2gpPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgICAgIDx0aWZmOlNhbXBsZXNQZXJQaXhlbD4zPC90aWZmOlNhbXBsZXNQZXJQaXhlbD4KICAgICAgICAgPHRpZmY6SW1hZ2VXaWR0aD4yMTg3PC90aWZmOkltYWdlV2lkdGg+CiAgICAgICAgIDx0aWZmOkJpdHNQZXJTYW1wbGU+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpPjg8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaT44PC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGk+ODwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwvdGlmZjpCaXRzUGVyU2FtcGxlPgogICAgICAgICA8dGlmZjpSZXNvbHV0aW9uVW5pdD4yPC90aWZmOlJlc29sdXRpb25Vbml0PgogICAgICAgICA8dGlmZjpQaG90b21ldHJpY0ludGVycHJldGF0aW9uPjI8L3RpZmY6UGhvdG9tZXRyaWNJbnRlcnByZXRhdGlvbj4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgICAgPHRpZmY6SW1hZ2VMZW5ndGg+MjQzODwvdGlmZjpJbWFnZUxlbmd0aD4KICAgICAgICAgPHhtcE1NOkRlcml2ZWRGcm9tIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgPHN0UmVmOm9yaWdpbmFsRG9jdW1lbnRJRD45Q0M5RUI0QjJBOEYwN0VDRjQ5MjhDMDhEREYyNkI4Njwvc3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD54bXAuaWlkOjQ3YzI0ZWQ2LTkzNjUtNDkwNy1hYzI3LWUwOGI3NDhkNzViODwvc3RSZWY6aW5zdGFuY2VJRD4KICAgICAgICAgICAgPHN0UmVmOmRvY3VtZW50SUQ+OUNDOUVCNEIyQThGMDdFQ0Y0OTI4QzA4RERGMjZCODY8L3N0UmVmOmRvY3VtZW50SUQ+CiAgICAgICAgIDwveG1wTU06RGVyaXZlZEZyb20+CiAgICAgICAgIDx4bXBNTTpJbnN0YW5jZUlEPnhtcC5paWQ6Mzc2MDY0ZmItNDk1YS00NzE1LWI2MTMtY2YyNzM3Njk5Y2NkPC94bXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eG1wTU06SGlzdG9yeT4KICAgICAgICAgICAgPHJkZjpTZXE+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoTWFjaW50b3NoKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMS0yN1QxMTo0Mjo0Ni0wNjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo0N2MyNGVkNi05MzY1LTQ5MDctYWMyNy1lMDhiNzQ4ZDc1Yjg8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGltYWdlL2pwZWcgdG8gaW1hZ2UvcG5nPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+ZGVyaXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5jb252ZXJ0ZWQgZnJvbSBpbWFnZS9qcGVnIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoTWFjaW50b3NoKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMS0yN1QxMTo0Mjo0Ni0wNjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDozNzYwNjRmYi00OTVhLTQ3MTUtYjYxMy1jZjI3Mzc2OTljY2Q8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgIDwvcmRmOlNlcT4KICAgICAgICAgPC94bXBNTTpIaXN0b3J5PgogICAgICAgICA8eG1wTU06RG9jdW1lbnRJRD5hZG9iZTpkb2NpZDpwaG90b3Nob3A6ZGRjZmE0MWMtZjU1Ni0xMTc5LTkyOGQtOWQxYzE0YWRmOWYyPC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06T3JpZ2luYWxEb2N1bWVudElEPjlDQzlFQjRCMkE4RjA3RUNGNDkyOEMwOERERjI2Qjg2PC94bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDxleGlmOlBpeGVsWERpbWVuc2lvbj4xMDAwMDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOkV4aWZWZXJzaW9uPjAyMjE8L2V4aWY6RXhpZlZlcnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj4yNDM4PC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgICAgPGV4aWY6Q29sb3JTcGFjZT4xPC9leGlmOkNvbG9yU3BhY2U+CiAgICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2UvcG5nPC9kYzpmb3JtYXQ+CiAgICAgICAgIDxwaG90b3Nob3A6VGV4dExheWVycz4KICAgICAgICAgICAgPHJkZjpCYWc+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8cGhvdG9zaG9wOkxheWVyTmFtZT5BRFZBTkRaPC9waG90b3Nob3A6TGF5ZXJOYW1lPgogICAgICAgICAgICAgICAgICA8cGhvdG9zaG9wOkxheWVyVGV4dD5BRFZBTkRaPC9waG90b3Nob3A6TGF5ZXJUZXh0PgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgPC9yZGY6QmFnPgogICAgICAgICA8L3Bob3Rvc2hvcDpUZXh0TGF5ZXJzPgogICAgICAgICA8cGhvdG9zaG9wOklDQ1Byb2ZpbGU+c1JHQiBJRUM2MTk2Ni0yLjE8L3Bob3Rvc2hvcDpJQ0NQcm9maWxlPgogICAgICAgICA8cGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPgogICAgICAgICAgICA8cmRmOkJhZz4KICAgICAgICAgICAgICAgPHJkZjpsaT45Q0M5RUI0QjJBOEYwN0VDRjQ5MjhDMDhEREYyNkI4NjwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpCYWc+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\">
                </center>
            </div>
        </body>
        </html>";
    } >/var/www/html/index.php

    # Configuring Lighttpd in HHVM
    clear;
    echo "==================================";
    echo " Configuring Lighttpd in HHVM...";
    echo "==================================";
    {
        echo "; php options";
        echo " ";
        echo "pid = /var/run/hhvm/pid";
        echo " ";
        echo "; hhvm specific";
        echo " ";
        echo "hhvm.server.file_socket = /var/run/hhvm/server.sock";
        echo "//hhvm.server.port = 9000";
        echo "hhvm.server.type = fastcgi";
        echo "hhvm.server.default_document = index.php";
        echo "hhvm.log.use_log_file = true";
        echo "hhvm.log.file = /var/log/hhvm/error.log";
        echo "hhvm.repo.central.path = /var/run/hhvm/hhvm.hhbc";
    } >/etc/hhvm/server.ini
    sudo update-rc.d hhvm defaults
    sudo /etc/init.d/hhvm start

    # Installing Percona Server
    clear;
    echo "==================================";
    echo " Installing Percona Server..."
    echo "==================================";
    apt-get -y remove mysql-server*
    apt-get -y install zlib1g-dev
    apt-get -y install libaio1
    apt-get -y install libmecab2
    apt-get -y install zlib1g-dev
    mkdir percona
    cd percona
    wget https://repo.percona.com/apt/percona-release_0.1-4.$(lsb_release -sc)_all.deb
    dpkg -i percona-release_0.1-4.$(lsb_release -sc)_all.deb
    sudo apt-get update
    sudo DEBIAN_FRONTEND=noninteractive apt-get -y install percona-server-server-5.7
    apt-get -fy install
    cd ..
    rm -rf percona
    
    # Installing PowerDNS
    clear;
    echo "==================================";
    echo " Installing PowerDNS..."
    echo "==================================";
    mysql -u root -e "CREATE DATABASE powerdns;"
    mysql -u root -e "GRANT ALL ON powerdns.* TO 'powerdns'@'localhost' IDENTIFIED BY '$POWERDNS_PASSWORD';"
    mysql -u root -e "FLUSH PRIVILEGES;"
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
    mysql -u root "powerdns" < "powerdns.sql"
    rm -rf powerdns.sql
    export DEBIAN_FRONTEND=noninteractive
    apt-get install -y pdns-server pdns-backend-mysql
    rm /etc/powerdns/pdns.d/*
    {
        echo "# MySQL Configuration file";
        echo " ";
        echo "launch=gmysql";
        echo " ";
        echo "gmysql-host=localhost";
        echo "gmysql-dbname=powerdns";
        echo "gmysql-user=powerdns";
        echo "gmysql-password=$POWERDNS_PASSWORD";
    } >/etc/powerdns/pdns.d/pdns.local.gmysql.conf

    # Finalizing 
    /etc/init.d/lighttpd restart
    /etc/init.d/pdns start

    # Set Root Password for Percona
    sudo /etc/init.d/mysql stop
    mysql -uroot -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$PERCONA_ROOT_PASSWORD';";
    sudo /etc/init.d/mysql start

elif [ "${option}" = "2" ]; then
    #
    # CentOS 7 Installation
    #
    
    # Install HHVM
    clear;
    echo "==================================";
    echo " Installing HHVM...";
    echo "==================================";
    yum update -y
    rpm -Uvh http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-8.noarch.rpm

    # HHVM Dependences
    yum install cpp gcc-c++ cmake git psmisc {binutils,boost,jemalloc,numactl}-devel \
    {ImageMagick,sqlite,tbb,bzip2,openldap,readline,elfutils-libelf,gmp,lz4,pcre}-devel \
    lib{xslt,event,yaml,vpx,png,zip,icu,mcrypt,memcached,cap,dwarf}-devel \
    {unixODBC,expat,mariadb}-devel lib{edit,curl,xml2,xslt}-devel \
    glog-devel oniguruma-devel ocaml gperf enca libjpeg-turbo-devel openssl-devel \
    mariadb mariadb-server make -y

    yum install -y GeoIP
    wget ftp://fr2.rpmfind.net/linux/epel/7/x86_64/l/libc-client-2007f-4.el7.1.x86_64.rpm
    rpm -Uvh libc-client-2007f-4.el7.1.x86_64.rpm
    rpm -Uvh http://mirrors.linuxeye.com/hhvm-repo/7/x86_64/hhvm-3.15.2-1.el7.centos.x86_64.rpm

    clear;
    echo "==================================";
    echo " Installing PHP 7.0...";
    echo "==================================";
    rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
    rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
    yum -y install php70w-fpm php70w-opcache
    {
        echo "; Unix user/group of processes
; Note: The user is mandatory. If the group is not set, the default user's group
;       will be used.
; RPM: apache Choosed to be able to access some dir as httpd
user = lighttpd
; RPM: Keep a group allowed to write in log dir.
group = lighttpd

; The address on which to accept FastCGI requests.
; Valid syntaxes are:
;   'ip.add.re.ss:port'    - to listen on a TCP socket to a specific IPv4 address on
;                            a specific port;
;   '[ip:6:addr:ess]:port' - to listen on a TCP socket to a specific IPv6 address on
;                            a specific port;
;   'port'                 - to listen on a TCP socket to all addresses
;                            (IPv6 and IPv4-mapped) on a specific port;
;   '/path/to/unix/socket' - to listen on a unix socket.
; Note: This value is mandatory.
listen = 127.0.0.1:9000

; Set listen(2) backlog.
; Default Value: 511 (-1 on FreeBSD and OpenBSD)
;listen.backlog = 511

; Set permissions for unix socket, if one is used. In Linux, read/write
; permissions must be set in order to allow connections from a web server. Many
; BSD-derived systems allow connections regardless of permissions.
; Default Values: user and group are set as the running user
;                 mode is set to 0660
;listen.owner = nobody
;listen.group = nobody
;listen.mode = 0660
; When POSIX Access Control Lists are supported you can set them using
; these options, value is a comma separated list of user/group names.
; When set, listen.owner and listen.group are ignored
;listen.acl_users =
;listen.acl_groups =

; List of addresses (IPv4/IPv6) of FastCGI clients which are allowed to connect.
; Equivalent to the FCGI_WEB_SERVER_ADDRS environment variable in the original
; PHP FCGI (5.2.2+). Makes sense only with a tcp listening socket. Each address
; must be separated by a comma. If this value is left blank, connections will be
; accepted from any ip address.
; Default Value: any
listen.allowed_clients = 127.0.0.1

; Specify the nice(2) priority to apply to the pool processes (only if set)
; The value can vary from -19 (highest priority) to 20 (lower priority)
; Note: - It will only work if the FPM master process is launched as root
;       - The pool processes will inherit the master process priority
;         unless it specified otherwise
; Default Value: no set
; process.priority = -19

; Choose how the process manager will control the number of child processes.
; Possible Values:
;   static  - a fixed number (pm.max_children) of child processes;
;   dynamic - the number of child processes are set dynamically based on the
;             following directives. With this process management, there will be
;             always at least 1 children.
;             pm.max_children      - the maximum number of children that can
;                                    be alive at the same time.
;             pm.start_servers     - the number of children created on startup.
;             pm.min_spare_servers - the minimum number of children in 'idle'
;                                    state (waiting to process). If the number
;                                    of 'idle' processes is less than this
;                                    number then some children will be created.
;             pm.max_spare_servers - the maximum number of children in 'idle'
;                                    state (waiting to process). If the number
;                                    of 'idle' processes is greater than this
;                                    number then some children will be killed.
;  ondemand - no children are created at startup. Children will be forked when
;             new requests will connect. The following parameter are used:
;             pm.max_children           - the maximum number of children that
;                                         can be alive at the same time.
;             pm.process_idle_timeout   - The number of seconds after which
;                                         an idle process will be killed.
; Note: This value is mandatory.
pm = dynamic

; The number of child processes to be created when pm is set to 'static' and the
; maximum number of child processes when pm is set to 'dynamic' or 'ondemand'.
; This value sets the limit on the number of simultaneous requests that will be
; served. Equivalent to the ApacheMaxClients directive with mpm_prefork.
; Equivalent to the PHP_FCGI_CHILDREN environment variable in the original PHP
; CGI.
; Note: Used when pm is set to 'static', 'dynamic' or 'ondemand'
; Note: This value is mandatory.
pm.max_children = 50

; The number of child processes created on startup.
; Note: Used only when pm is set to 'dynamic'
; Default Value: min_spare_servers + (max_spare_servers - min_spare_servers) / 2
pm.start_servers = 5

; The desired minimum number of idle server processes.
; Note: Used only when pm is set to 'dynamic'
; Note: Mandatory when pm is set to 'dynamic'
pm.min_spare_servers = 5

; The desired maximum number of idle server processes.
; Note: Used only when pm is set to 'dynamic'
; Note: Mandatory when pm is set to 'dynamic'
pm.max_spare_servers = 35

; The number of seconds after which an idle process will be killed.
; Note: Used only when pm is set to 'ondemand'
; Default Value: 10s
;pm.process_idle_timeout = 10s;

; The number of requests each child process should execute before respawning.
; This can be useful to work around memory leaks in 3rd party libraries. For
; endless request processing specify '0'. Equivalent to PHP_FCGI_MAX_REQUESTS.
; Default Value: 0
;pm.max_requests = 500

; The URI to view the FPM status page. If this value is not set, no URI will be
; recognized as a status page. It shows the following informations:
;   pool                 - the name of the pool;
;   process manager      - static, dynamic or ondemand;
;   start time           - the date and time FPM has started;
;   start since          - number of seconds since FPM has started;
;   accepted conn        - the number of request accepted by the pool;
;   listen queue         - the number of request in the queue of pending
;                          connections (see backlog in listen(2));
;   max listen queue     - the maximum number of requests in the queue
;                          of pending connections since FPM has started;
;   listen queue len     - the size of the socket queue of pending connections;
;   idle processes       - the number of idle processes;
;   active processes     - the number of active processes;
;   total processes      - the number of idle + active processes;
;   max active processes - the maximum number of active processes since FPM
;                          has started;
;   max children reached - number of times, the process limit has been reached,
;                          when pm tries to start more children (works only for
;                          pm 'dynamic' and 'ondemand');
; Value are updated in real time.
; Example output:
;   pool:                 www
;   process manager:      static
;   start time:           01/Jul/2011:17:53:49 +0200
;   start since:          62636
;   accepted conn:        190460
;   listen queue:         0
;   max listen queue:     1
;   listen queue len:     42
;   idle processes:       4
;   active processes:     11
;   total processes:      15
;   max active processes: 12
;   max children reached: 0
;
; By default the status page output is formatted as text/plain. Passing either
; 'html', 'xml' or 'json' in the query string will return the corresponding
; output syntax. Example:
;   http://www.foo.bar/status
;   http://www.foo.bar/status?json
;   http://www.foo.bar/status?html
;   http://www.foo.bar/status?xml
;
; By default the status page only outputs short status. Passing 'full' in the
; query string will also return status for each pool process.
; Example:
;   http://www.foo.bar/status?full
;   http://www.foo.bar/status?json&full
;   http://www.foo.bar/status?html&full
;   http://www.foo.bar/status?xml&full
; The Full status returns for each process:
;   pid                  - the PID of the process;
;   state                - the state of the process (Idle, Running, ...);
;   start time           - the date and time the process has started;
;   start since          - the number of seconds since the process has started;
;   requests             - the number of requests the process has served;
;   request duration     - the duration in µs of the requests;
;   request method       - the request method (GET, POST, ...);
;   request URI          - the request URI with the query string;
;   content length       - the content length of the request (only with POST);
;   user                 - the user (PHP_AUTH_USER) (or '-' if not set);
;   script               - the main script called (or '-' if not set);
;   last request cpu     - the %cpu the last request consumed
;                          it's always 0 if the process is not in Idle state
;                          because CPU calculation is done when the request
;                          processing has terminated;
;   last request memory  - the max amount of memory the last request consumed
;                          it's always 0 if the process is not in Idle state
;                          because memory calculation is done when the request
;                          processing has terminated;
; If the process is in Idle state, then informations are related to the
; last request the process has served. Otherwise informations are related to
; the current request being served.
; Example output:
;   ************************
;   pid:                  31330
;   state:                Running
;   start time:           01/Jul/2011:17:53:49 +0200
;   start since:          63087
;   requests:             12808
;   request duration:     1250261
;   request method:       GET
;   request URI:          /test_mem.php?N=10000
;   content length:       0
;   user:                 -
;   script:               /home/fat/web/docs/php/test_mem.php
;   last request cpu:     0.00
;   last request memory:  0
;
; Note: There is a real-time FPM status monitoring sample web page available
;       It's available in: @EXPANDED_DATADIR@/fpm/status.html
;
; Note: The value must start with a leading slash (/). The value can be
;       anything, but it may not be a good idea to use the .php extension or it
;       may conflict with a real PHP file.
; Default Value: not set
;pm.status_path = /status

; The ping URI to call the monitoring page of FPM. If this value is not set, no
; URI will be recognized as a ping page. This could be used to test from outside
; that FPM is alive and responding, or to
; - create a graph of FPM availability (rrd or such);
; - remove a server from a group if it is not responding (load balancing);
; - trigger alerts for the operating team (24/7).
; Note: The value must start with a leading slash (/). The value can be
;       anything, but it may not be a good idea to use the .php extension or it
;       may conflict with a real PHP file.
; Default Value: not set
;ping.path = /ping

; This directive may be used to customize the response of a ping request. The
; response is formatted as text/plain with a 200 response code.
; Default Value: pong
;ping.response = pong

; The access log file
; Default: not set
;access.log = log/\$pool.access.log

; The access log format.
; The following syntax is allowed
;  %%: the '%' character
;  %C: %CPU used by the request
;      it can accept the following format:
;      - %{user}C for user CPU only
;      - %{system}C for system CPU only
;      - %{total}C  for user + system CPU (default)
;  %d: time taken to serve the request
;      it can accept the following format:
;      - %{seconds}d (default)
;      - %{miliseconds}d
;      - %{mili}d
;      - %{microseconds}d
;      - %{micro}d
;  %e: an environment variable (same as \$_ENV or \$_SERVER)
;      it must be associated with embraces to specify the name of the env
;      variable. Some exemples:
;      - server specifics like: %{REQUEST_METHOD}e or %{SERVER_PROTOCOL}e
;      - HTTP headers like: %{HTTP_HOST}e or %{HTTP_USER_AGENT}e
;  %f: script filename
;  %l: content-length of the request (for POST request only)
;  %m: request method
;  %M: peak of memory allocated by PHP
;      it can accept the following format:
;      - %{bytes}M (default)
;      - %{kilobytes}M
;      - %{kilo}M
;      - %{megabytes}M
;      - %{mega}M
;  %n: pool name
;  %o: output header
;      it must be associated with embraces to specify the name of the header:
;      - %{Content-Type}o
;      - %{X-Powered-By}o
;      - %{Transfert-Encoding}o
;      - ....
;  %p: PID of the child that serviced the request
;  %P: PID of the parent of the child that serviced the request
;  %q: the query string
;  %Q: the '?' character if query string exists
;  %r: the request URI (without the query string, see %q and %Q)
;  %R: remote IP address
;  %s: status (response code)
;  %t: server time the request was received
;      it can accept a strftime(3) format:
;      %d/%b/%Y:%H:%M:%S %z (default)
;      The strftime(3) format must be encapsuled in a %{<strftime_format>}t tag
;      e.g. for a ISO8601 formatted timestring, use: %{%Y-%m-%dT%H:%M:%S%z}t
;  %T: time the log has been written (the request has finished)
;      it can accept a strftime(3) format:
;      %d/%b/%Y:%H:%M:%S %z (default)
;      The strftime(3) format must be encapsuled in a %{<strftime_format>}t tag
;      e.g. for a ISO8601 formatted timestring, use: %{%Y-%m-%dT%H:%M:%S%z}t
;  %u: remote user
;
; Default: \"%R - %u %t \\"%m %r\\" %s\"
;access.format = \"%R - %u %t \\"%m %r%Q%q\\" %s %f %{mili}d %{kilo}M %C%%\"

; The log file for slow requests
; Default Value: not set
; Note: slowlog is mandatory if request_slowlog_timeout is set
slowlog = /var/log/php-fpm/www-slow.log

; The timeout for serving a single request after which a PHP backtrace will be
; dumped to the 'slowlog' file. A value of '0s' means 'off'.
; Available units: s(econds)(default), m(inutes), h(ours), or d(ays)
; Default Value: 0
;request_slowlog_timeout = 0

; The timeout for serving a single request after which the worker process will
; be killed. This option should be used when the 'max_execution_time' ini option
; does not stop script execution for some reason. A value of '0' means 'off'.
; Available units: s(econds)(default), m(inutes), h(ours), or d(ays)
; Default Value: 0
;request_terminate_timeout = 0

; Set open file descriptor rlimit.
; Default Value: system defined value
;rlimit_files = 1024

; Set max core size rlimit.
; Possible Values: 'unlimited' or an integer greater or equal to 0
; Default Value: system defined value
;rlimit_core = 0

; Chroot to this directory at the start. This value must be defined as an
; absolute path. When this value is not set, chroot is not used.
; Note: chrooting is a great security feature and should be used whenever
;       possible. However, all PHP paths will be relative to the chroot
;       (error_log, sessions.save_path, ...).
; Default Value: not set
;chroot =

; Chdir to this directory at the start.
; Note: relative path can be used.
; Default Value: current directory or / when chroot
;chdir = /var/www

; Redirect worker stdout and stderr into main error log. If not set, stdout and
; stderr will be redirected to /dev/null according to FastCGI specs.
; Note: on highloaded environement, this can cause some delay in the page
; process time (several ms).
; Default Value: no
;catch_workers_output = yes

; Clear environment in FPM workers
; Prevents arbitrary environment variables from reaching FPM worker processes
; by clearing the environment in workers before env vars specified in this
; pool configuration are added.
; Setting to \"no\" will make all environment variables available to PHP code
; via getenv(), \$_ENV and \$_SERVER.
; Default Value: yes
;clear_env = no

; Limits the extensions of the main script FPM will allow to parse. This can
; prevent configuration mistakes on the web server side. You should only limit
; FPM to .php extensions to prevent malicious users to use other extensions to
; exectute php code.
; Note: set an empty value to allow all extensions.
; Default Value: .php
;security.limit_extensions = .php .php3 .php4 .php5 .php7

; Pass environment variables like LD_LIBRARY_PATH. All \$VARIABLEs are taken from
; the current environment.
; Default Value: clean env
;env[HOSTNAME] = \$HOSTNAME
;env[PATH] = /usr/local/bin:/usr/bin:/bin
;env[TMP] = /tmp
;env[TMPDIR] = /tmp
;env[TEMP] = /tmp

; Additional php.ini defines, specific to this pool of workers. These settings
; overwrite the values previously defined in the php.ini. The directives are the
; same as the PHP SAPI:
;   php_value/php_flag             - you can set classic ini defines which can
;                                    be overwritten from PHP call 'ini_set'.
;   php_admin_value/php_admin_flag - these directives won't be overwritten by
;                                     PHP call 'ini_set'
; For php_*flag, valid values are on, off, 1, 0, true, false, yes or no.

; Defining 'extension' will load the corresponding shared extension from
; extension_dir. Defining 'disable_functions' or 'disable_classes' will not
; overwrite previously defined php.ini values, but will append the new value
; instead.

; Default Value: nothing is defined by default except the values in php.ini and
;                specified at startup with the -d argument
;php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f www@my.domain.com
;php_flag[display_errors] = off
php_admin_value[error_log] = /var/log/php-fpm/www-error.log
php_admin_flag[log_errors] = on
;php_admin_value[memory_limit] = 128M

; Set session path to a directory owned by process user
php_value[session.save_handler] = files
php_value[session.save_path]    = /var/lib/php/session
php_value[soap.wsdl_cache_dir]  = /var/lib/php/wsdlcache";
    } >/etc/php-fpm.d/www.conf

    {
        echo "
[PHP]

;;;;;;;;;;;;;;;;;;;
; About php.ini   ;
;;;;;;;;;;;;;;;;;;;
; PHP's initialization file, generally called php.ini, is responsible for
; configuring many of the aspects of PHP's behavior.

; PHP attempts to find and load this configuration from a number of locations.
; The following is a summary of its search order:
; 1. SAPI module specific location.
; 2. The PHPRC environment variable. (As of PHP 5.2.0)
; 3. A number of predefined registry keys on Windows (As of PHP 5.2.0)
; 4. Current working directory (except CLI)
; 5. The web server's directory (for SAPI modules), or directory of PHP
; (otherwise in Windows)
; 6. The directory from the --with-config-file-path compile time option, or the
; Windows directory (C:\windows or C:\winnt)
; See the PHP docs for more specific information.
; http://php.net/configuration.file

; The syntax of the file is extremely simple.  Whitespace and lines
; beginning with a semicolon are silently ignored (as you probably guessed).
; Section headers (e.g. [Foo]) are also silently ignored, even though
; they might mean something in the future.

; Directives following the section heading [PATH=/www/mysite] only
; apply to PHP files in the /www/mysite directory.  Directives
; following the section heading [HOST=www.example.com] only apply to
; PHP files served from www.example.com.  Directives set in these
; special sections cannot be overridden by user-defined INI files or
; at runtime. Currently, [PATH=] and [HOST=] sections only work under
; CGI/FastCGI.
; http://php.net/ini.sections

; Directives are specified using the following syntax:
; directive = value
; Directive names are *case sensitive* - foo=bar is different from FOO=bar.
; Directives are variables used to configure PHP or PHP extensions.
; There is no name validation.  If PHP can't find an expected
; directive because it is not set or is mistyped, a default value will be used.

; The value can be a string, a number, a PHP constant (e.g. E_ALL or M_PI), one
; of the INI constants (On, Off, True, False, Yes, No and None) or an expression
; (e.g. E_ALL & ~E_NOTICE), a quoted string (\"bar\"), or a reference to a
; previously set variable or directive (e.g. \${foo})

; Expressions in the INI file are limited to bitwise operators and parentheses:
; |  bitwise OR
; ^  bitwise XOR
; &  bitwise AND
; ~  bitwise NOT
; !  boolean NOT

; Boolean flags can be turned on using the values 1, On, True or Yes.
; They can be turned off using the values 0, Off, False or No.

; An empty string can be denoted by simply not writing anything after the equal
; sign, or by using the None keyword:

;  foo =         ; sets foo to an empty string
;  foo = None    ; sets foo to an empty string
;  foo = \"None\"  ; sets foo to the string 'None'

; If you use constants in your value, and these constants belong to a
; dynamically loaded extension (either a PHP extension or a Zend extension),
; you may only use these constants *after* the line that loads the extension.

;;;;;;;;;;;;;;;;;;;
; About this file ;
;;;;;;;;;;;;;;;;;;;
; PHP comes packaged with two INI files. One that is recommended to be used
; in production environments and one that is recommended to be used in
; development environments.

; php.ini-production contains settings which hold security, performance and
; best practices at its core. But please be aware, these settings may break
; compatibility with older or less security conscience applications. We
; recommending using the production ini in production and testing environments.

; php.ini-development is very similar to its production variant, except it is
; much more verbose when it comes to errors. We recommend using the
; development version only in development environments, as errors shown to
; application users can inadvertently leak otherwise secure information.

; This is php.ini-production INI file.

;;;;;;;;;;;;;;;;;;;
; Quick Reference ;
;;;;;;;;;;;;;;;;;;;
; The following are all the settings which are different in either the production
; or development versions of the INIs with respect to PHP's default behavior.
; Please see the actual settings later in the document for more details as to why
; we recommend these changes in PHP's behavior.

; display_errors
;   Default Value: On
;   Development Value: On
;   Production Value: Off

; display_startup_errors
;   Default Value: Off
;   Development Value: On
;   Production Value: Off

; error_reporting
;   Default Value: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
;   Development Value: E_ALL
;   Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT

; html_errors
;   Default Value: On
;   Development Value: On
;   Production value: On

; log_errors
;   Default Value: Off
;   Development Value: On
;   Production Value: On

; max_input_time
;   Default Value: -1 (Unlimited)
;   Development Value: 60 (60 seconds)
;   Production Value: 60 (60 seconds)

; output_buffering
;   Default Value: Off
;   Development Value: 4096
;   Production Value: 4096

; register_argc_argv
;   Default Value: On
;   Development Value: Off
;   Production Value: Off

; request_order
;   Default Value: None
;   Development Value: \"GP\"
;   Production Value: \"GP\"

; session.gc_divisor
;   Default Value: 100
;   Development Value: 1000
;   Production Value: 1000

; session.hash_bits_per_character
;   Default Value: 4
;   Development Value: 5
;   Production Value: 5

; short_open_tag
;   Default Value: On
;   Development Value: Off
;   Production Value: Off

; track_errors
;   Default Value: Off
;   Development Value: On
;   Production Value: Off

; url_rewriter.tags
;   Default Value: \"a=href,area=href,frame=src,form=,fieldset=\"
;   Development Value: \"a=href,area=href,frame=src,input=src,form=fakeentry\"
;   Production Value: \"a=href,area=href,frame=src,input=src,form=fakeentry\"

; variables_order
;   Default Value: \"EGPCS\"
;   Development Value: \"GPCS\"
;   Production Value: \"GPCS\"

;;;;;;;;;;;;;;;;;;;;
; php.ini Options  ;
;;;;;;;;;;;;;;;;;;;;
; Name for user-defined php.ini (.htaccess) files. Default is \".user.ini\"
;user_ini.filename = \".user.ini\"

; To disable this feature set this option to empty value
;user_ini.filename =

; TTL for user-defined php.ini files (time-to-live) in seconds. Default is 300 seconds (5 minutes)
;user_ini.cache_ttl = 300

;;;;;;;;;;;;;;;;;;;;
; Language Options ;
;;;;;;;;;;;;;;;;;;;;

; Enable the PHP scripting language engine under Apache.
; http://php.net/engine
engine = On

; This directive determines whether or not PHP will recognize code between
; <? and ?> tags as PHP source which should be processed as such. It is
; generally recommended that <?php and ?> should be used and that this feature
; should be disabled, as enabling it may result in issues when generating XML
; documents, however this remains supported for backward compatibility reasons.
; Note that this directive does not control the <?= shorthand tag, which can be
; used regardless of this directive.
; Default Value: On
; Development Value: Off
; Production Value: Off
; http://php.net/short-open-tag
short_open_tag = Off

; The number of significant digits displayed in floating point numbers.
; http://php.net/precision
precision = 14

; Output buffering is a mechanism for controlling how much output data
; (excluding headers and cookies) PHP should keep internally before pushing that
; data to the client. If your application's output exceeds this setting, PHP
; will send that data in chunks of roughly the size you specify.
; Turning on this setting and managing its maximum buffer size can yield some
; interesting side-effects depending on your application and web server.
; You may be able to send headers and cookies after you've already sent output
; through print or echo. You also may see performance benefits if your server is
; emitting less packets due to buffered output versus PHP streaming the output
; as it gets it. On production servers, 4096 bytes is a good setting for performance
; reasons.
; Note: Output buffering can also be controlled via Output Buffering Control
;   functions.
; Possible Values:
;   On = Enabled and buffer is unlimited. (Use with caution)
;   Off = Disabled
;   Integer = Enables the buffer and sets its maximum size in bytes.
; Note: This directive is hardcoded to Off for the CLI SAPI
; Default Value: Off
; Development Value: 4096
; Production Value: 4096
; http://php.net/output-buffering
output_buffering = 4096

; You can redirect all of the output of your scripts to a function.  For
; example, if you set output_handler to \"mb_output_handler\", character
; encoding will be transparently converted to the specified encoding.
; Setting any output handler automatically turns on output buffering.
; Note: People who wrote portable scripts should not depend on this ini
;   directive. Instead, explicitly set the output handler using ob_start().
;   Using this ini directive may cause problems unless you know what script
;   is doing.
; Note: You cannot use both \"mb_output_handler\" with \"ob_iconv_handler\"
;   and you cannot use both \"ob_gzhandler\" and \"zlib.output_compression\".
; Note: output_handler must be empty if this is set 'On' !!!!
;   Instead you must use zlib.output_handler.
; http://php.net/output-handler
;output_handler =

; Transparent output compression using the zlib library
; Valid values for this option are 'off', 'on', or a specific buffer size
; to be used for compression (default is 4KB)
; Note: Resulting chunk size may vary due to nature of compression. PHP
;   outputs chunks that are few hundreds bytes each as a result of
;   compression. If you prefer a larger chunk size for better
;   performance, enable output_buffering in addition.
; Note: You need to use zlib.output_handler instead of the standard
;   output_handler, or otherwise the output will be corrupted.
; http://php.net/zlib.output-compression
zlib.output_compression = Off

; http://php.net/zlib.output-compression-level
;zlib.output_compression_level = -1

; You cannot specify additional output handlers if zlib.output_compression
; is activated here. This setting does the same as output_handler but in
; a different order.
; http://php.net/zlib.output-handler
;zlib.output_handler =

; Implicit flush tells PHP to tell the output layer to flush itself
; automatically after every output block.  This is equivalent to calling the
; PHP function flush() after each and every call to print() or echo() and each
; and every HTML block.  Turning this option on has serious performance
; implications and is generally recommended for debugging purposes only.
; http://php.net/implicit-flush
; Note: This directive is hardcoded to On for the CLI SAPI
implicit_flush = Off

; The unserialize callback function will be called (with the undefined class'
; name as parameter), if the unserializer finds an undefined class
; which should be instantiated. A warning appears if the specified function is
; not defined, or if the function doesn't include/implement the missing class.
; So only set this entry, if you really want to implement such a
; callback-function.
unserialize_callback_func =

; When floats & doubles are serialized store serialize_precision significant
; digits after the floating point. The default value ensures that when floats
; are decoded with unserialize, the data will remain the same.
serialize_precision = 17

; open_basedir, if set, limits all file operations to the defined directory
; and below.  This directive makes most sense if used in a per-directory
; or per-virtualhost web server configuration file.
; http://php.net/open-basedir
;open_basedir =

; This directive allows you to disable certain functions for security reasons.
; It receives a comma-delimited list of function names.
; http://php.net/disable-functions
disable_functions =

; This directive allows you to disable certain classes for security reasons.
; It receives a comma-delimited list of class names.
; http://php.net/disable-classes
disable_classes =

; Colors for Syntax Highlighting mode.  Anything that's acceptable in
; <span style=\"color: ???????\"> would work.
; http://php.net/syntax-highlighting
;highlight.string  = #DD0000
;highlight.comment = #FF9900
;highlight.keyword = #007700
;highlight.default = #0000BB
;highlight.html    = #000000

; If enabled, the request will be allowed to complete even if the user aborts
; the request. Consider enabling it if executing long requests, which may end up
; being interrupted by the user or a browser timing out. PHP's default behavior
; is to disable this feature.
; http://php.net/ignore-user-abort
;ignore_user_abort = On

; Determines the size of the realpath cache to be used by PHP. This value should
; be increased on systems where PHP opens many files to reflect the quantity of
; the file operations performed.
; http://php.net/realpath-cache-size
;realpath_cache_size = 16k

; Duration of time, in seconds for which to cache realpath information for a given
; file or directory. For systems with rarely changing files, consider increasing this
; value.
; http://php.net/realpath-cache-ttl
;realpath_cache_ttl = 120

; Enables or disables the circular reference collector.
; http://php.net/zend.enable-gc
zend.enable_gc = On

; If enabled, scripts may be written in encodings that are incompatible with
; the scanner.  CP936, Big5, CP949 and Shift_JIS are the examples of such
; encodings.  To use this feature, mbstring extension must be enabled.
; Default: Off
;zend.multibyte = Off

; Allows to set the default encoding for the scripts.  This value will be used
; unless \"declare(encoding=...)\" directive appears at the top of the script.
; Only affects if zend.multibyte is set.
; Default: \"\"
;zend.script_encoding =

;;;;;;;;;;;;;;;;;
; Miscellaneous ;
;;;;;;;;;;;;;;;;;

; Decides whether PHP may expose the fact that it is installed on the server
; (e.g. by adding its signature to the Web server header).  It is no security
; threat in any way, but it makes it possible to determine whether you use PHP
; on your server or not.
; http://php.net/expose-php
expose_php = On

;;;;;;;;;;;;;;;;;;;
; Resource Limits ;
;;;;;;;;;;;;;;;;;;;

; Maximum execution time of each script, in seconds
; http://php.net/max-execution-time
; Note: This directive is hardcoded to 0 for the CLI SAPI
max_execution_time = 30

; Maximum amount of time each script may spend parsing request data. It's a good
; idea to limit this time on productions servers in order to eliminate unexpectedly
; long running scripts.
; Note: This directive is hardcoded to -1 for the CLI SAPI
; Default Value: -1 (Unlimited)
; Development Value: 60 (60 seconds)
; Production Value: 60 (60 seconds)
; http://php.net/max-input-time
max_input_time = 60

; Maximum input variable nesting level
; http://php.net/max-input-nesting-level
;max_input_nesting_level = 64

; How many GET/POST/COOKIE input variables may be accepted
; max_input_vars = 1000

; Maximum amount of memory a script may consume (128MB)
; http://php.net/memory-limit
memory_limit = 128M

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; Error handling and logging ;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

; This directive informs PHP of which errors, warnings and notices you would like
; it to take action for. The recommended way of setting values for this
; directive is through the use of the error level constants and bitwise
; operators. The error level constants are below here for convenience as well as
; some common settings and their meanings.
; By default, PHP is set to take action on all errors, notices and warnings EXCEPT
; those related to E_NOTICE and E_STRICT, which together cover best practices and
; recommended coding standards in PHP. For performance reasons, this is the
; recommend error reporting setting. Your production server shouldn't be wasting
; resources complaining about best practices and coding standards. That's what
; development servers and development settings are for.
; Note: The php.ini-development file has this setting as E_ALL. This
; means it pretty much reports everything which is exactly what you want during
; development and early testing.
;
; Error Level Constants:
; E_ALL             - All errors and warnings (includes E_STRICT as of PHP 5.4.0)
; E_ERROR           - fatal run-time errors
; E_RECOVERABLE_ERROR  - almost fatal run-time errors
; E_WARNING         - run-time warnings (non-fatal errors)
; E_PARSE           - compile-time parse errors
; E_NOTICE          - run-time notices (these are warnings which often result
;                     from a bug in your code, but it's possible that it was
;                     intentional (e.g., using an uninitialized variable and
;                     relying on the fact it is automatically initialized to an
;                     empty string)
; E_STRICT          - run-time notices, enable to have PHP suggest changes
;                     to your code which will ensure the best interoperability
;                     and forward compatibility of your code
; E_CORE_ERROR      - fatal errors that occur during PHP's initial startup
; E_CORE_WARNING    - warnings (non-fatal errors) that occur during PHP's
;                     initial startup
; E_COMPILE_ERROR   - fatal compile-time errors
; E_COMPILE_WARNING - compile-time warnings (non-fatal errors)
; E_USER_ERROR      - user-generated error message
; E_USER_WARNING    - user-generated warning message
; E_USER_NOTICE     - user-generated notice message
; E_DEPRECATED      - warn about code that will not work in future versions
;                     of PHP
; E_USER_DEPRECATED - user-generated deprecation warnings
;
; Common Values:
;   E_ALL (Show all errors, warnings and notices including coding standards.)
;   E_ALL & ~E_NOTICE  (Show all errors, except for notices)
;   E_ALL & ~E_NOTICE & ~E_STRICT  (Show all errors, except for notices and coding standards warnings.)
;   E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR  (Show only errors)
; Default Value: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
; Development Value: E_ALL
; Production Value: E_ALL & ~E_DEPRECATED & ~E_STRICT
; http://php.net/error-reporting
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; This directive controls whether or not and where PHP will output errors,
; notices and warnings too. Error output is very useful during development, but
; it could be very dangerous in production environments. Depending on the code
; which is triggering the error, sensitive information could potentially leak
; out of your application such as database usernames and passwords or worse.
; For production environments, we recommend logging errors rather than
; sending them to STDOUT.
; Possible Values:
;   Off = Do not display any errors
;   stderr = Display errors to STDERR (affects only CGI/CLI binaries!)
;   On or stdout = Display errors to STDOUT
; Default Value: On
; Development Value: On
; Production Value: Off
; http://php.net/display-errors
display_errors = Off

; The display of errors which occur during PHP's startup sequence are handled
; separately from display_errors. PHP's default behavior is to suppress those
; errors from clients. Turning the display of startup errors on can be useful in
; debugging configuration problems. We strongly recommend you
; set this to 'off' for production servers.
; Default Value: Off
; Development Value: On
; Production Value: Off
; http://php.net/display-startup-errors
display_startup_errors = Off

; Besides displaying errors, PHP can also log errors to locations such as a
; server-specific log, STDERR, or a location specified by the error_log
; directive found below. While errors should not be displayed on productions
; servers they should still be monitored and logging is a great way to do that.
; Default Value: Off
; Development Value: On
; Production Value: On
; http://php.net/log-errors
log_errors = On

; Set maximum length of log_errors. In error_log information about the source is
; added. The default is 1024 and 0 allows to not apply any maximum length at all.
; http://php.net/log-errors-max-len
log_errors_max_len = 1024

; Do not log repeated messages. Repeated errors must occur in same file on same
; line unless ignore_repeated_source is set true.
; http://php.net/ignore-repeated-errors
ignore_repeated_errors = Off

; Ignore source of message when ignoring repeated messages. When this setting
; is On you will not log errors with repeated messages from different files or
; source lines.
; http://php.net/ignore-repeated-source
ignore_repeated_source = Off

; If this parameter is set to Off, then memory leaks will not be shown (on
; stdout or in the log). This has only effect in a debug compile, and if
; error reporting includes E_WARNING in the allowed list
; http://php.net/report-memleaks
report_memleaks = On

; This setting is on by default.
;report_zend_debug = 0

; Store the last error/warning message in \$php_errormsg (boolean). Setting this value
; to On can assist in debugging and is appropriate for development servers. It should
; however be disabled on production servers.
; Default Value: Off
; Development Value: On
; Production Value: Off
; http://php.net/track-errors
track_errors = Off

; Turn off normal error reporting and emit XML-RPC error XML
; http://php.net/xmlrpc-errors
;xmlrpc_errors = 0

; An XML-RPC faultCode
;xmlrpc_error_number = 0

; When PHP displays or logs an error, it has the capability of formatting the
; error message as HTML for easier reading. This directive controls whether
; the error message is formatted as HTML or not.
; Note: This directive is hardcoded to Off for the CLI SAPI
; Default Value: On
; Development Value: On
; Production value: On
; http://php.net/html-errors
html_errors = On

; If html_errors is set to On *and* docref_root is not empty, then PHP
; produces clickable error messages that direct to a page describing the error
; or function causing the error in detail.
; You can download a copy of the PHP manual from http://php.net/docs
; and change docref_root to the base URL of your local copy including the
; leading '/'. You must also specify the file extension being used including
; the dot. PHP's default behavior is to leave these settings empty, in which
; case no links to documentation are generated.
; Note: Never use this feature for production boxes.
; http://php.net/docref-root
; Examples
;docref_root = \"/phpmanual/\"

; http://php.net/docref-ext
;docref_ext = .html

; String to output before an error message. PHP's default behavior is to leave
; this setting blank.
; http://php.net/error-prepend-string
; Example:
;error_prepend_string = \"<span style='color: #ff0000'>\"

; String to output after an error message. PHP's default behavior is to leave
; this setting blank.
; http://php.net/error-append-string
; Example:
;error_append_string = \"</span>\"

; Log errors to specified file. PHP's default behavior is to leave this value
; empty.
; http://php.net/error-log
; Example:
;error_log = php_errors.log
; Log errors to syslog (Event Log on Windows).
;error_log = syslog

;windows.show_crt_warning
; Default value: 0
; Development value: 0
; Production value: 0

;;;;;;;;;;;;;;;;;
; Data Handling ;
;;;;;;;;;;;;;;;;;

; The separator used in PHP generated URLs to separate arguments.
; PHP's default setting is \"&\".
; http://php.net/arg-separator.output
; Example:
;arg_separator.output = \"&amp;\"

; List of separator(s) used by PHP to parse input URLs into variables.
; PHP's default setting is \"&\".
; NOTE: Every character in this directive is considered as separator!
; http://php.net/arg-separator.input
; Example:
;arg_separator.input = \";&\"

; This directive determines which super global arrays are registered when PHP
; starts up. G,P,C,E & S are abbreviations for the following respective super
; globals: GET, POST, COOKIE, ENV and SERVER. There is a performance penalty
; paid for the registration of these arrays and because ENV is not as commonly
; used as the others, ENV is not recommended on productions servers. You
; can still get access to the environment variables through getenv() should you
; need to.
; Default Value: \"EGPCS\"
; Development Value: \"GPCS\"
; Production Value: \"GPCS\";
; http://php.net/variables-order
variables_order = \"GPCS\"

; This directive determines which super global data (G,P & C) should be
; registered into the super global array REQUEST. If so, it also determines
; the order in which that data is registered. The values for this directive
; are specified in the same manner as the variables_order directive,
; EXCEPT one. Leaving this value empty will cause PHP to use the value set
; in the variables_order directive. It does not mean it will leave the super
; globals array REQUEST empty.
; Default Value: None
; Development Value: \"GP\"
; Production Value: \"GP\"
; http://php.net/request-order
request_order = \"GP\"

; This directive determines whether PHP registers \$argv & \$argc each time it
; runs. \$argv contains an array of all the arguments passed to PHP when a script
; is invoked. \$argc contains an integer representing the number of arguments
; that were passed when the script was invoked. These arrays are extremely
; useful when running scripts from the command line. When this directive is
; enabled, registering these variables consumes CPU cycles and memory each time
; a script is executed. For performance reasons, this feature should be disabled
; on production servers.
; Note: This directive is hardcoded to On for the CLI SAPI
; Default Value: On
; Development Value: Off
; Production Value: Off
; http://php.net/register-argc-argv
register_argc_argv = Off

; When enabled, the ENV, REQUEST and SERVER variables are created when they're
; first used (Just In Time) instead of when the script starts. If these
; variables are not used within a script, having this directive on will result
; in a performance gain. The PHP directive register_argc_argv must be disabled
; for this directive to have any affect.
; http://php.net/auto-globals-jit
auto_globals_jit = On

; Whether PHP will read the POST data.
; This option is enabled by default.
; Most likely, you won't want to disable this option globally. It causes \$_POST
; and \$_FILES to always be empty; the only way you will be able to read the
; POST data will be through the php://input stream wrapper. This can be useful
; to proxy requests or to process the POST data in a memory efficient fashion.
; http://php.net/enable-post-data-reading
;enable_post_data_reading = Off

; Maximum size of POST data that PHP will accept.
; Its value may be 0 to disable the limit. It is ignored if POST data reading
; is disabled through enable_post_data_reading.
; http://php.net/post-max-size
post_max_size = 8M

; Automatically add files before PHP document.
; http://php.net/auto-prepend-file
auto_prepend_file =

; Automatically add files after PHP document.
; http://php.net/auto-append-file
auto_append_file =

; By default, PHP will output a character encoding using
; the Content-type: header.  To disable sending of the charset, simply
; set it to be empty.
;
; PHP's built-in default is text/html
; http://php.net/default-mimetype
default_mimetype = \"text/html\"

; PHP's default character set is set to UTF-8.
; http://php.net/default-charset
default_charset = \"UTF-8\"

; PHP internal character encoding is set to empty.
; If empty, default_charset is used.
; http://php.net/internal-encoding
;internal_encoding =

; PHP input character encoding is set to empty.
; If empty, default_charset is used.
; http://php.net/input-encoding
;input_encoding =

; PHP output character encoding is set to empty.
; If empty, default_charset is used.
; mbstring or iconv output handler is used.
; See also output_buffer.
; http://php.net/output-encoding
;output_encoding =

;;;;;;;;;;;;;;;;;;;;;;;;;
; Paths and Directories ;
;;;;;;;;;;;;;;;;;;;;;;;;;

; UNIX: \"/path1:/path2\"
;include_path = \".:/php/includes\"
;
; Windows: \"\path1;\path2\"
;include_path = \".;c:\php\includes\"
;
; PHP's default setting for include_path is \".;/path/to/php/pear\"
; http://php.net/include-path

; The root of the PHP pages, used only if nonempty.
; if PHP was not compiled with FORCE_REDIRECT, you SHOULD set doc_root
; if you are running php as a CGI under any web server (other than IIS)
; see documentation for security issues.  The alternate is to use the
; cgi.force_redirect configuration below
; http://php.net/doc-root
doc_root =

; The directory under which PHP opens the script using /~username used only
; if nonempty.
; http://php.net/user-dir
user_dir =

; Directory in which the loadable extensions (modules) reside.
; http://php.net/extension-dir
; extension_dir = \"./\"
; On windows:
; extension_dir = \"ext\"

; Directory where the temporary files should be placed.
; Defaults to the system default (see sys_get_temp_dir)
; sys_temp_dir = \"/tmp\"

; Whether or not to enable the dl() function.  The dl() function does NOT work
; properly in multithreaded servers, such as IIS or Zeus, and is automatically
; disabled on them.
; http://php.net/enable-dl
enable_dl = Off

; cgi.force_redirect is necessary to provide security running PHP as a CGI under
; most web servers.  Left undefined, PHP turns this on by default.  You can
; turn it off here AT YOUR OWN RISK
; **You CAN safely turn this off for IIS, in fact, you MUST.**
; http://php.net/cgi.force-redirect
;cgi.force_redirect = 1

; if cgi.nph is enabled it will force cgi to always sent Status: 200 with
; every request. PHP's default behavior is to disable this feature.
;cgi.nph = 1

; if cgi.force_redirect is turned on, and you are not running under Apache or Netscape
; (iPlanet) web servers, you MAY need to set an environment variable name that PHP
; will look for to know it is OK to continue execution.  Setting this variable MAY
; cause security issues, KNOW WHAT YOU ARE DOING FIRST.
; http://php.net/cgi.redirect-status-env
;cgi.redirect_status_env =

; cgi.fix_pathinfo provides *real* PATH_INFO/PATH_TRANSLATED support for CGI.  PHP's
; previous behaviour was to set PATH_TRANSLATED to SCRIPT_FILENAME, and to not grok
; what PATH_INFO is.  For more information on PATH_INFO, see the cgi specs.  Setting
; this to 1 will cause PHP CGI to fix its paths to conform to the spec.  A setting
; of zero causes PHP to behave as before.  Default is 1.  You should fix your scripts
; to use SCRIPT_FILENAME rather than PATH_TRANSLATED.
; http://php.net/cgi.fix-pathinfo
cgi.fix_pathinfo=1

; FastCGI under IIS (on WINNT based OS) supports the ability to impersonate
; security tokens of the calling client.  This allows IIS to define the
; security context that the request runs under.  mod_fastcgi under Apache
; does not currently support this feature (03/17/2002)
; Set to 1 if running under IIS.  Default is zero.
; http://php.net/fastcgi.impersonate
;fastcgi.impersonate = 1

; Disable logging through FastCGI connection. PHP's default behavior is to enable
; this feature.
;fastcgi.logging = 0

; cgi.rfc2616_headers configuration option tells PHP what type of headers to
; use when sending HTTP response code. If set to 0, PHP sends Status: header that
; is supported by Apache. When this option is set to 1, PHP will send
; RFC2616 compliant header.
; Default is zero.
; http://php.net/cgi.rfc2616-headers
;cgi.rfc2616_headers = 0

;;;;;;;;;;;;;;;;
; File Uploads ;
;;;;;;;;;;;;;;;;

; Whether to allow HTTP file uploads.
; http://php.net/file-uploads
file_uploads = On

; Temporary directory for HTTP uploaded files (will use system default if not
; specified).
; http://php.net/upload-tmp-dir
;upload_tmp_dir =

; Maximum allowed size for uploaded files.
; http://php.net/upload-max-filesize
upload_max_filesize = 2M

; Maximum number of files that can be uploaded via a single request
max_file_uploads = 20

;;;;;;;;;;;;;;;;;;
; Fopen wrappers ;
;;;;;;;;;;;;;;;;;;

; Whether to allow the treatment of URLs (like http:// or ftp://) as files.
; http://php.net/allow-url-fopen
allow_url_fopen = On

; Whether to allow include/require to open URLs (like http:// or ftp://) as files.
; http://php.net/allow-url-include
allow_url_include = Off

; Define the anonymous ftp password (your email address). PHP's default setting
; for this is empty.
; http://php.net/from
;from=\"john@doe.com\"

; Define the User-Agent string. PHP's default setting for this is empty.
; http://php.net/user-agent
;user_agent=\"PHP\"

; Default timeout for socket based streams (seconds)
; http://php.net/default-socket-timeout
default_socket_timeout = 60

; If your scripts have to deal with files from Macintosh systems,
; or you are running on a Mac and need to deal with files from
; unix or win32 systems, setting this flag will cause PHP to
; automatically detect the EOL character in those files so that
; fgets() and file() will work regardless of the source of the file.
; http://php.net/auto-detect-line-endings
;auto_detect_line_endings = Off

;;;;;;;;;;;;;;;;;;;;;;
; Dynamic Extensions ;
;;;;;;;;;;;;;;;;;;;;;;

; If you wish to have an extension loaded automatically, use the following
; syntax:
;
;   extension=modulename.extension
;
; For example, on Windows:
;
;   extension=msql.dll
;
; ... or under UNIX:
;
;   extension=msql.so
;
; ... or with a path:
;
;   extension=/path/to/extension/msql.so
;
; If you only provide the name of the extension, PHP will look for it in its
; default extension directory.

;;;;
; Note: packaged extension modules are now loaded via the .ini files
; found in the directory /etc/php.d; these are loaded by default.
;;;;

;;;;;;;;;;;;;;;;;;;
; Module Settings ;
;;;;;;;;;;;;;;;;;;;

[CLI Server]
; Whether the CLI web server uses ANSI color coding in its terminal output.
cli_server.color = On

[Date]
; Defines the default timezone used by the date functions
; http://php.net/date.timezone
;date.timezone =

; http://php.net/date.default-latitude
;date.default_latitude = 31.7667

; http://php.net/date.default-longitude
;date.default_longitude = 35.2333

; http://php.net/date.sunrise-zenith
;date.sunrise_zenith = 90.583333

; http://php.net/date.sunset-zenith
;date.sunset_zenith = 90.583333

[filter]
; http://php.net/filter.default
;filter.default = unsafe_raw

; http://php.net/filter.default-flags
;filter.default_flags =

[iconv]
; Use of this INI entry is deprecated, use global input_encoding instead.
; If empty, default_charset or input_encoding or iconv.input_encoding is used.
; The precedence is: default_charset < intput_encoding < iconv.input_encoding
;iconv.input_encoding =

; Use of this INI entry is deprecated, use global internal_encoding instead.
; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.
; The precedence is: default_charset < internal_encoding < iconv.internal_encoding
;iconv.internal_encoding =

; Use of this INI entry is deprecated, use global output_encoding instead.
; If empty, default_charset or output_encoding or iconv.output_encoding is used.
; The precedence is: default_charset < output_encoding < iconv.output_encoding
; To use an output encoding conversion, iconv's output handler must be set
; otherwise output encoding conversion cannot be performed.
;iconv.output_encoding =

[intl]
;intl.default_locale =
; This directive allows you to produce PHP errors when some error
; happens within intl functions. The value is the level of the error produced.
; Default is 0, which does not produce any errors.
;intl.error_level = E_WARNING

[sqlite]
; http://php.net/sqlite.assoc-case
;sqlite.assoc_case = 0

[sqlite3]
;sqlite3.extension_dir =

[Pcre]
;PCRE library backtracking limit.
; http://php.net/pcre.backtrack-limit
;pcre.backtrack_limit=100000

;PCRE library recursion limit.
;Please note that if you set this value to a high number you may consume all
;the available process stack and eventually crash PHP (due to reaching the
;stack size limit imposed by the Operating System).
; http://php.net/pcre.recursion-limit
;pcre.recursion_limit=100000

[Pdo]
; Whether to pool ODBC connections. Can be one of \"strict\", \"relaxed\" or \"off\"
; http://php.net/pdo-odbc.connection-pooling
;pdo_odbc.connection_pooling=strict

;pdo_odbc.db2_instance_name

[Pdo_mysql]
; If mysqlnd is used: Number of cache slots for the internal result set cache
; http://php.net/pdo_mysql.cache_size
pdo_mysql.cache_size = 2000

; Default socket name for local MySQL connects.  If empty, uses the built-in
; MySQL defaults.
; http://php.net/pdo_mysql.default-socket
pdo_mysql.default_socket=

[Phar]
; http://php.net/phar.readonly
;phar.readonly = On

; http://php.net/phar.require-hash
;phar.require_hash = On

;phar.cache_list =

[mail function]
; For Unix only.  You may supply arguments as well (default: \"sendmail -t -i\").
; http://php.net/sendmail-path
sendmail_path = /usr/sbin/sendmail -t -i

; Force the addition of the specified parameters to be passed as extra parameters
; to the sendmail binary. These parameters will always replace the value of
; the 5th parameter to mail().
;mail.force_extra_parameters =

; Add X-PHP-Originating-Script: that will include uid of the script followed by the filename
mail.add_x_header = On

; The path to a log file that will log all mail() calls. Log entries include
; the full path of the script, line number, To address and headers.
;mail.log =
; Log mail to syslog (Event Log on Windows).
;mail.log = syslog

[SQL]
; http://php.net/sql.safe-mode
sql.safe_mode = Off

[ODBC]
; http://php.net/odbc.default-db
;odbc.default_db    =  Not yet implemented

; http://php.net/odbc.default-user
;odbc.default_user  =  Not yet implemented

; http://php.net/odbc.default-pw
;odbc.default_pw    =  Not yet implemented

; Controls the ODBC cursor model.
; Default: SQL_CURSOR_STATIC (default).
;odbc.default_cursortype

; Allow or prevent persistent links.
; http://php.net/odbc.allow-persistent
odbc.allow_persistent = On

; Check that a connection is still valid before reuse.
; http://php.net/odbc.check-persistent
odbc.check_persistent = On

; Maximum number of persistent links.  -1 means no limit.
; http://php.net/odbc.max-persistent
odbc.max_persistent = -1

; Maximum number of links (persistent + non-persistent).  -1 means no limit.
; http://php.net/odbc.max-links
odbc.max_links = -1

; Handling of LONG fields.  Returns number of bytes to variables.  0 means
; passthru.
; http://php.net/odbc.defaultlrl
odbc.defaultlrl = 4096

; Handling of binary data.  0 means passthru, 1 return as is, 2 convert to char.
; See the documentation on odbc_binmode and odbc_longreadlen for an explanation
; of odbc.defaultlrl and odbc.defaultbinmode
; http://php.net/odbc.defaultbinmode
odbc.defaultbinmode = 1

;birdstep.max_links = -1

[Interbase]
; Allow or prevent persistent links.
ibase.allow_persistent = 1

; Maximum number of persistent links.  -1 means no limit.
ibase.max_persistent = -1

; Maximum number of links (persistent + non-persistent).  -1 means no limit.
ibase.max_links = -1

; Default database name for ibase_connect().
;ibase.default_db =

; Default username for ibase_connect().
;ibase.default_user =

; Default password for ibase_connect().
;ibase.default_password =

; Default charset for ibase_connect().
;ibase.default_charset =

; Default timestamp format.
ibase.timestampformat = \"%Y-%m-%d %H:%M:%S\"

; Default date format.
ibase.dateformat = \"%Y-%m-%d\"

; Default time format.
ibase.timeformat = \"%H:%M:%S\"

[MySQLi]

; Maximum number of persistent links.  -1 means no limit.
; http://php.net/mysqli.max-persistent
mysqli.max_persistent = -1

; Allow accessing, from PHP's perspective, local files with LOAD DATA statements
; http://php.net/mysqli.allow_local_infile
;mysqli.allow_local_infile = On

; Allow or prevent persistent links.
; http://php.net/mysqli.allow-persistent
mysqli.allow_persistent = On

; Maximum number of links.  -1 means no limit.
; http://php.net/mysqli.max-links
mysqli.max_links = -1

; If mysqlnd is used: Number of cache slots for the internal result set cache
; http://php.net/mysqli.cache_size
mysqli.cache_size = 2000

; Default port number for mysqli_connect().  If unset, mysqli_connect() will use
; the \$MYSQL_TCP_PORT or the mysql-tcp entry in /etc/services or the
; compile-time value defined MYSQL_PORT (in that order).  Win32 will only look
; at MYSQL_PORT.
; http://php.net/mysqli.default-port
mysqli.default_port = 3306

; Default socket name for local MySQL connects.  If empty, uses the built-in
; MySQL defaults.
; http://php.net/mysqli.default-socket
mysqli.default_socket =

; Default host for mysql_connect() (doesn't apply in safe mode).
; http://php.net/mysqli.default-host
mysqli.default_host =

; Default user for mysql_connect() (doesn't apply in safe mode).
; http://php.net/mysqli.default-user
mysqli.default_user =

; Default password for mysqli_connect() (doesn't apply in safe mode).
; Note that this is generally a *bad* idea to store passwords in this file.
; *Any* user with PHP access can run 'echo get_cfg_var(\"mysqli.default_pw\")
; and reveal this password!  And of course, any users with read access to this
; file will be able to reveal the password as well.
; http://php.net/mysqli.default-pw
mysqli.default_pw =

; Allow or prevent reconnect
mysqli.reconnect = Off

[mysqlnd]
; Enable / Disable collection of general statistics by mysqlnd which can be
; used to tune and monitor MySQL operations.
; http://php.net/mysqlnd.collect_statistics
mysqlnd.collect_statistics = On

; Enable / Disable collection of memory usage statistics by mysqlnd which can be
; used to tune and monitor MySQL operations.
; http://php.net/mysqlnd.collect_memory_statistics
mysqlnd.collect_memory_statistics = Off

; Size of a pre-allocated buffer used when sending commands to MySQL in bytes.
; http://php.net/mysqlnd.net_cmd_buffer_size
;mysqlnd.net_cmd_buffer_size = 2048

; Size of a pre-allocated buffer used for reading data sent by the server in
; bytes.
; http://php.net/mysqlnd.net_read_buffer_size
;mysqlnd.net_read_buffer_size = 32768

[OCI8]

; Connection: Enables privileged connections using external
; credentials (OCI_SYSOPER, OCI_SYSDBA)
; http://php.net/oci8.privileged-connect
;oci8.privileged_connect = Off

; Connection: The maximum number of persistent OCI8 connections per
; process. Using -1 means no limit.
; http://php.net/oci8.max-persistent
;oci8.max_persistent = -1

; Connection: The maximum number of seconds a process is allowed to
; maintain an idle persistent connection. Using -1 means idle
; persistent connections will be maintained forever.
; http://php.net/oci8.persistent-timeout
;oci8.persistent_timeout = -1

; Connection: The number of seconds that must pass before issuing a
; ping during oci_pconnect() to check the connection validity. When
; set to 0, each oci_pconnect() will cause a ping. Using -1 disables
; pings completely.
; http://php.net/oci8.ping-interval
;oci8.ping_interval = 60

; Connection: Set this to a user chosen connection class to be used
; for all pooled server requests with Oracle 11g Database Resident
; Connection Pooling (DRCP).  To use DRCP, this value should be set to
; the same string for all web servers running the same application,
; the database pool must be configured, and the connection string must
; specify to use a pooled server.
;oci8.connection_class =

; High Availability: Using On lets PHP receive Fast Application
; Notification (FAN) events generated when a database node fails. The
; database must also be configured to post FAN events.
;oci8.events = Off

; Tuning: This option enables statement caching, and specifies how
; many statements to cache. Using 0 disables statement caching.
; http://php.net/oci8.statement-cache-size
;oci8.statement_cache_size = 20

; Tuning: Enables statement prefetching and sets the default number of
; rows that will be fetched automatically after statement execution.
; http://php.net/oci8.default-prefetch
;oci8.default_prefetch = 100

; Compatibility. Using On means oci_close() will not close
; oci_connect() and oci_new_connect() connections.
; http://php.net/oci8.old-oci-close-semantics
;oci8.old_oci_close_semantics = Off

[PostgreSQL]
; Allow or prevent persistent links.
; http://php.net/pgsql.allow-persistent
pgsql.allow_persistent = On

; Detect broken persistent links always with pg_pconnect().
; Auto reset feature requires a little overheads.
; http://php.net/pgsql.auto-reset-persistent
pgsql.auto_reset_persistent = Off

; Maximum number of persistent links.  -1 means no limit.
; http://php.net/pgsql.max-persistent
pgsql.max_persistent = -1

; Maximum number of links (persistent+non persistent).  -1 means no limit.
; http://php.net/pgsql.max-links
pgsql.max_links = -1

; Ignore PostgreSQL backends Notice message or not.
; Notice message logging require a little overheads.
; http://php.net/pgsql.ignore-notice
pgsql.ignore_notice = 0

; Log PostgreSQL backends Notice message or not.
; Unless pgsql.ignore_notice=0, module cannot log notice message.
; http://php.net/pgsql.log-notice
pgsql.log_notice = 0

[bcmath]
; Number of decimal digits for all bcmath functions.
; http://php.net/bcmath.scale
bcmath.scale = 0

[browscap]
; http://php.net/browscap
;browscap = extra/browscap.ini

[Session]
; Handler used to store/retrieve data.
; http://php.net/session.save-handler
session.save_handler = files

; Argument passed to save_handler.  In the case of files, this is the path
; where data files are stored. Note: Windows users have to change this
; variable in order to use PHP's session functions.
;
; The path can be defined as:
;
;     session.save_path = \"N;/path\"
;
; where N is an integer.  Instead of storing all the session files in
; /path, what this will do is use subdirectories N-levels deep, and
; store the session data in those directories.  This is useful if
; your OS has problems with many files in one directory, and is
; a more efficient layout for servers that handle many sessions.
;
; NOTE 1: PHP will not create this directory structure automatically.
;         You can use the script in the ext/session dir for that purpose.
; NOTE 2: See the section on garbage collection below if you choose to
;         use subdirectories for session storage
;
; The file storage module creates files using mode 600 by default.
; You can change that by using
;
;     session.save_path = \"N;MODE;/path\"
;
; where MODE is the octal representation of the mode. Note that this
; does not overwrite the process's umask.
; http://php.net/session.save-path

; RPM note : session directory must be owned by process owner
; for mod_php, see /etc/httpd/conf.d/php.conf
; for php-fpm, see /etc/php-fpm.d/*conf
;session.save_path = \"/tmp\"

; Whether to use strict session mode.
; Strict session mode does not accept uninitialized session ID and regenerate
; session ID if browser sends uninitialized session ID. Strict mode protects
; applications from session fixation via session adoption vulnerability. It is
; disabled by default for maximum compatibility, but enabling it is encouraged.
; https://wiki.php.net/rfc/strict_sessions
session.use_strict_mode = 0

; Whether to use cookies.
; http://php.net/session.use-cookies
session.use_cookies = 1

; http://php.net/session.cookie-secure
;session.cookie_secure =

; This option forces PHP to fetch and use a cookie for storing and maintaining
; the session id. We encourage this operation as it's very helpful in combating
; session hijacking when not specifying and managing your own session id. It is
; not the be-all and end-all of session hijacking defense, but it's a good start.
; http://php.net/session.use-only-cookies
session.use_only_cookies = 1

; Name of the session (used as cookie name).
; http://php.net/session.name
session.name = PHPSESSID

; Initialize session on request startup.
; http://php.net/session.auto-start
session.auto_start = 0

; Lifetime in seconds of cookie or, if 0, until browser is restarted.
; http://php.net/session.cookie-lifetime
session.cookie_lifetime = 0

; The path for which the cookie is valid.
; http://php.net/session.cookie-path
session.cookie_path = /

; The domain for which the cookie is valid.
; http://php.net/session.cookie-domain
session.cookie_domain =

; Whether or not to add the httpOnly flag to the cookie, which makes it inaccessible to browser scripting languages such as JavaScript.
; http://php.net/session.cookie-httponly
session.cookie_httponly =

; Handler used to serialize data.  php is the standard serializer of PHP.
; http://php.net/session.serialize-handler
session.serialize_handler = php

; Defines the probability that the 'garbage collection' process is started
; on every session initialization. The probability is calculated by using
; gc_probability/gc_divisor. Where session.gc_probability is the numerator
; and gc_divisor is the denominator in the equation. Setting this value to 1
; when the session.gc_divisor value is 100 will give you approximately a 1% chance
; the gc will run on any give request.
; Default Value: 1
; Development Value: 1
; Production Value: 1
; http://php.net/session.gc-probability
session.gc_probability = 1

; Defines the probability that the 'garbage collection' process is started on every
; session initialization. The probability is calculated by using the following equation:
; gc_probability/gc_divisor. Where session.gc_probability is the numerator and
; session.gc_divisor is the denominator in the equation. Setting this value to 1
; when the session.gc_divisor value is 100 will give you approximately a 1% chance
; the gc will run on any give request. Increasing this value to 1000 will give you
; a 0.1% chance the gc will run on any give request. For high volume production servers,
; this is a more efficient approach.
; Default Value: 100
; Development Value: 1000
; Production Value: 1000
; http://php.net/session.gc-divisor
session.gc_divisor = 1000

; After this number of seconds, stored data will be seen as 'garbage' and
; cleaned up by the garbage collection process.
; http://php.net/session.gc-maxlifetime
session.gc_maxlifetime = 1440

; NOTE: If you are using the subdirectory option for storing session files
;       (see session.save_path above), then garbage collection does *not*
;       happen automatically.  You will need to do your own garbage
;       collection through a shell script, cron entry, or some other method.
;       For example, the following script would is the equivalent of
;       setting session.gc_maxlifetime to 1440 (1440 seconds = 24 minutes):
;          find /path/to/sessions -cmin +24 -type f | xargs rm

; Check HTTP Referer to invalidate externally stored URLs containing ids.
; HTTP_REFERER has to contain this substring for the session to be
; considered as valid.
; http://php.net/session.referer-check
session.referer_check =

; How many bytes to read from the file.
; http://php.net/session.entropy-length
;session.entropy_length = 32

; Specified here to create the session id.
; http://php.net/session.entropy-file
; Defaults to /dev/urandom
; On systems that don't have /dev/urandom but do have /dev/arandom, this will default to /dev/arandom
; If neither are found at compile time, the default is no entropy file.
; On windows, setting the entropy_length setting will activate the
; Windows random source (using the CryptoAPI)
;session.entropy_file = /dev/urandom

; Set to {nocache,private,public,} to determine HTTP caching aspects
; or leave this empty to avoid sending anti-caching headers.
; http://php.net/session.cache-limiter
session.cache_limiter = nocache

; Document expires after n minutes.
; http://php.net/session.cache-expire
session.cache_expire = 180

; trans sid support is disabled by default.
; Use of trans sid may risk your users' security.
; Use this option with caution.
; - User may send URL contains active session ID
;   to other person via. email/irc/etc.
; - URL that contains active session ID may be stored
;   in publicly accessible computer.
; - User may access your site with the same session ID
;   always using URL stored in browser's history or bookmarks.
; http://php.net/session.use-trans-sid
session.use_trans_sid = 0

; Select a hash function for use in generating session ids.
; Possible Values
;   0  (MD5 128 bits)
;   1  (SHA-1 160 bits)
; This option may also be set to the name of any hash function supported by
; the hash extension. A list of available hashes is returned by the hash_algos()
; function.
; http://php.net/session.hash-function
session.hash_function = 0

; Define how many bits are stored in each character when converting
; the binary hash data to something readable.
; Possible values:
;   4  (4 bits: 0-9, a-f)
;   5  (5 bits: 0-9, a-v)
;   6  (6 bits: 0-9, a-z, A-Z, \"-\", \",\")
; Default Value: 4
; Development Value: 5
; Production Value: 5
; http://php.net/session.hash-bits-per-character
session.hash_bits_per_character = 5

; The URL rewriter will look for URLs in a defined set of HTML tags.
; form/fieldset are special; if you include them here, the rewriter will
; add a hidden <input> field with the info which is otherwise appended
; to URLs.  If you want XHTML conformity, remove the form entry.
; Note that all valid entries require a \"=\", even if no value follows.
; Default Value: \"a=href,area=href,frame=src,form=,fieldset=\"
; Development Value: \"a=href,area=href,frame=src,input=src,form=fakeentry\"
; Production Value: \"a=href,area=href,frame=src,input=src,form=fakeentry\"
; http://php.net/url-rewriter.tags
url_rewriter.tags = \"a=href,area=href,frame=src,input=src,form=fakeentry\"

; Enable upload progress tracking in \$_SESSION
; Default Value: On
; Development Value: On
; Production Value: On
; http://php.net/session.upload-progress.enabled
;session.upload_progress.enabled = On

; Cleanup the progress information as soon as all POST data has been read
; (i.e. upload completed).
; Default Value: On
; Development Value: On
; Production Value: On
; http://php.net/session.upload-progress.cleanup
;session.upload_progress.cleanup = On

; A prefix used for the upload progress key in \$_SESSION
; Default Value: \"upload_progress_\"
; Development Value: \"upload_progress_\"
; Production Value: \"upload_progress_\"
; http://php.net/session.upload-progress.prefix
;session.upload_progress.prefix = \"upload_progress_\"

; The index name (concatenated with the prefix) in \$_SESSION
; containing the upload progress information
; Default Value: \"PHP_SESSION_UPLOAD_PROGRESS\"
; Development Value: \"PHP_SESSION_UPLOAD_PROGRESS\"
; Production Value: \"PHP_SESSION_UPLOAD_PROGRESS\"
; http://php.net/session.upload-progress.name
;session.upload_progress.name = \"PHP_SESSION_UPLOAD_PROGRESS\"

; How frequently the upload progress should be updated.
; Given either in percentages (per-file), or in bytes
; Default Value: \"1%\"
; Development Value: \"1%\"
; Production Value: \"1%\"
; http://php.net/session.upload-progress.freq
;session.upload_progress.freq =  \"1%\"

; The minimum delay between updates, in seconds
; Default Value: 1
; Development Value: 1
; Production Value: 1
; http://php.net/session.upload-progress.min-freq
;session.upload_progress.min_freq = \"1\"

[Assertion]
; Switch whether to compile assertions at all (to have no overhead at run-time)
; -1: Do not compile at all
;  0: Jump over assertion at run-time
;  1: Execute assertions
; Changing from or to a negative value is only possible in php.ini! (For turning assertions on and off at run-time, see assert.active, when zend.assertions = 1)
; Default Value: 1 
; Development Value: 1 
; Production Value: -1 
; http://php.net/zend.assertions
zend.assertions = -1

; Assert(expr); active by default.
; http://php.net/assert.active
;assert.active = On

; Throw an AssertationException on failed assertions
; http://php.net/assert.exception
;assert.exception = On

; Issue a PHP warning for each failed assertion. (Overridden by assert.exception if active)
; http://php.net/assert.warning
;assert.warning = On

; Don't bail out by default.
; http://php.net/assert.bail
;assert.bail = Off

; User-function to be called if an assertion fails.
; http://php.net/assert.callback
;assert.callback = 0

; Eval the expression with current error_reporting().  Set to true if you want
; error_reporting(0) around the eval().
; http://php.net/assert.quiet-eval
;assert.quiet_eval = 0

[mbstring]
; language for internal character representation.
; This affects mb_send_mail() and mbstring.detect_order.
; http://php.net/mbstring.language
;mbstring.language = Japanese

; Use of this INI entry is deprecated, use global internal_encoding instead.
; internal/script encoding.
; Some encoding cannot work as internal encoding. (e.g. SJIS, BIG5, ISO-2022-*)
; If empty, default_charset or internal_encoding or iconv.internal_encoding is used.
; The precedence is: default_charset < internal_encoding < iconv.internal_encoding
;mbstring.internal_encoding =

; Use of this INI entry is deprecated, use global input_encoding instead.
; http input encoding.
; mbstring.encoding_traslation = On is needed to use this setting.
; If empty, default_charset or input_encoding or mbstring.input is used.
; The precedence is: default_charset < intput_encoding < mbsting.http_input
; http://php.net/mbstring.http-input
;mbstring.http_input =

; Use of this INI entry is deprecated, use global output_encoding instead.
; http output encoding.
; mb_output_handler must be registered as output buffer to function.
; If empty, default_charset or output_encoding or mbstring.http_output is used.
; The precedence is: default_charset < output_encoding < mbstring.http_output
; To use an output encoding conversion, mbstring's output handler must be set
; otherwise output encoding conversion cannot be performed.
; http://php.net/mbstring.http-output
;mbstring.http_output =

; enable automatic encoding translation according to
; mbstring.internal_encoding setting. Input chars are
; converted to internal encoding by setting this to On.
; Note: Do _not_ use automatic encoding translation for
;       portable libs/applications.
; http://php.net/mbstring.encoding-translation
;mbstring.encoding_translation = Off

; automatic encoding detection order.
; \"auto\" detect order is changed according to mbstring.language
; http://php.net/mbstring.detect-order
;mbstring.detect_order = auto

; substitute_character used when character cannot be converted
; one from another
; http://php.net/mbstring.substitute-character
;mbstring.substitute_character = none

; overload(replace) single byte functions by mbstring functions.
; mail(), ereg(), etc are overloaded by mb_send_mail(), mb_ereg(),
; etc. Possible values are 0,1,2,4 or combination of them.
; For example, 7 for overload everything.
; 0: No overload
; 1: Overload mail() function
; 2: Overload str*() functions
; 4: Overload ereg*() functions
; http://php.net/mbstring.func-overload
;mbstring.func_overload = 0

; enable strict encoding detection.
; Default: Off
;mbstring.strict_detection = On

; This directive specifies the regex pattern of content types for which mb_output_handler()
; is activated.
; Default: mbstring.http_output_conv_mimetype=^(text/|application/xhtml\+xml)
;mbstring.http_output_conv_mimetype=

[gd]
; Tell the jpeg decode to ignore warnings and try to create
; a gd image. The warning will then be displayed as notices
; disabled by default
; http://php.net/gd.jpeg-ignore-warning
;gd.jpeg_ignore_warning = 0

[exif]
; Exif UNICODE user comments are handled as UCS-2BE/UCS-2LE and JIS as JIS.
; With mbstring support this will automatically be converted into the encoding
; given by corresponding encode setting. When empty mbstring.internal_encoding
; is used. For the decode settings you can distinguish between motorola and
; intel byte order. A decode setting cannot be empty.
; http://php.net/exif.encode-unicode
;exif.encode_unicode = ISO-8859-15

; http://php.net/exif.decode-unicode-motorola
;exif.decode_unicode_motorola = UCS-2BE

; http://php.net/exif.decode-unicode-intel
;exif.decode_unicode_intel    = UCS-2LE

; http://php.net/exif.encode-jis
;exif.encode_jis =

; http://php.net/exif.decode-jis-motorola
;exif.decode_jis_motorola = JIS

; http://php.net/exif.decode-jis-intel
;exif.decode_jis_intel    = JIS

[Tidy]
; The path to a default tidy configuration file to use when using tidy
; http://php.net/tidy.default-config
;tidy.default_config = /usr/local/lib/php/default.tcfg

; Should tidy clean and repair output automatically?
; WARNING: Do not use this option if you are generating non-html content
; such as dynamic images
; http://php.net/tidy.clean-output
tidy.clean_output = Off

[soap]
; Enables or disables WSDL caching feature.
; http://php.net/soap.wsdl-cache-enabled
soap.wsdl_cache_enabled=1

; Sets the directory name where SOAP extension will put cache files.
; http://php.net/soap.wsdl-cache-dir

; RPM note : cache directory must be owned by process owner
; for mod_php, see /etc/httpd/conf.d/php.conf
; for php-fpm, see /etc/php-fpm.d/*conf
soap.wsdl_cache_dir=\"/tmp\"

; (time to live) Sets the number of second while cached file will be used
; instead of original one.
; http://php.net/soap.wsdl-cache-ttl
soap.wsdl_cache_ttl=86400

; Sets the size of the cache limit. (Max. number of WSDL files to cache)
soap.wsdl_cache_limit = 5

[sysvshm]
; A default size of the shared memory segment
;sysvshm.init_mem = 10000

[ldap]
; Sets the maximum number of open links or -1 for unlimited.
ldap.max_links = -1

[mcrypt]
; For more information about mcrypt settings see http://php.net/mcrypt-module-open

; Directory where to load mcrypt algorithms
; Default: Compiled in into libmcrypt (usually /usr/local/lib/libmcrypt)
;mcrypt.algorithms_dir=

; Directory where to load mcrypt modes
; Default: Compiled in into libmcrypt (usually /usr/local/lib/libmcrypt)
;mcrypt.modes_dir=

[dba]
;dba.default_handler=

[curl]
; A default value for the CURLOPT_CAINFO option. This is required to be an
; absolute path.
;curl.cainfo =

[openssl]
; The location of a Certificate Authority (CA) file on the local filesystem
; to use when verifying the identity of SSL/TLS peers. Most users should
; not specify a value for this directive as PHP will attempt to use the
; OS-managed cert stores in its absence. If specified, this value may still
; be overridden on a per-stream basis via the \"cafile\" SSL stream context
; option.
;openssl.cafile=

; If openssl.cafile is not specified or if the CA file is not found, the
; directory pointed to by openssl.capath is searched for a suitable
; certificate. This value must be a correctly hashed certificate directory.
; Most users should not specify a value for this directive as PHP will
; attempt to use the OS-managed cert stores in its absence. If specified,
; this value may still be overridden on a per-stream basis via the \"capath\"
; SSL stream context option.
;openssl.capath=

; Local Variables:
; tab-width: 4
; End:";
    } >/etc/php.ini

    # Install Lighttpd
    clear;
    echo "==================================";
    echo " Installing Lighttpd...";
    echo "==================================";
    yum -y remove httpd
    yum -y erase httpd nginx
    yum -y install epel-release
    rpm --import /etc/pki/rpm-gpg/RPM-GPG-KEY-EPEL-7
    yum -y update
    yum -y install lighttpd
    yum -y install lighttpd-*
    systemctl enable lighttpd.service
    systemctl start lighttpd.service
    firewall-cmd --permanent --zone=public --add-service=http
    firewall-cmd --permanent --zone=public --add-service=https
    firewall-cmd --reload

    {
        echo "#######################################################################
##
##  FastCGI Module
## ---------------
##
## See http://redmine.lighttpd.net/projects/lighttpd/wiki/Docs_ModFastCGI
##
server.modules += ( \"mod_fastcgi\" )

fastcgi.server += ( \".php\" =>
    ((
        \"host\" => \"127.0.0.1\",
        \"port\" => \"9000\",
        \"broken-scriptfilename\" => \"enable\"
    ))
)";
    } >/etc/lighttpd/conf.d/fastcgi.conf

    {
        echo "#######################################################################
##
##  Modules to load
## -----------------
##
## at least mod_access and mod_accesslog should be loaded
## all other module should only be loaded if really neccesary
##
## - saves some time
## - saves memory
##
## the default module set contains:
##
## \"mod_indexfile\", \"mod_dirlisting\", \"mod_staticfile\"
##
## you dont have to include those modules in your list
##
## Modules, which are pulled in via conf.d/*.conf
##
## NOTE: the order of modules is important.
##
## - mod_accesslog     -> conf.d/access_log.conf
## - mod_compress      -> conf.d/compress.conf
## - mod_status        -> conf.d/status.conf
## - mod_webdav        -> conf.d/webdav.conf
## - mod_cml           -> conf.d/cml.conf
## - mod_evhost        -> conf.d/evhost.conf
## - mod_simple_vhost  -> conf.d/simple_vhost.conf
## - mod_mysql_vhost   -> conf.d/mysql_vhost.conf
## - mod_trigger_b4_dl -> conf.d/trigger_b4_dl.conf
## - mod_userdir       -> conf.d/userdir.conf
## - mod_rrdtool       -> conf.d/rrdtool.conf
## - mod_ssi           -> conf.d/ssi.conf
## - mod_cgi           -> conf.d/cgi.conf
## - mod_scgi          -> conf.d/scgi.conf
## - mod_fastcgi       -> conf.d/fastcgi.conf
## - mod_proxy         -> conf.d/proxy.conf
## - mod_secdownload   -> conf.d/secdownload.conf
## - mod_expire        -> conf.d/expire.conf
##

server.modules = (
  \"mod_access\",
#  \"mod_alias\",
#  \"mod_auth\",
#  \"mod_evasive\",
#  \"mod_redirect\",
  \"mod_rewrite\",
#  \"mod_setenv\",
#  \"mod_usertrack\",
)

##
#######################################################################

#######################################################################
##
##  Config for various Modules
##

##
## mod_ssi
##
#include \"conf.d/ssi.conf\"

##
## mod_status
##
#include \"conf.d/status.conf\"

##
## mod_webdav
##
#include \"conf.d/webdav.conf\"

##
## mod_compress
##
#include \"conf.d/compress.conf\"

##
## mod_userdir
##
#include \"conf.d/userdir.conf\"

##
## mod_magnet
##
#include \"conf.d/magnet.conf\"

##
## mod_cml
##
#include \"conf.d/cml.conf\"

##
## mod_rrdtool
##
#include \"conf.d/rrdtool.conf\"

##
## mod_proxy
##
#include \"conf.d/proxy.conf\"

##
## mod_expire
##
#include \"conf.d/expire.conf\"

##
## mod_secdownload
##
#include \"conf.d/secdownload.conf\"

##
#######################################################################

#######################################################################
##
## CGI modules
##

##
## SCGI (mod_scgi)
##
#include \"conf.d/scgi.conf\"

##
## FastCGI (mod_fastcgi)
##
include \"conf.d/fastcgi.conf\"

##
## plain old CGI (mod_cgi)
##
#include \"conf.d/cgi.conf\"

##
#######################################################################

#######################################################################
##
## VHost Modules
##
##  Only load ONE of them!
## ========================
##

##
## You can use conditionals for vhosts aswell.
## 
## see http://www.lighttpd.net/documentation/configuration.html
##

##
## mod_evhost
##
#include \"conf.d/evhost.conf\"

##
## mod_simple_vhost
##
#include \"conf.d/simple_vhost.conf\"

##
## mod_mysql_vhost
##
#include \"conf.d/mysql_vhost.conf\"

##
#######################################################################";
    } >/etc/lighttpd/modules.conf

    rm -rf /var/www/lighttpd/index.html
    rm -rf /var/www/lighttpd/poweredby.png
    rm -rf /var/www/lighttpd/light_logo.png
    rm -rf /var/www/lighttpd/light_button.png
    rm -rf /var/www/lighttpd/favicon.ico
    {
        echo "
        <html>
        <head>
            <title>Advandz Stack</title>
            <link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\">
        </head>

        <body>
            <div class=\"container\" style=\"padding-top: 50px;\">
                <div class=\"panel panel-default\">
                    <div class=\"panel-heading\">
                        <h3 class=\"panel-title\">Advandz Stack</h3>
                    </div>
                    <div class=\"panel-body\">
                        <h5>It is possible you have reached this page because:</h5>
                        <ul class=\"list-group\">
                            <li class=\"list-group-item\">
                                <span class=\"glyphicon glyphicon-random\" aria-hidden=\"true\"></span> <b>The IP address has changed.</b>
                                <br>
                                <small>The IP address for this domain may have changed recently. Check your DNS settings to verify that the domain is set up correctly.</small>
                            </li>
                            <li class=\"list-group-item\">
                                <span class=\"glyphicon glyphicon-warning-sign\" aria-hidden=\"true\"></span> <b>There has been a server misconfiguration.</b>
                                <br>
                                <small>You must verify that your hosting provider has the correct IP address configured for your Lighttpd settings and DNS records.</small>
                            </li>
                            <li class=\"list-group-item\">
                                <span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span> <b>The site may have moved to a different server.</b>
                                <br>
                                <small>The IP address for this domain may have changed recently. Check your DNS settings to verify that the domain is set up correctly.</small>
                            </li>
                        </ul>
                    </div>
                    <div class=\"panel-footer\">Copyright (c) <?php echo date('Y'); ?> <a href=\"http://advandz.com/\" target=\"_blank\">The Advandz Team</a>.</div>
                </div>
                <center>
                    <img style=\"max-width: 150px; margin-top: 15px; margin-bottom: 35px;\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAABJCAYAAACHMxsoAAAAAXNSR0IArs4c6QAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAACXBIWXMAAAsTAAALEwEAmpwYAAARsGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgICAgICAgICAgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiCiAgICAgICAgICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICAgICAgICAgIHhtbG5zOmV4aWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vZXhpZi8xLjAvIgogICAgICAgICAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICAgICAgICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyI+CiAgICAgICAgIDx4bXA6TW9kaWZ5RGF0ZT4yMDE2LTExLTI3VDExOjQyOjQ2LTA2OjAwPC94bXA6TW9kaWZ5RGF0ZT4KICAgICAgICAgPHhtcDpDcmVhdGVEYXRlPjIwMTYtMTEtMjdUMTE6MzA6NTctMDY6MDA8L3htcDpDcmVhdGVEYXRlPgogICAgICAgICA8eG1wOk1ldGFkYXRhRGF0ZT4yMDE2LTExLTI3VDExOjQyOjQ2LTA2OjAwPC94bXA6TWV0YWRhdGFEYXRlPgogICAgICAgICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1IChNYWNpbnRvc2gpPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgICAgIDx0aWZmOlNhbXBsZXNQZXJQaXhlbD4zPC90aWZmOlNhbXBsZXNQZXJQaXhlbD4KICAgICAgICAgPHRpZmY6SW1hZ2VXaWR0aD4yMTg3PC90aWZmOkltYWdlV2lkdGg+CiAgICAgICAgIDx0aWZmOkJpdHNQZXJTYW1wbGU+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpPjg8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaT44PC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGk+ODwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwvdGlmZjpCaXRzUGVyU2FtcGxlPgogICAgICAgICA8dGlmZjpSZXNvbHV0aW9uVW5pdD4yPC90aWZmOlJlc29sdXRpb25Vbml0PgogICAgICAgICA8dGlmZjpQaG90b21ldHJpY0ludGVycHJldGF0aW9uPjI8L3RpZmY6UGhvdG9tZXRyaWNJbnRlcnByZXRhdGlvbj4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgICAgPHRpZmY6SW1hZ2VMZW5ndGg+MjQzODwvdGlmZjpJbWFnZUxlbmd0aD4KICAgICAgICAgPHhtcE1NOkRlcml2ZWRGcm9tIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgPHN0UmVmOm9yaWdpbmFsRG9jdW1lbnRJRD45Q0M5RUI0QjJBOEYwN0VDRjQ5MjhDMDhEREYyNkI4Njwvc3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD54bXAuaWlkOjQ3YzI0ZWQ2LTkzNjUtNDkwNy1hYzI3LWUwOGI3NDhkNzViODwvc3RSZWY6aW5zdGFuY2VJRD4KICAgICAgICAgICAgPHN0UmVmOmRvY3VtZW50SUQ+OUNDOUVCNEIyQThGMDdFQ0Y0OTI4QzA4RERGMjZCODY8L3N0UmVmOmRvY3VtZW50SUQ+CiAgICAgICAgIDwveG1wTU06RGVyaXZlZEZyb20+CiAgICAgICAgIDx4bXBNTTpJbnN0YW5jZUlEPnhtcC5paWQ6Mzc2MDY0ZmItNDk1YS00NzE1LWI2MTMtY2YyNzM3Njk5Y2NkPC94bXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eG1wTU06SGlzdG9yeT4KICAgICAgICAgICAgPHJkZjpTZXE+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoTWFjaW50b3NoKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMS0yN1QxMTo0Mjo0Ni0wNjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo0N2MyNGVkNi05MzY1LTQ5MDctYWMyNy1lMDhiNzQ4ZDc1Yjg8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGltYWdlL2pwZWcgdG8gaW1hZ2UvcG5nPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+ZGVyaXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5jb252ZXJ0ZWQgZnJvbSBpbWFnZS9qcGVnIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoTWFjaW50b3NoKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMS0yN1QxMTo0Mjo0Ni0wNjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDozNzYwNjRmYi00OTVhLTQ3MTUtYjYxMy1jZjI3Mzc2OTljY2Q8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgIDwvcmRmOlNlcT4KICAgICAgICAgPC94bXBNTTpIaXN0b3J5PgogICAgICAgICA8eG1wTU06RG9jdW1lbnRJRD5hZG9iZTpkb2NpZDpwaG90b3Nob3A6ZGRjZmE0MWMtZjU1Ni0xMTc5LTkyOGQtOWQxYzE0YWRmOWYyPC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06T3JpZ2luYWxEb2N1bWVudElEPjlDQzlFQjRCMkE4RjA3RUNGNDkyOEMwOERERjI2Qjg2PC94bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDxleGlmOlBpeGVsWERpbWVuc2lvbj4xMDAwMDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOkV4aWZWZXJzaW9uPjAyMjE8L2V4aWY6RXhpZlZlcnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj4yNDM4PC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgICAgPGV4aWY6Q29sb3JTcGFjZT4xPC9leGlmOkNvbG9yU3BhY2U+CiAgICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2UvcG5nPC9kYzpmb3JtYXQ+CiAgICAgICAgIDxwaG90b3Nob3A6VGV4dExheWVycz4KICAgICAgICAgICAgPHJkZjpCYWc+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8cGhvdG9zaG9wOkxheWVyTmFtZT5BRFZBTkRaPC9waG90b3Nob3A6TGF5ZXJOYW1lPgogICAgICAgICAgICAgICAgICA8cGhvdG9zaG9wOkxheWVyVGV4dD5BRFZBTkRaPC9waG90b3Nob3A6TGF5ZXJUZXh0PgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgPC9yZGY6QmFnPgogICAgICAgICA8L3Bob3Rvc2hvcDpUZXh0TGF5ZXJzPgogICAgICAgICA8cGhvdG9zaG9wOklDQ1Byb2ZpbGU+c1JHQiBJRUM2MTk2Ni0yLjE8L3Bob3Rvc2hvcDpJQ0NQcm9maWxlPgogICAgICAgICA8cGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPgogICAgICAgICAgICA8cmRmOkJhZz4KICAgICAgICAgICAgICAgPHJkZjpsaT45Q0M5RUI0QjJBOEYwN0VDRjQ5MjhDMDhEREYyNkI4NjwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpCYWc+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\">
                </center>
            </div>
        </body>
        </html>";
    } >/var/www/lighttpd/index.php

    systemctl restart php-fpm
    systemctl restart lighttpd

    # Installing Percona Server
    clear;
    echo "==================================";
    echo " Installing Percona Server..."
    echo "==================================";
    yum -y remove mariadb*
    yum -y remove mysql*
    yum -y install http://www.percona.com/downloads/percona-release/redhat/0.1-3/percona-release-0.1-3.noarch.rpm
    yum -y install Percona-Server-server-57
    chkconfig --levels 235 mysqld on
    systemctl start mysql

    # Get Percona Password
    PERCONA_ROOT_PASSWORD_TEMP=$(cat /var/log/mysqld.log |grep generated);
    PERCONA_ROOT_PASSWORD_DELIMITER="#";
    PERCONA_ROOT_PASSWORD_REPLACED=${PERCONA_ROOT_PASSWORD_TEMP/: /$PERCONA_ROOT_PASSWORD_DELIMITER};
    PERCONA_ROOT_PASSWORD=$(cut -d "#" -f 2 <<< "$PERCONA_ROOT_PASSWORD_REPLACED");

    # Installing PowerDNS
    clear;
    echo "==================================";
    echo " Installing PowerDNS..."
    echo "==================================";
    mysql -u root -p$PERCONA_ROOT_PASSWORD -e "CREATE DATABASE powerdns;"
    mysql -u root -p$PERCONA_ROOT_PASSWORD -e "GRANT ALL ON powerdns.* TO 'powerdns'@'localhost' IDENTIFIED BY '$POWERDNS_PASSWORD';"
    mysql -u root -p$PERCONA_ROOT_PASSWORD -e "FLUSH PRIVILEGES;"
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
    mysql -u root -p$PERCONA_ROOT_PASSWORD "powerdns" < "powerdns.sql"
    rm -rf powerdns.sql
    rpm -Uhv http://mirror.cc.columbia.edu/pub/linux/epel/7/x86_64/e/epel-release-7-8.noarch.rpm
    yum -y install pdns-backend-mysql pdns
    chkconfig --levels 235 pdns on
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

    # Finalizing 
    systemctl restart lighttpd
    systemctl start pdns
    systemctl restart pdns.service
    systemctl enable pdns.service

elif [ "${option}" = "3" ]; then
    echo "Not Implemented";
elif [ "${option}" = "4" ]; then
    echo "Not Implemented";
fi

#
# Final Screen
#
clear;
echo "o------------------------------------------------------------------o";
echo "| Advandz Stack Installer                                     v1.0 |";
echo "o------------------------------------------------------------------o";
echo "|                                                                  |";
echo "|   Advandz Stacks has been installed succesfully.                 |";
echo "|   Please copy and save the following data in a safe place.       |";
echo "|                                                                  |";
echo "|   Percona Root User: root                                        |";
echo "|   Percona Root Password: $PERCONA_ROOT_PASSWORD                            |";
echo "|                                                                  |";
echo "|   PowerDNS Database User: powerdns                               |";
echo "|   PowerDNS Database Name: powerdns                               |";
echo "|   PowerDNS Database Password: $POWERDNS_PASSWORD                       |";
echo "|                                                                  |";
echo "|   You can access to http://$SERVER_HOSTNAME/  ";
echo "|                                                                  |";
echo "|   NOTE: Before restart your server we recommend execute          |";
echo "|   \"mysql_secure_installation\" for secure your Percona Server.    |";
echo "|                                                                  |";
echo "o------------------------------------------------------------------o";
