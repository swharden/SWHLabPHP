# Server Installation From Scratch

## Install Apache
* Install the [latest Visual C++ Redistributable](https://support.microsoft.com/en-us/help/2977003/the-latest-supported-visual-c-downloads)
* download latest [Apache Lounge Distro](https://www.apachelounge.com/download/)
* move the correct folder to `C:\Apache24\`
* test the server with `C:\Apache24\bin\httpd.exe`
  * this only runs the server while this command window is open
* install the server as a service
  * open a command prompt as administrator
  * `C:\Apache24\bin\httpd.exe -k install`
  * control it with windows services
  * right-click the service, properties, Log On (tab), and make the account `.\LabAdmin`
  * alternatively use the service monitor (as administrator) `C:\Apache24\bin\ApacheMonitor.exe`

## Limit HTTP Access to the LAN Only
Configure Apache to only to respond to requests from `192.168.1.x` and not `10.x.x.x` IP addresses. Edit `C:\Apache24\conf\httpd.conf` to reflect the following:

```
Listen 192.168.1.109:80
#Listen 80
```

## Install PHP
Download [64-bit thread-safe PHP](http://windows.php.net/download) and extract it to `C:\php\`. Edit `C:\Apache24\conf\httpd.conf` and add these lines at the bottom (you may have to change the .dll filename):
  
```
LoadModule php7_module "C:/php/php7apache2_4.dll"
AddHandler application/x-httpd-php .php
PHPIniDir "C:/php"
```

Also in the same file, modify it so that `index.php` is seen as a directory index:

```
<IfModule dir_module>
    DirectoryIndex index.php
    DirectoryIndex index.html
</IfModule>
```
