# Installation (#2)
Rather than using WAMP server, this install guide focuses on installing [Apache from Apache Lounge](https://www.apachelounge.com/download/)

* Install the [latest Visual C++ Redistributable](https://support.microsoft.com/en-us/help/2977003/the-latest-supported-visual-c-downloads)
* download latest [Apache Lounge Distro](https://www.apachelounge.com/download/)
* move the correct folder to `C:\Apache24\`
* test the server with `C:\Apache24\bin\httpd.exe`
  * this only runs the server while this command window is open
* install the server as a service
  * open a command prompt as administrator
  * `C:\Apache24\bin\httpd.exe -k install`
  * use the apache service monitor (as administrator) to control it (`C:\Apache24\bin\ApacheMonitor.exe`)
