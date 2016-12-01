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
PERCONA_ROOT_PASSWORD=$(date +%s | sha256sum | base64 | head -c 10 ; echo);
sleep 1;
POWERDNS_PASSWORD=$(date +%s | sha256sum | base64 | head -c 10 ; echo);

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
echo "|   | [2] | CentOS/RHEL/Oracle Linux | 6/7                     |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | [3] | Debian                   | 7                       |   |";
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
echo "|   | HHVM                                | PHP Replacement    |   |";
echo "|   ------------------------------------------------------------   |";
echo "|   | PowerDNS                            | DNS                |   |";
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
    echo "Installing HHVM...";
    echo "=================================";
    sudo apt-get install software-properties-common
    sudo apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449
    sudo add-apt-repository "deb http://dl.hhvm.com/ubuntu $(lsb_release -sc) main"
    sudo apt-get update
    sudo apt-get -y install hhvm

    # Install Lighttpd
    clear;
    echo "Installing Lighttpd...";
    echo "=================================";
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
    echo "Configuring HHVM in Lighttpd...";
    echo "=================================";
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
                        <?php if(isset(\$_GET['installed'])) { ?>
                            <div class=\"alert alert-success\" role=\"alert\">
                                Advandz Stack has been installed succesfully!
                            </div>
                            <h5>Installed Stack:</h5>
                            <div class=\"list-group\">
                                <a href=\"http://lighttpd.net\" target=\"_blank\" class=\"list-group-item\"><span class=\"glyphicon glyphicon-send\" aria-hidden=\"true\"></span> Lighttpd</a>
                                <a href=\"https://www.percona.com/software/mysql-database/percona-server\" target=\"_blank\" class=\"list-group-item\"><span class=\"glyphicon glyphicon-oil\" aria-hidden=\"true\"></span> Percona</a>
                                <a href=\"http://hhvm.com\" target=\"_blank\" class=\"list-group-item\"><span class=\"glyphicon glyphicon-console\" aria-hidden=\"true\"></span> HHVM</a>
                                    <a href=\"https://www.powerdns.com\" target=\"_blank\" class=\"list-group-item\"><span class=\"glyphicon glyphicon-globe\" aria-hidden=\"true\"></span> PowerDNS</a>
                            </div>
                        <?php } ?>
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
                    <img style=\"max-width: 150px; margin-top: 15px; margin-bottom: 35px;\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAABJCAYAAACHMxsoAAAAAXNSR0IArs4c6QAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAACXBIWXMAAAsTAAALEwEAmpwYAAARsGlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iCiAgICAgICAgICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgICAgICAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgICAgICAgICAgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiCiAgICAgICAgICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICAgICAgICAgIHhtbG5zOmV4aWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vZXhpZi8xLjAvIgogICAgICAgICAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICAgICAgICAgIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyI+CiAgICAgICAgIDx4bXA6TW9kaWZ5RGF0ZT4yMDE2LTExLTI3VDExOjQyOjQ2LTA2OjAwPC94bXA6TW9kaWZ5RGF0ZT4KICAgICAgICAgPHhtcDpDcmVhdGVEYXRlPjIwMTYtMTEtMjdUMTE6MzA6NTctMDY6MDA8L3htcDpDcmVhdGVEYXRlPgogICAgICAgICA8eG1wOk1ldGFkYXRhRGF0ZT4yMDE2LTExLTI3VDExOjQyOjQ2LTA2OjAwPC94bXA6TWV0YWRhdGFEYXRlPgogICAgICAgICA8eG1wOkNyZWF0b3JUb29sPkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1IChNYWNpbnRvc2gpPC94bXA6Q3JlYXRvclRvb2w+CiAgICAgICAgIDx0aWZmOlNhbXBsZXNQZXJQaXhlbD4zPC90aWZmOlNhbXBsZXNQZXJQaXhlbD4KICAgICAgICAgPHRpZmY6SW1hZ2VXaWR0aD4yMTg3PC90aWZmOkltYWdlV2lkdGg+CiAgICAgICAgIDx0aWZmOkJpdHNQZXJTYW1wbGU+CiAgICAgICAgICAgIDxyZGY6U2VxPgogICAgICAgICAgICAgICA8cmRmOmxpPjg8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaT44PC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGk+ODwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpTZXE+CiAgICAgICAgIDwvdGlmZjpCaXRzUGVyU2FtcGxlPgogICAgICAgICA8dGlmZjpSZXNvbHV0aW9uVW5pdD4yPC90aWZmOlJlc29sdXRpb25Vbml0PgogICAgICAgICA8dGlmZjpQaG90b21ldHJpY0ludGVycHJldGF0aW9uPjI8L3RpZmY6UGhvdG9tZXRyaWNJbnRlcnByZXRhdGlvbj4KICAgICAgICAgPHRpZmY6T3JpZW50YXRpb24+MTwvdGlmZjpPcmllbnRhdGlvbj4KICAgICAgICAgPHRpZmY6SW1hZ2VMZW5ndGg+MjQzODwvdGlmZjpJbWFnZUxlbmd0aD4KICAgICAgICAgPHhtcE1NOkRlcml2ZWRGcm9tIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KICAgICAgICAgICAgPHN0UmVmOm9yaWdpbmFsRG9jdW1lbnRJRD45Q0M5RUI0QjJBOEYwN0VDRjQ5MjhDMDhEREYyNkI4Njwvc3RSZWY6b3JpZ2luYWxEb2N1bWVudElEPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD54bXAuaWlkOjQ3YzI0ZWQ2LTkzNjUtNDkwNy1hYzI3LWUwOGI3NDhkNzViODwvc3RSZWY6aW5zdGFuY2VJRD4KICAgICAgICAgICAgPHN0UmVmOmRvY3VtZW50SUQ+OUNDOUVCNEIyQThGMDdFQ0Y0OTI4QzA4RERGMjZCODY8L3N0UmVmOmRvY3VtZW50SUQ+CiAgICAgICAgIDwveG1wTU06RGVyaXZlZEZyb20+CiAgICAgICAgIDx4bXBNTTpJbnN0YW5jZUlEPnhtcC5paWQ6Mzc2MDY0ZmItNDk1YS00NzE1LWI2MTMtY2YyNzM3Njk5Y2NkPC94bXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eG1wTU06SGlzdG9yeT4KICAgICAgICAgICAgPHJkZjpTZXE+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoTWFjaW50b3NoKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMS0yN1QxMTo0Mjo0Ni0wNjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDo0N2MyNGVkNi05MzY1LTQ5MDctYWMyNy1lMDhiNzQ4ZDc1Yjg8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6YWN0aW9uPmNvbnZlcnRlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5mcm9tIGltYWdlL2pwZWcgdG8gaW1hZ2UvcG5nPC9zdEV2dDpwYXJhbWV0ZXJzPgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgICAgPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+ZGVyaXZlZDwvc3RFdnQ6YWN0aW9uPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6cGFyYW1ldGVycz5jb252ZXJ0ZWQgZnJvbSBpbWFnZS9qcGVnIHRvIGltYWdlL3BuZzwvc3RFdnQ6cGFyYW1ldGVycz4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8c3RFdnQ6c29mdHdhcmVBZ2VudD5BZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoTWFjaW50b3NoKTwvc3RFdnQ6c29mdHdhcmVBZ2VudD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OmNoYW5nZWQ+Lzwvc3RFdnQ6Y2hhbmdlZD4KICAgICAgICAgICAgICAgICAgPHN0RXZ0OndoZW4+MjAxNi0xMS0yN1QxMTo0Mjo0Ni0wNjowMDwvc3RFdnQ6d2hlbj4KICAgICAgICAgICAgICAgICAgPHN0RXZ0Omluc3RhbmNlSUQ+eG1wLmlpZDozNzYwNjRmYi00OTVhLTQ3MTUtYjYxMy1jZjI3Mzc2OTljY2Q8L3N0RXZ0Omluc3RhbmNlSUQ+CiAgICAgICAgICAgICAgICAgIDxzdEV2dDphY3Rpb24+c2F2ZWQ8L3N0RXZ0OmFjdGlvbj4KICAgICAgICAgICAgICAgPC9yZGY6bGk+CiAgICAgICAgICAgIDwvcmRmOlNlcT4KICAgICAgICAgPC94bXBNTTpIaXN0b3J5PgogICAgICAgICA8eG1wTU06RG9jdW1lbnRJRD5hZG9iZTpkb2NpZDpwaG90b3Nob3A6ZGRjZmE0MWMtZjU1Ni0xMTc5LTkyOGQtOWQxYzE0YWRmOWYyPC94bXBNTTpEb2N1bWVudElEPgogICAgICAgICA8eG1wTU06T3JpZ2luYWxEb2N1bWVudElEPjlDQzlFQjRCMkE4RjA3RUNGNDkyOEMwOERERjI2Qjg2PC94bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ+CiAgICAgICAgIDxleGlmOlBpeGVsWERpbWVuc2lvbj4xMDAwMDwvZXhpZjpQaXhlbFhEaW1lbnNpb24+CiAgICAgICAgIDxleGlmOkV4aWZWZXJzaW9uPjAyMjE8L2V4aWY6RXhpZlZlcnNpb24+CiAgICAgICAgIDxleGlmOlBpeGVsWURpbWVuc2lvbj4yNDM4PC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgICAgPGV4aWY6Q29sb3JTcGFjZT4xPC9leGlmOkNvbG9yU3BhY2U+CiAgICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2UvcG5nPC9kYzpmb3JtYXQ+CiAgICAgICAgIDxwaG90b3Nob3A6VGV4dExheWVycz4KICAgICAgICAgICAgPHJkZjpCYWc+CiAgICAgICAgICAgICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgogICAgICAgICAgICAgICAgICA8cGhvdG9zaG9wOkxheWVyTmFtZT5BRFZBTkRaPC9waG90b3Nob3A6TGF5ZXJOYW1lPgogICAgICAgICAgICAgICAgICA8cGhvdG9zaG9wOkxheWVyVGV4dD5BRFZBTkRaPC9waG90b3Nob3A6TGF5ZXJUZXh0PgogICAgICAgICAgICAgICA8L3JkZjpsaT4KICAgICAgICAgICAgPC9yZGY6QmFnPgogICAgICAgICA8L3Bob3Rvc2hvcDpUZXh0TGF5ZXJzPgogICAgICAgICA8cGhvdG9zaG9wOklDQ1Byb2ZpbGU+c1JHQiBJRUM2MTk2Ni0yLjE8L3Bob3Rvc2hvcDpJQ0NQcm9maWxlPgogICAgICAgICA8cGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPgogICAgICAgICAgICA8cmRmOkJhZz4KICAgICAgICAgICAgICAgPHJkZjpsaT45Q0M5RUI0QjJBOEYwN0VDRjQ5MjhDMDhEREYyNkI4NjwvcmRmOmxpPgogICAgICAgICAgICA8L3JkZjpCYWc+CiAgICAgICAgIDwvcGhvdG9zaG9wOkRvY3VtZW50QW5jZXN0b3JzPgogICAgICAgICA8cGhvdG9zaG9wOkNvbG9yTW9kZT4zPC9waG90b3Nob3A6Q29sb3JNb2RlPgogICAgICA8L3JkZjpEZXNjcmlwdGlvbj4KICAgPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4K94X67wAANOhJREFUeAHtnQmcXUWV/++9776tmyQEUCSgDiIOBnDU8e8AAsoiAooImAA66Aj+AzoDCJKkOySd20nIAhiEODomyjoCkkEZxQVEguL4xwV3kAEUDAiyxJCll7fd+//+6t7b/br7ve73ul9igFuffn23Wk6dqvrVqVOnqmxrG7sgCOyZa9c6a2fOrCgpz/MyBds91beCE4LA2tex7cm8LuPvWdsKfmE77o3Luub9JPLrPPDAA/batWt9ngO9S1zCgYQDL18O2Nsq6waoZs50ABsDVEpnbvcl77Gsylzbdo5Ip9MWgMUPLLJt/kJSisXiJicIrguszGdWeB3rFQ6Qc6KrQCsBLjEjcQkHXoYc2BaAZc+YMcPZf//9A4BGkpHV6S2dHtiVOYDY6elsNlMulSwfJ7yCgBiAREvKdV3LSaUsgOsxXlyZDypriKdX8RBvqjpevUtcwoGEAy8fDrQUsIYDSufSpbtWSpV/Q4z6ZDabfWW5XLYqvl/hWSAlqclITobdtq13jBTNNzedyViB71vlSvn/pRx7+bIFC75h/PFvxi23pPZ/8MEBQIzfJ9eEAwkHXtocaAlgacj2wPTpdqynugVA+eXvH/5QJQjmZDLpA3wwqFQqSZqqgEoprmOlK8nMZ5hogKtYKFi2Y9+cSjkrll588a/iIlE6gKT8xVJa/Cm5JhxIOPAS5MBYwDFqlqWn6u7uTgFY5djjxUuWHF4qB52Ay7GpcGinT/o+VKKKA9S7CoSMgouAjpOSxFXo738BJf0XHNdZBXA9raACy+mA5cxIqV8vuuR9woGEAy9+DowbsAAKl58U6ka66Vi27PVBsXwhD2cy/MtKKkKyqhhlehCkxssq4pNk5ksxDwCmXIFgofAwz5ej37oBGvoVt6QtFPxWtZJ/vGkm4RIOJBzYMTnQNGAZYCAv8fDPu+KKnQtbej6ObuqCXC4/rVgqWhVcNOvnICU1nUYdVsVKehtpi4nEwKqUynfbKWvF8gUL7ozDzJo1Kz1t2rQKQGYU/vH75JpwIOHAi58DDYMJAOA89dRTqdWrV5eUbT332+4pAMecdDbzNib9pKeqKEKkIodrw3E3yUaj3yKMm83lNEz0GTJe66TsKxgm/i6Kywa43JjWJuNPvCccSDiwg3KgEVCxZ33xi+7qs882QKV8zPWWHAIsfdp2nJPTmbTV39/fjEJ94qwI9Vsaigqs3Ew2K+B6jsysqmAGcZnn/UWJAKpudB3Qsek5cQkHEg68ODkwKmCpwWNpHsR6ofmLF++DDPXJwA7Ozufy7f39fZKmSgz7pKMaNFHYXryoMoVw02lX+q1Cof+3lp26fM+dJ331vPPOK4gUADc97emnk2Hi9iqXJJ2EA9uIAzUBS/ZUU48+2omlKs9DT+X0fKQS+J8CqPYuYvhZKZclcf1tgGo4M0KJSxMADsNEB9rQo/nfsp1gJfqtu2PvXwS4zq6SFOP3yTXhQMKBFwcHhgOWjVSV5lcU+TJbmLdkyUmYel7A0O9QzdQxQxcPr8xwa0fKpmYUoaeM6UM6l89b/b29/dB8jZNOfW7ZvHkPitbI6FT5jPOxI2UhoSXhQMKBUTgwAFgjrNQXLf0n9EHng1qnm8bfFw7/ZMzZopk/Kc8HQSOcVmxp3KzzyeCsvr6+J4j+83m/fDVA9az4odlElPJKXyCXuIQDCQdeBBwwgCWpIzZT6PCW/Z1ll8+hFX8coNoVZbbW/UnikkTVEj0VcWs2MaVZPpTmhk3MMMazjKKpJekQjwFElPJGGiwVij8FuK566xvfcLMMTQEvkw7XxATClELyL+HAjs0BLVSWwaXZUaFj0aIzMW+ancnm9vMrFQtdVQn0EIC0cvhXkQEoM4wWarD/JfJHib+NZN6KfdUUGZziBCCtAa1Qv1ViaJjO5XK2ABiZ6jbLCbwVXV2/VmICrgS0xInEJRzYsTkwsP3L3EVLumw79eVsLr8fM20VsKoImKQhf+JgFc7maexlwAoj04JfLnt2kDkm/5pXfzDIZU90U/b7kIDu1LYzOIFVKPVEYfVyXC40XGUldRD09/UVpYfLt7d/QKDFMiLMMwacgDlxCQcSDuzAHDCNtLN7yUftlHOt6GRHBS11yfBrpYSj1ckVrQk0CQbBvOULFyxTetUu3N2hvDabyR5RkHIfBDXfW2ctr+g0TKy0tbdne3t6f2fn0u9d0dGxHgnL5WeGkPKUuIQDCQd2PA6kkDJeza4K17IP1dRSuazxWI6fdtQDY1pira6IZFgaZFlSUylX7slblVn33HNPcMHKlfljDz7YesPpp6cO33ff7GWXXLL5nUccvZ6h4inYVeUYlgpAUi2jJcyTMcVAgiwBWntU+gvWj+5Zdwf0+NLlPbh2rfKduIQDCQd2QA44Zd86Hd3R3myYp72ozHjMAEQrpJpoOAdYmaEgUhNR2/8hfRG/zBUXXtgnqUb2XgcffLAxpVi68OJ14ORt2sgPFwNna1inPIU0SXq0S0U2EmQWdO6iRfsrAfbYMgJgaxJLYkk4kHCg1RxgcbJ/tGmlbKje6shl/iDpSrCDiYEWGP4gF5S/HqUzZPgVzdppKIo45l+PcnyLrNdRPZWNGUUEfhOmMQJi6HJZqB0A1q+yfesdilfgya81Q+EJE5pEkHAg4cBwDkitNJ2hYAQr0cbqLZauHNtJlYoSoBxtd1zU0IvrCIBkGZCZrbx04cLvAaLf1lIbrqFU1AqaqnIvsy/iriiNwHH2iT9pb634PrkmHEg4sGNxQLsq7KGlLBqrGUmmFfRJGhqQrrRtKAdOWMH/TJs6+b8UvbY35jICsGReAZAZKQuV/7X9hULPgJSlgK2SshRX7ELgkt5umzmtGNhmkVdFrHRMWtE1vsfLtkrfpFeVThU1Y9/G9JrwY3tv1Efr6vEoKUY0j4evgzwbJf6mPo1e7uOhsYnkR9a56nIdfm9wIWwP46LLDQRUcmEkTRA6tlcirrBMxtWhE6SyWouRZfcFKNU11IylLNYAfneut/gOTLZOJrxoNCA4dqoN+qjOL1xtMFSz3gzdsHhbxV9Nj3Z/NWXp8bY7+qJ7yzP/zbfwsT7/9b0JF5CmGUJ7BPJMH9RYXlWRY3qj9FrBI8NvVbZt7eIypS43ZcOHf+W7mrwJ59uj3BWjxy+OWfdRuevOMIS0ldaE01OEsfM80iZRjxdx2vG3Wtcqf3UxoFa4+J2WwigDJkPxywldq6Qr6o3tummL4eB9u0/eaa3i1ak3SFJ1mSYpS7srSBHvpKxr0GUdjwI+h7mFFlunjZRVDTbjJTakc7yhGw0XREuetD5TM7B1891ohLX8qfGrAUUVspaXIe/wF4LMKB3HkAC1H1Rnhh0EAmRFtNQOEr5V+tCrCjuEH42EHSXemJ7Mhg0b7FWrVhkL5FH8j/sT9Lt9uVw75jCbuNcEUiOgFdM3pKG2KM9D4qyXMdHJN62jbch/vXjMe7XBsM6NP64ojlHTGfbRbR1SDYsZw09ylJJlAllbfWE4I+h4CxcGVtjjjwgQv9h4112GCZyUc/vc7kXfQ2F/AoClrlvy5zYgufV6diqFy6/8ugMPPKDHt07g/nP8Xojfx3md6JX4TOP/9JIlr4XZhzuBnYJPTP4yqMbxD3u2YJNVtp+s9OUfvfzy2T2EMfxV2Pi+CTpMw8N/cNGCRQc7djAdwxPtBtub6uu7EyDaOFojjNOcPX/xPpYbvAMbE0eKS6K4h7CPN0HHEK/oRc1hvT22fVLbbq/U4bxr5CFOb4jncT7E+SLO8pzuxefN9RY9ssLrupnnUUErDneu503O2fa7qcVTOZuzEJSC+8jzI+Ok0ZRDZ+fSXcu50jHEmQU/sFAKxUvi1+lUWxjkPIX2+NHPhnXPlHv16pbxsMLQS4fT0bFsaiVfPJq0J1HPMDMI61y9OKGJbYIroIKTo07+eIVtPxD5jetUvaAD71vbUkOpJQQW+Cardb/i//QVbblbBlKUnzFcLGXJm+PYVxf7+4tIWQLXcGaxgTjGSILmti2Ar0aqvvVuKuW8PsuaXuNrK16ZMnTL/j/ZlnM9y72vgcE3gGLX6cf9V8jq19mx7C5np967aWSL5yxe/PdKWA1NlbcZImh8A7xjvmLfVNr9UiadvTrluDdXMvnTFBczvqJJlXCIG9IwXbbUzmavY73UNXhcwcTHJHnGjzskUAMPpgFGh5DYgf1RP/DP+6Tn7aSgT+2xR1P5ayC50Etg7U0Nunr2gkVmhlmnRil/tcJTn817CHoV9K1qb29fg/L4eidtvSvyXzNcrbiid0rL8LeSLe5NnFezyB8+OtfH5c7s/39SULfSgu7MWM49c73uz87xvLcpvNpXs+VeTctTT4U8LecKrybtz3GEw5epFaa+xenH15TjXBf/UC5en83nrke3I6NxU84RzwjemGuWUQ3FSgNFurLo8EFTx/ni7Nmze9TLLJR01aDThnvymq1UvkHV/77MIggMRHNpBdi0AvTq5GXGDDMLKhOJDPQes/PUqXlIf4+8866sWdI6QSf8Wngih7mGpZ0qtBsrPx4zu6Uz2bdzbtp8Dlv7YUf34gvkT5VX+/TrvhFH2QYzQkDCxDjz3+w79ivemc6Jo9hOV54VJ9cRgLVHBB6z2QgS6fs90jUpLB5vZ13nbxtJv5Yf1AyG/tme92a+H5RJZw6Y5NuHyS/1KOJIrZDjf0cl3DB58pQ8e5dcd5HnHRBtHqCzBkbke1gqeFEVFllG4Bn2eRyPMkmKCl4TXJmMKXOVu7YrmUrh/wPb8p5PHVxHh7Vslue1NVvuQ6j6x/CJUhd+7KT0SNcx9Y06V133tLnBQH7j8g6sS+N1vEPibeChdYAlAKCwKAbDPDGOXR5+lvNLZmZQClbciMpDxa5ZyLz3tQWMrvQcX0bKKmOCkCKO1khZY1esBthX28vUozcavvZY1tH4OIg1kjDFPplVBXsqxLYwUNUQgIpRVCeB62MR+dOFYuExrk/yewHDfr1HIi9bCKuvpHxWMqlxld7JBq4ZEJUeUuGkw6G4r1HcJn7bfvtWHyAK3Yi6NXXqVNNC7UpwHOX4ek3GsL6zj1pzo4Ko16e8w/KNIhnrovrDzxgdO5ZzEnmbKr0pEtsMhVV8/JqW2sZK12Fg09vba+Wy+X24v+GixYv3VlrUc4HnENAyyltellMarVumjCgrdBy2Kayx0hrte2ClMfi2Cip3lT9LgJ9hLfBjxWLhCYzBN/DClJU2M+BE9Z2y+XzHVCQxaJ0c2T42zZu4E3Ctkpbx/X7rls3Po2L+A/XtT/pR3/6EkfgT6J8f5SDk56AL++ygkKZc2E79B3nLN/VO5cKvKdQeUalGY04j3ygpMSilQylwsrvazHVIAepD7ESwgAw3wo9Ov5E/toO5jSjuoTKqUFojZdUAz5imiVzJz8BOrTSgU+jlJm3t2Rq4afeAYtkfkLLkbyLpDA9LjRD/nGjxeI/tBx9jaHQQuqGj6OSOCSz/X8rF4q3aKkhlI9s7NmU8F13MAsUlCaFWGQxPR8/QPjCUtC3/NuJ7XD0paWe5nh75KVfHRxhXDUTLsSD0hHQ6Y0lqpmb8eGsQ/ERhALTx8MQ0OIY70+DACdgVWjRWCW/Hz/YukcSlYeGIuqX3E3H0ygiUttVf6C/lcm1vdir+9UharySfZR2AUituHSZF4+Agc8hhnSyVezz5HRJ1yjHnvkjC0XuitzudbPofUxiEc8reMbSs0yj3a8uVymYxgQ7Cz7W1ndJnpy5VANHLrya9+l7L4d+0y6xl/ZHyPxnoPcgJ0kf7jn2EX7KPcCz/aKTHg7geR/jfUS/Isp2lXDhI2V5K+H5+0mebeGqlUe9dU4TWiwTmG+mK7wIrWY9b9J6/yAfhzCA9p3rBmsRddNll7a/JZstkqKAKLvCK0yGMlJmq6OWO7iVfArmPlJRFwasXdqvSjYM0fq0BkI0Hru8zGvb4GiaQERSsMvS3egHbdrbAOPXcc6/6yqpVZq95Vdamepf6qUZfxLuwaZbRbz5yebhZ4bPR159xvY4hwUwA5kqG6q8C5HgVfHr2okX3XNbVde/Zq1erPoSiWBSo3iWa7bVWeN762d6i/2JgdxHlp/iOZpuityzv6vplJG3EEpNpnOktWw6FyIMBUyuocNwkepbPe95WpRN3UPXSrPX+gQemh/XKdt+NXugtSBhG0mCvtd3p5U8gzK8042waSJO9ea30hrwT8HCeAWcblHL5tkMBg6tJ5zR+W6MNIg0vJepJyqLqIlNFXFK9qKrrQ+Jt9gEmmoE11PhB5U+Xdi7YSBT6yf2C3y3UxxvYOXgVncR06FTaZ1MX7mbSQPpl1UPVnIG2x/1oTv6kR5Nku76ex9nd3TOoke8ALI16AnBffZnXdaf8GwPtcKa4XvCa7yeM8CbWwcavjKTCIrG+5HkXvKDv8RDC+OUfGTVAKfOFdE9fx1MbN39A36IGE3uLr6ZR7/2qV3wNHv1QUhZOqBaDZOxvh7g+HeneHDt1IkOTV0dDMXOwLDQfmd9t4yERoXEl2RZ02wyJ2hWxGk401DKMUwWlnXw88P0+GQyjf5iCxvEs+Y0btu7HcpShTBpM/Uk51o2lcnGjwJnyeQU4ZJTvxGHKTv74mWEbTev9dGiTJGXQ8TyCBHCH0lJdwI/xP1ba8Xf8u2vXzqyce+65WZrqydoQEh4LJGQQLTA5Cb3Wq3S7zZTvoZRkAwJlzuV8b6+d0lpZc8SceK+04yGh7qFPMCfA0GMLHTHj0JmgLzVtLKNyj4b6Np3X3bZjfYQyfxL1gdEzgXIfl8AgvteTChVXHWcSJKxj0gmH8ypnY4Q91/New5C3mxFGRtI3ncfjrCm5XHHhx0jbdeId9XVrAEtJ0ArIgZGuYMpv2UddyG32UIfAIRWR8w1Nae329PNvZDw4j+HKGfKrBqPM6z52CqsM6vAICvlLjMs1Ftc2NVHPOiiRxWH+VldVUNH7ac/bjZr5fo7xEaqKbpc9wCrs4MqY1p4p+uRPU/HbkFbD840bN/pr194iHppJAKW3YuHCb3FZo2FE1N8f3ul5++lbrBjX/RguQLox5Shpioi+rYqpxkA1eL+GaCaPYXkasOzwvNfz8ZiqpvrN5Z73R6WzMdJv6b5Rx2yViap911cezM2R4fIvM8xytdCeavIWeH+U4vvirFlIembY3Gj0jfrzkVZd8u4iafnsSPLhXsu5UoF1LiY8cJnZaDSucfob5KhvNCYmGsp9ra+hPpMqAhKHcrofA5SrhJXqrGg5B9ub+8zMIdJtiHhNUqAylgJfPwXlWXot6r1zPkr4NzIqioR+DjyOyprPQ/BA/ht1E28wsVirmo92XKyj9/wShz5sEBHRMpwBejQjFR9w6tv+hyZNnoxOyj6kY/HiI43/aMZnIEB4YzJolvYE1o92dCnLtZxjKLK3GtID68+w5nb4wqb4pk68Dxukfc23bVmRo4FdKN2GoE5lKvILRVTLuZUGjtmSqex7+pZj9D333z+M86M83nLLDClTTQREc4N2qEW/Svk7+zG4P0lBSd+OVy9wHPh7aNhvEBfwu4nq8t/yI6CPt+jWcyNOksPq1eFZmVDwgUwuZ8wYaCnKwb3qPLNZzH0s+xTynCGfwaxZZ5u8NxJ/Q34oT0DRQVp9mGH2evLmFOhQ0+nMJxluXaI4SLv8qsMOa226oxDnoLys+mzupTvcsMsuRtpjYu/2il95jE5fOsSdLNc29VR08psQHsSdXYe3+F0k/HEzFGSWGhvKO9os/1rRRRouv78RYMVgFV59Js9t6uzv7ZT9VREncBpO3IPRFi4L2NKFTH2kp6fHYhp6alCxPqow+C/WkLICVVBzzqDjrNEwi7qxQ0lZYQNaTYNlrwnLOpnexdU20Igc/4PQ0UHWnlD+ANu9HDc4UffqlcjvhCqJ4qnpwuo54lOsgE6l7Uf4+Ch8FC8z0Pw6eZ42zZiTGBAaEXjYC4FApKeyep9//h5k7B8oPqP4D+wPaqhG/spRPtsYIpwgqU7DQfyyL1r5vmFRNvxIR2gk8Qs97w3w/H3qDMRv1CJXQ9ZlPDPirMii9ag+y327Ih6vFFGPKOkABYpg4y9J/WxNPrD5JDOmRd9x3XlMaMxW2FXR+ZgWtvcw1oBIvTgn+j6afBkRza5//auRgNoqlcf4+JAKGLCVndA+VZ7HXRcpZzMKOveqq7K+HczBxm4y5aJTtnrpmIyiPcKDWKdZlWzjt+Mm0CSh3pWfCMOpthjpiiPjn9ELwGlI4ahRqwLrWymwP8yYfw/0533qlYnphM5FSw/TN3plUxl1H7kgltR23ym/llp4n5kVUb1XBRAdMXjGIbbzNW5AHd3d76AyvFPJa/94eHM3NicPUFV/zLBQw1kRPENWz/Ijg0Ndt5uLRKhiJrOZ1vMXpSuQgYhddU/5SGrSbaPO9JZaCkNN+E96U4pHwrZ1UNtuuyFphm6rlTqEl4cobg3XmGP7OmlJ4nOi04tir2NeFUZh5TFlp46j4e2jeJEYny0GwffzQfAdePyQ6hVGjfC5crL8EqasRqP7VjnSISp7ymXegu/CxbMwKeiX1CUzAnhw6dzu7v8bp+W6lcG0m2JxHMOErqac4EE/rHrGlBHlDv27DhcQxpGK6rCJP//XF85CljhOZZyRysGyPr/Cm//DccRZM8jEACuKEmorSBROqVB4OOunb9ZrGOPyM5mIvA3YH3V6S9UrnoGNhuAmXSqXKhwQMZUZjjOisLWlLPQhWuLDDNiacEE1c9gTGA/HdE30Sj4HGhCQdCISxm4CbwDqIcwKTGHR83+TBtXHNA5Ztv9Pe6RbWTtjcFg1UToMcDcYyU49PcxXWabRa1hIzzMgk8lmrsFoVM5Gxyj/TJd9Gynj15KgUKzn6Ec+FMdD0zgRSXpSOAS1f1XC8j76puFSU803VqB/yvN2psc6OVIRKJK7rvC8R6GpTAa+pQ4idPYJspPSfSzhRx8mfInAXTNmbcu9BXcj5p2DZCe9JVXA9Oefm93VbUw9KmVjhyWzHCNqTTjxcUdgI+uFDj6lx2lOEkehOmB0t5y49Xoq0qewbDdSNqD1+4rrXCWPmlTR0HQg0DhvJgxYMF/8p44SleN82fPmPiVadMR9NU3V0lWQCk7BBHcvGrAUdDJ86+de3o/vWLTsLbqpKWVFBos537/F94Ofczi9ejERAAr8TaUso6O4aP7iveHC8WGjlMgZfAdF46PKzxZ6fmrp/RjvWChm0TTYp+m9JEMKcsLlYOJq4l9vPp+Gd20KokYHD42yVM/NrEiQ/7isly1b9hxx3mziI07cMVozKGDh/r0CcQBN4Hj7FfPn/1kecEbiDm8b+x+vNcWS63CoP0TSApMxZZhodGJhLMFtKJafJ3MWw7PX275/vN7TuCasq6mmUvUP529tbze3ly6cfx3lf37MA/REGcp8DbNmh5TcynPyGwLZkOZhItmW/yijiFRVOavNPKjcbbufE9GbLoOY1qhdm8aLdd8FDIn3lcpGw3HAe8XK+fOfgBd2OOkRhxr/dcINhYwb6apY6H/U8ssD0lU8axCTJlsUuXmXXHIA2oUOhoOaUueSR1+ab5cR4ZSpU/f0g7IZ91OxkLKGiu9aOM17STNbHaQsloWoF5MuK+5Kw0S28/9YqYxu6jgImq7kGbs/x+mL18WkGFsj27pWRo1RVTX2SvoeKsYN9sbex3dtaFgcrqvAJmwX+LanGnQIIvazSlT8hadNtaZIR2VAG2PBryExPyEpC7OOXey0NdO1nPcx7Nyb9HS02zNAyDejtDKk11TZ4R9ThnBGCuA7hSGfWcAPwT/84wO/vVXxyl3qefeR3u0CDjcFaejU4vWFfJ5wvTeJxP9ISBKrHsW/FQsXfAEQvVjDf83GMbXfzhD46jSmLnjtlT+B1rZw8L1m2WnyQ+nNWbFiEmW+l9I3w8LAejYub2yjaoYdjU5UISbe2d7iY/F3psBK5iVIt19/4c/rzQoGZvjdOI3R4mrkm6lkjXgc4QfGqDJALXWR8neca9DVrJe/uMeNw6gQY3HQLwf7U1p/2rJl01aUsMqsKo9+5U3F4lQEkEnzWWu2ZMGCP+y//4P2kIk0FUa0Ri238843923cdA7Dr7fI1AFOS5kW6rJ03U5OM1ya9YwawweonOrtqZFWP5LkDGaL3k0/thOEbYZf+0JYL5W4jSHTLuwpLxz/JfzRsEpA3HSFqZvNaJZw+HeU6iYNt1LBsNV+vcCKWa7eih9ICR+bNTQFIgoXV/ZlnvfwHG/R12ms55XMyo3gLCRNrSU1SnFK6e5Lvfk/Uxhc0+mEwSxLVuws/T/eKNYltXF6294HvOnC2fsfqKGtQGwzGd1dOjXbkVRnHd5upQ7l23ejdFVHWsbvTZuY9Axdhks/BpJLKfsplPMcM7XvOH8Pny+nygeiCdBQXY2CbPvL4zK0po05hQIzuNZ0lYdm8WDM/8apo8tqqjyM7RzmRtLHkq05EkAE0Kjx/oo2+1K1i1mz2Coqms2N05nIVUAxPgfDcWXprtgZ9LG0HdykiGh0Az1gHHF1Q7TTzl0p13lPW5D/QCrtfMC3Kifm27In8Dspn8++i3UL5/TnckYZTDjTa8Xx6KrhCs72zjtvM49r1OAoeK0xbIrZhGlZZRVdKGcO43KYKiMVU2LUnqmUe3Eqnb4MSWMh188A7OfAtTRW3qWwqfinaDmHwm8Pd8EFF+ThaSj+B85HaUyumEAFfiybScki2tgEjIcWdUgaHigsffdXMaTsVUdGq9wHdcGbKB+zLASp6Da8SFJ2B2hRoAaclMODYSrvRzLfjSVGYC38tOyDWAVxKWqCS/h1MylzBVEeR10poQDHEj0vk5sBGzjiGVSAN5D2qF6G1iQNOY0ggJHuXFQdqzWTSJ3w4ccuxLOLUcjDD9XjUeNt0UdWV2SvjeyjUKWcmnLTu6vdQMfzNIOfKxl1vJTR0JyMkj55HFiClrNTZ1HHjxAwa+aR4v13OqX7FDyadR4lpuY+jU/CUsZAaBjOhAAElkrXIRE9pqSHS1cROQOMiO2zxiJThVmLgXoXzWpU7AxbmhRLn8Du5UDpwEikcSmrBZVFM040VCPLwI6TaBQ51vKWmB3JmllMZZIPasLcoN40eu20Zg+1hARAe4NTqbyPj1cLiPnVzDPfm3NKLm16VEv2N1RGX8teqGTscoN00tV9PqU3QxMXMkFA0r3zEnQN+iZdAwa8um3aaXggE0lMre9j8ce3kDZnsEBY28X6DBOyGFb+pBz4dyvi2LyimUQi5XBF1uu09w9qeY8cepO0qYemlsX8FruZpfKDNOmGi7Ot4AQmfKYv8+Y9yOys8d1M+rX9kjvxu8rBZ60nNJL3Pnvs/m9/ePrZKdSNU2UNj7cK5hdZ6ndViJbexm3ahQZbhsPRUjArXCpjny0pSMM2Ote70Qf/Uqk3a/IRTXz44mclKF+o/ChO6vZvy6XUGsUJH6QjLSKJpaxhBn7Sm9Vq3wo3moszN5qfod9CsKLjtErYXVEZ+v+UYWmGPMWFNDTA0Cf1wjIBIMMDJRbtVmGxrMXWezWa0TIjnQ96DGt5Z+fGud2L1wAKmomIdVnDqs/Q9Fv5xIyTupMKx4QdSNs5jr2/ENxszXp+n55/Ha2F9aGqzwxWJcdEQyAyfir7R+0nvV3Rr5wGAF9HuNgma4AvCjseR0K0ZV8K3kEbIO47Oztf4Wfz59EVSE+oVQk2veJ6ZjJXy6/KDzoMAOu5WUcFNXng6s/xFt9IA50hflA+vhoJTLj9M573fNQZhZJeg4kQp4bMhjbifDc95YGKk3uy0K+1jNjQmKF3LJWzl6HdT9e6O0n8M/yYymhgNwxWZdD6oAxV1fHF+rAGyRjTW7yKIxwOzUprhQZDplmIl5PzbW3H9fX2RqsJqRdNSDSjJizwi4aXaPT/Kr/wamASRctv7N7ej9MxdeFvJ4E7ZdOH/PsF/JlZXq4Nlwd+XY98KR3fKn8aoNqryI4khUphC3F+auUlYeeHP6Ovk7/hDv5QH2oLJcP9Vj83D1iSTIw0i9aKjNvF4g1LFnQ9okiF5tWR17pXRaEXjitVLS9aolPzffxSTI4rWz7I39RX6j2Hxjcd3ZFQgXq77UVtaFADMrMjJPc+dmPYi9U3InETqXdf1rXg3pje4Vd0PC/Awys0NIDaQ/c+4IDDsSJdF/kTsI0btIzZBJ0dVeHEOd3dD7HPyhTL9ndlceH+pHYUJkJvkt5HEqCZmbWD+csWeg9F+Wm40g7PU/QcsFxGdcpvsyp39wbOvYDEYRoNoYx9ghGiUbZjNiFR0/CuTjy1Xkt94UtvYj39zKkyZVCvDs9/E+Rz/7pi7twttQLpHbvWTqNzOMUwNbA+CHD/BzOaG6hDMt6tF2zC72PQWsWOJUw2fayvt+drDA8PMTpXdWEjZLNxJ8kkKE0PBKSrOorF7MxJBbvQR05Bc7if1dN7BMPyg6hzxotOikK6WoQZxj1RimO22yrKtHLBsPKi7u4Tif9jZihIeZRL5f/FbHoS9e4o6h2WO36JpUKqzwMOS3zznCq4P4dcY6858LGBm+Z0WHGPgL6OqXnMbvqfCFJ2KF1RkUbpraqJ1n0jP5FfHW5IdqKZNcvzLnoe3cAaE2G0kM14jGkdEqqlDwbsO5cu3ZWa936lL50NrejeTU8+acbv6kFqpchM2m2A1Xp9o+HlqVjVupWaYWrFU+udsW2zrHZ605VQBEBIt+isYWfQCxCI36RGboYDpZK2f/lXjlS7oSqecQNlHEesswAApWO8UQ0J/Qa3wZ1Vm/Q1C4wDjWTKM8+8HQa9U9KVAJ8G861L64DVAP8D62sAJitS1KEEb0bKPEr0Mpw3kx26b9aZPlugo2GpwZ7aMQBaRqclY2rHypxRKBR/wYnj8sxKJSKYoKv4xrbLFj8gRujQSdljOOvcROTXsJ61E2X4QcIyM/ynjgIwS5gUWK6ka61GGY0kpHCjo5apihM4c5il1exbP/wlueCNlAf1Kap3gY3kG6yNf0y+fBW6bqVZ3FrJls0aRsIofMN1vjkJSxGLy5HuCunqKysWdP1eGYxtY4ZnlorrgMgNEzQ8PMunYeoM2a4MKVziRcq6BZF+ZgWV+03ohM5OpzP70XuN8Dsyzgm/UX5MoysXy+9FyXuQwEqSC2R+Qz0r9GVmzjRb0ZrEIoA1UgLfHp/jdX8PKecsdbOAzCksPr6SGbaHxssrejZWhKPadxlmBgH9Scag/QDTVJ2UGK7Q3/dj6u8iFkHfoWdJq9BkxEM9T8QRj5GCtJDdLab+u2yV5xLf35GeEWXQXZhhUjNp0KgGZpkxYPtorq19J+qghjXPYv73DcVFujkuxWr+RcuGik7BvYMG8iAN90AxhWHZmfj/Gr8yuqzmOu2IcBqm4bUkVbu/kLFe/eroy4iLdJPiret5nX9kYfwZW7duvTOfz+/Z39/bLHCPiBwwEP150YFOkj3ytPkU0BCVtS66F2AVi6XfIZIuv3TRwq8ookgH23C5q56obisstevCtva2QxBapGgX77WKo11Kd5VNrQav9xqViaZCf48J0yxkNw5YAgwAi0QlXUlx/OdUOmUyLjFdFVRED3OyAG5G3BwWXI9rYTZ5JO3hoIXZA7osy1LvNXfRki/z/TICUFqGDeOqiDUIGPGKPJnZKk3ZWtYz7xZQIWJL1r2fbVHvVAB0GdA2CALR0GOwR7etm2hwJ5K5KQiSk9kP8liCPUQlUjymYxiR8CgvGPIVMFDYxIytjAK3Uj5cjNmA+I8uIfgL8/u/ZhB1h53P3RkPoeKh9ShRN/0p7ryWLr34aYa/t8Obw0qTJ/1QEaGnDFGziVgN0uGf3R5eR1N4B4BTJhJ2tTFbK/9aUQEKheH1QxJ/VG82sK7vViYu/14tl/r0pt7AfSvBfoo+NSB+8apJuoIeGmuZsa7LfNuWKZtlRVFbeS261A4igHiQIdMZlP0tFPKuChPrvXTfrIMfZcSqDUhNiks7zYoOlbmummR5jpsHAPq7fCf4zuWLPGNvJ11ybGqEnwadrHDWWuhs/wHB8p97ezheBRUIPDU4gu2Vj5phtPYuJa/G8lwG7dHC5toYCY0DlokPYRMRzqBksXwTQPE7vY4raI0kA2/Fir0QF/fim084tK/hDok1/A55Jb8sOMTG3/Ynu+6DFHqPAJOcmoohz6oEYrz0Ynk/fWNfsTgLyWJfmCZRR5VQSBdezUPVvxCAq140fqsek7Q1ZWtvtdIrynblKnKmBJ//zPyuPykmDQVqxUg4Q38bC4X7KvaRqKJzdI46wNEoS1W5a4Wr9474TA/p9Ls/qOT8I9XjMmL3WR4hvQYbJ3N6Ssrfggb2+c8uXPBCHI8aspbgxOHj9624CiiIVzo+Kq9/HdjygytYUqW4x5NevJNDkMttCPrLH5Vwi1mqxQLuJxRnBEqj8q0YVK5MW+lv0uMigFCH087jChvSqLuxXXXZMBP279h3YajKMUVWhrJD64yrlz+lExtCMwxfh6Hlx/Aumy3N0DUs5cg/TgBo8ttulR7uCdLHU+6YqCBmswOpyp2Pvl1O9aQqqeeXLQt3TgmDhhJ1zNP4XSNXjWbkj9WSf0mnKh/Wjsc8gtkh61WPme2hGfASLZquwx2k8b1iAauPRt8kiQz3VvfZ7lx8CcNfQHq0QGHjVqxFlNsZRM+nsaU6HsD6VS3pCmaaysp1Zxb+fY0O7FDCqpcnjtESwgceDfmC3bDxTkHCvYiDVT+jXMRx616u+hkpqwOTgmWsYQpRXpmqB1hh8DIzN26hr+/K5QsXfEqvmhCTRWbNQuH9aN+UTL3v9d4rTH03DMjrezRfBFIpgW51AxwjzI7wuSZvGgGrUfzUjLOBzNYLV+/9QJTV9ZV7l1/Njm0gwOg3Y6ZXHVzp8SwpfzQpqDpIrfum0qwVQdW7puPCIlhhRnERWNEy1TiZbHGtSrF8q8BKodiQXg3AMbsOMD6LlwDwyS/YqVMZUx8BaMhUf4qWa8QtvF6q8XfFzVS0hf2KhS3PJzjA4RbZCsmuiPRKsa6Cq8WzlngUMV79KvsRnYOu4rUsgVFFYMBcx4X5qvOxodeGVDUGSSpxCOhQZajORvyp+qrv4lt1OL0bK1x1HIP3IbAPiW/wo7GNs1UuskZnCCgdnxTB1V5eDPeGN9A9ZKhPXsZsfALmcZZTPb6MOz7VD9EimrifCFiJtpo8iYlWG6G8zSMmOK1Ib8w047THupL3cdV3e96SpSwrK9WXsMKGLRGzJN0VdixPpILUsTK+G40oCNqpz0rd62bSb2aIJnFZMqLEKymYBhpqrTjkx7wP09asVpooOld0zV9ey3/1u85FSy4EVT8DcEmcUzxDKviA3zDuipGw+vuvWt41/3x9a0LCGogquRmVA0ILjdDDMh3V68voI6AlhryI+aI2vN3L1MVU/y808GnolozV44ghVDzc0NifsRlarO+zWu4pAGnnIhbdBaaLJ0G5jGAYLdqAk13YsGETp3KwL47zZllTUyqaOpBhp3HxNXqsfxG0obCQpMX08ZnYsny7L59/xN28eQrdalnpxq6HWZh2y3qBOYt7oeFJJkukOxucMVQ+ajg1Jrq8ge02anhJXk2MA03pKCaW1IsoNABes0K+eLKw3cFKrAGwKr/HlmJawecEnhAgQsX2sAYOc1NYFCvM0b2W/QNQiHnUiu+yswx7/zL2sgO/WAKZWIW6625CmNeg/OOLIgXpxu9cpDoK1963Uqp8M13aqq11NUOkPYf5r2KnBwcVUZIxYcMEkm1P0swdTjOGI+sFlUVAhTOHvaKFfVQPchKdw7vkf8KBhAM7Gge0je9dtFAsU0cZpg02+gBpbC9sLfYyYv4ouUGRr21LpF8AWyaOAcTA2X7p18Q2JsNRSCnE75CslLaQKn41SGk4FNTQtIQUlkYCfIYJhB/LA1LjRJWgg+kkdwkHEg60nANuPuPe2FuQojrzWiQZdhkIODJJAg1D7EGgihO2AaKAacVQ0VlLIzWITQKLiUhWcZpAVUgPaet42wiGqrEoStSglnkvi99qD2FccZ5Yt0c88Qk/N8fmGfEWKYMJJ3cJBxIO7EgccLyOjvWOHSySMMQaoyxtXvocDelGNng+GCAwyiwzzEOppWvVr/pbK3MqetCDDaZnwFCAOJi+oYXnOrSb91rcywEFLI/I9PX0PIjc9lmRKemqeUO6VmYwiSvhQMKBsThgpv1/tG7dL9/xzndxhIbzTiQtV8M5gKkUgkMNSSWUXgRoo/3GSnu830dLM/4Wxi2JKqSR/0hVKOqZCMhgKpFisfoTKMLOXL5w/m8Aq8jIcbwkJeESDiQc2B4cMKe2KiEMJ7tY6X82ivU/aEGjDEQlidDQay252R60TTwNJC0QS6Cl88tTmEdkpHvr6+37tus671/G8exVichf4hIOJBzYgTmQ0qyY1pPp+qN1d99/xFFHflML+ZllewOSSDsmBTp6vghwKRut0UltP4YYsAWA0+yFo03zfgEqXczWJ3MXd3U9Lclq3bp11hFHHJGA1fYrkySlhAPj5oBBIYVW45VlbLxFzFxvyaGYep6P1HUywOWwWFPeirRsnbYyEE4vdzQHjVqwWMEqP6OtTdid4EmGu2vYgvk/vNmzzeLPRjYb3NHyldCTcODlzoERwDN8bSArs09jFHUe+1YcrC1U2A1AYKBZQpY67lgOoNJMgc6E00k8Vh8oi7XrVzgjehUzgb8RtQCzq9nARMG+Y5VdQk3CgUY4MAKwFEjSFheXa7gCfeXKXfq39JzF1OEnAIK9pZTHfknfpLSvv16Pj9vLAVbaOzzFgaza3VI7UbLXU+rKFQsv/k5Egw0Yu3W2wdleZCbpJBxIODABDtQErDg+ratDt6VFtWaRJhvO7+fblU/y/V8YJk7S5l3ouARcWgX+t9JviTbZVKGn0m6K/b9lxLpq90nt/6lTovkmAM6wO4EkL3AtcQkHEg68WDkwKmDFmYqGiQYY9K5j8eJ3MX94PifamnP4MBHQUEwnAmhzrobijOOewFXD0jL7ZnE4S1a7SD7L8O+LbLu5Jj4BRnRvnDrVH8/ePxOgKwmacCDhwDbiQMPgwkyhtlHR1i7hMBGppd92Z2AO/ym2c3mbjlxiKFbCn6zMt+UwUVKSwDOdz7dZfX29PkB1A0sZVy3v6rpffIJGh10cB7Zz1bvEJRxIOPDi50DDgBVnVTt8Tr3rLife29nzLntln1M4E6XRubm2tmk6QYMTZY0+iTCtGyaGy2pYoW3ZSFQytUCPVlzHxporly1YcHtMn4Z//GTOkAz/YqYk14QDLxEONA1Ycb5lFhAdzmnWFXJ6zHS/VDkPlDgTxXfamEFgdAqw6NjwcafDMFPAozR8lg5pk32ZKTzMKpwr/Gz6K/GJKRr+sZmgtuY19MR0JteEAwkHXjocGD+QGB4EzLytHjLzNq/7kqPYwOoijhc6Vnu/I3EZsAF1tB9W4+mFEpUBK5TlLiDIXtL9mzBc+AI6/i+s8DrWiwQZvWo3TYDKTAy8dIomyUnCgYQDwznQOIAMD1n1DFgM0RldddVV2ac3bj6NzalmZ7K5/XVmGqYQ5pjeSMc1VroG5JCuUlomVGJ/f0wqvppO2ZejUP95nHRk/DkwGRC/T64JBxIOvDQ5MBZwNJVrgMvFWl7HyJvd89ghdHe/bJ3Dnp7ncuLtruyzrn2qBDChbgvRycwqIk1JJwUxkqj049A3jlfjc6lYuo9VNSvQU90WEzN8OBq/T64JBxIOvLQ50FLAilil4Zk5ty9mHdbyB7Ih7KcBpTOwQXDMUd0660cuNoMIh4A2mwM60lMV+/vXM4hcuUsmc/Xc6GRfKfwVJDFTEBcSl3Dg5ceBbQFYhosaJnKjbVsGdEvYbx3LRqAdqZT7Tp2+oz24tOc7u57qyHZGgLbFEV09HBv35Vw2dYXX2fm4IgPoZFIxBAT1PnEJBxIOvLw4sM0AK2ajlOK6j4eJK1euzD+3pfeDHPxwAmPA/ZCopoFI/ZyB8Rh4dT9qq5uWdc37SRwewHNfhGfoxeQn14QDCQdayIH/D/Z5iHCPpZRyAAAAAElFTkSuQmCC\">
                </center>
            </div>
        </body>
        </html>";
    } >/var/www/html/index.php

    # Configuring Lighttpd in HHVM
    clear;
    echo "Configuring Lighttpd in HHVM...";
    echo "=================================";
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
    echo "Installing Percona Server..."
    echo "=================================";
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
    clear;
    
    # Installing PowerDNS
    clear;
    echo "Installing PowerDNS..."
    echo "=================================";
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

    # Set Root Password for Percona
    sudo /etc/init.d/mysql stop
    #mysqld_safe --skip-grant-tables & mysql -u root -e "USE mysql; UPDATE user SET authentication_string=PASSWORD(\"Metabee5\") WHERE User='root'; FLUSH privileges;"
    mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$PERCONA_ROOT_PASSWORD';";

elif [ "${option}" = "2" ]; then
    echo "Not Implemented";
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
echo "|   Percona Root Password: $PERCONA_ROOT_PASSWORD                              |";
echo "|                                                                  |";
echo "|   PowerDNS Database User: powerdns                               |";
echo "|   PowerDNS Database Name: powerdns                               |";
echo "|   PowerDNS Database Password: $POWERDNS_PASSWORD                         |";
echo "|                                                                  |";
echo "|   You can access to http://$SERVER_HOSTNAME/  ";
echo "|                                                                  |";
echo "|   NOTE: Before restart your server we recommend execute          |";
echo "|   \"mysql_secure_installation\" for secure your Percona Server.    |";
echo "|                                                                  |";
echo "o------------------------------------------------------------------o";
