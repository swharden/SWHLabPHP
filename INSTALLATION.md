# Server Installation From Scratch

## Install Apache
* Install the [latest Visual C++ Redistributable](https://support.microsoft.com/en-us/help/2977003/the-latest-supported-visual-c-downloads)
* download latest [Apache Lounge Distro](https://www.apachelounge.com/download/)
* move the correct folder to `C:\Apache24\`
* disable the IPV6 protocol on the LAN network adapter
* test the server with `C:\Apache24\bin\httpd.exe`
  * this only runs the server while this command window is open
  
### Configure Apache as a Service
* open a command prompt as administrator
* `C:\Apache24\bin\httpd.exe -k install`
* open windows services and ensure it is set to start automatically
* right-click the service, properties, Log On (tab), and make the account `.\LabAdmin`

### Map `\\Spike\X_Data\Data\` to `/dataX/`
We don't want clients to manually fetch individual files (i.e., images) from the X-Drive because the SMB protocol is painfully slow. Instead, let Apache load these files and serve them over HTTP. This gets performance benefits of server-side caching. Edit `C:\Apache24\conf\httpd.conf` to add an alias and virtual directory:
```
Alias /dataX "//spike/X_Drive/Data/"	
<Directory "//spike/X_Drive/Data/">
   Options Indexes FollowSymLinks MultiViews
   AllowOverride all
   Require all granted
   Order Allow,Deny
   Allow from all
</Directory>
```


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

## Maximum Execution Time
By default, PHP will kill pages which take more than 120s to load. If your page is doing complex things (like calling python to analyze dozens of ABFs), you may want to extend this time to 10m (600s). Edit php.ini to reflect:

```max_execution_time = 600```

## Install Imagemagik
This is required for TIF -> JPG conversion
[download](https://www.imagemagick.org/script/download.php#windows)
