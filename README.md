# SWHLabPHP
SWHLabPHP is a cross-platform PHP-centric data browser designed to provide a web front-end to easily explore electrophysiology project data (analyzed by SWHLab) residing on a local network. This software was written with emphasis on [ABF files](http://mdc.custhelp.com/app/answers/detail/a_id/18881/~/axon%E2%84%A2-pclamp%C2%AE-abf-file-support-pack-download-page) and microscopy file formats (fluorescent micrographs, time series, 3d stacks, etc) but can be easily modified to allow browing of other file formats. The intended use of this software is to allow quick browsing of data served on the local network in real time as experiments are being performed. SWHLabPHP assumes you are running a modern version of Apache and PHP.

### Screenshot / Mock-up
![](design/mockups/frames.jpg)

## Windows 10 setup
I developed this on Windows 10 by installing [WAMP server](https://sourceforge.net/projects/wampserver/) (version [3.0.6](https://www.google.com/search?q=wampserver3.0.6_x64_apache2.4.23_mysql5.7.14_php5.6.25-7.0.10.exe)). After installation, I made a few modifications to core apache files:

**httpd-vhosts.conf** modified to change port number and allow more than local access (allowing web server to be accessed over LAN)

```
<VirtualHost *:8080>
	ServerName localhost
	DocumentRoot c:/wamp64/www
	IndexOptions NameWidth=*
	<Directory  "c:/wamp64/www/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require all granted
	</Directory>
</VirtualHost>
```

**httpd.conf** modified to change port number and force serving on the LAN ip (192.x.x.x) and not the department network (10.x.x.x)
```
...
#ServerName localhost:8080
ServerName 192.168.1.225:8080
...
#Listen 12.34.56.78:80
#Listen 0.0.0.0:80
#Listen [::0]:80
#Listen 192.168.1.225:80
Listen 192.168.1.225:8080
...
```

## Developing with GitHub
Close this package into `C:\wamp64\www\SWHLabPHP`
