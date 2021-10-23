DAMP - The Developer's Apache Mysql PHP administrative environment on Windows
=================================================================

INTRODUCTION
----------------------------
Through developing many web projects, I found that a suitable environment will affect developer performance so much.
So, an idea, an optimized environment for php development on Windows, is rised. 
This project had some names such as superman 1 click (1.x,2.x), 1-click (3.x), Click (4.x-Jun,2018), DAMP (current).
This project is remaked from 2.0.
Now, this project is hosted on gitlab.com.

FEATURES
----------------------------
- Able to switch Apache, PHP and MySQL versions
- Able to manage Apache and MySQL services
- Integrated common components: ssl, xdebug, balancing, apc, icocube
- Integrated amazing tools: log tool, data sample tool
- Kept directory and file structures clean

COMPONENTS
----------------------------
- Apache 2.2.19 vc9 x86
- Apache 2.4.25 vc11 x86
- Apache 2.4.38 VC14 x86
- MySQL 5.1.57 win32
- MySQL 5.5.58 win32
- MySQL 5.7.22 win32
- PHP 5.3.3 vc9 x86
- PHP 5.6.4 vc11 x86
- PHP 7.1.26 vc14 x86
- Ioncube vc9 x86
- Ioncube vc11 x86
- Ioncube vc14 x86
- Xdebug 2.1.2 vc9
- Xdebug 2.5.3 vc11
- Xdebug 2.7.0 vc14
- Apc vc9 x86 (PHP >= 5.4 use OPCache instead)

INSTALL
----------------------------
- Backup all databases: check server/readme
- Install VC libraries in following order
    * Install Microsoft Visual C++ 2008 Redistributable Package (VC9) for x86
    * Install Microsoft Visual C++ 2012 Redistributable Package (VC11) for x86
    * Install Microsoft Visual C++ 2015 Redistributable Package (VC14) for x86
    * Install Microsoft Visual C++ 2017 Redistributable Package (VC15) for x86
    * Install Microsoft Visual C++ 2019 Redistributable Package (VC16) for x86
    * Install Microsoft Visual C++ 2008 Redistributable Package (VC9) for x64
    * Install Microsoft Visual C++ 2012 Redistributable Package (VC11) for x64
    * Install Microsoft Visual C++ 2015 Redistributable Package (VC14) for x64
    * Install Microsoft Visual C++ 2017 Redistributable Package (VC15) for x64
    * Install Microsoft Visual C++ 2019 Redistributable Package (VC16) for x64
    * Note: uninstall and reinstall in other order if something is not done
    https://support.microsoft.com/vi-vn/help/2977003/the-latest-supported-visual-c-downloads
- Make both ports 80, 443 free to use
	* Check: open Resource Monitor > Network > Listening Ports
    * Quit skype
    * Disable TeamViewer from using port 80
    * Uninstall other softwares (xampp, wamp, appserver, iis, ...)
    * Uninstall old version of DAMP by run command/uninstall.bat
    * Others
	    ~ Change port for VMware Host Agent service
	    ~ Change port Windows Work Folders
	    ~ Disable Windows Services That Listen on Port 80
	    ~ Disable HTTP (HTTP.SYS) Hidden Driver/Service
- Configure
    * Extract DAMP into anywhere you want
    * Create a directory symbolic link C:\server points to path\damp\server
    * Download and extract correct versions of apache, mysql, php. Do not replace the files exist in their directories.
    * Create a directory symbolic link C:\project points to path\damp\project
    * Create a file symbolic link C:\Windows\System32\drivers\etc\hosts linking points to C:\server\hosts
    * Configure system environment 'path' system (not user) variable: append ';C:\server\php;C:\server\mysql\bin;' to end if not exist
- Setup
    * Run command/change_mysql_version.bat (bypass any errors)
    * Run command/change_php_version.bat (bypass any errors)
    * Run command/change_apache_version.bat (bypass any errors)
    * Run command/install.bat
- Test by access http://test.win, https://test.win on the browser
- Try to restart windows if something doesn't run

OTHER
----------------------------
- Require latest versions of IDEs
- If any error occurs, please check error logs or Windows Event Viewer application
- For helps, read server/readme file