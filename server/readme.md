LIST BASIC PACKAGES
------------------------------------------------------------------------------------------------------------------------
httpd-2.2.19-win32-x86-openssl-0.9.8r
php-5.3.3-Win32-VC9-x86
mysql-essential-5.1.57-win32
httpd-2.4.25-win32-VC11
php-5.6.4-Win32-VC11-x86
mysql-5.5.58-win32
and other modules


HOW TO ADD NEW APACHE VERSIONS
------------------------------------------------------------------------------------------------------------------------
https://www.apachelounge.com/download/
http://www.josephspurrier.com/run-apache-http-on-windows-without-installing/
https://www.legroom.net/software/uniextract
https://superuser.com/questions/307678/how-do-i-extract-files-from-an-msi-package/788227
check system architecture: x86, x64, win32, win64
check MSVC runtime version: VC9, VC11, VC14, VC15
download new apache version
extract to C:\server
edit conf/*.* and conf/extra/*.* base difference between versions of the files of other apache versions. Use git diff or svn diff or any file-diff tools
create change_apache_version-* for new version


HOW TO RECOVER DATABASES
------------------------------------------------------------------------------------------------------------------------
https://dev.mysql.com/doc/refman/5.7/en/forcing-innodb-recovery.html
entry recovery mode: innodb_force_recovery = ... (0 -> 6)
backup some databases
back to fresh mode
restore some databases


HOW TO ADD NEW MYSQL VERSIONS WITHOUT INSTALLATION
------------------------------------------------------------------------------------------------------------------------
http://mysqlserverteam.com/upgrading-directly-from-mysql-5-0-to-5-7-using-an-in-place-upgrade/
https://dev.mysql.com/doc/refman/5.7/en/windows-install-archive.html
https://stackoverflow.com/questions/42045494/running-starting-mysql-without-installation-on-windows
https://stackoverflow.com/questions/12071834/installing-mysql-noinstall-zip-archive
https://stackoverflow.com/questions/3456159/how-to-shrink-purge-ibdata1-file-in-mysql
https://downloads.mysql.com/archives/community/
check system architecture: x86, x64, win32, win64
download https://dev.mysql.com/get/Downloads/MySQL-5.5/mysql-5.5.58-win32.zip
extract to C:\server
copy my.ini from other mysql versions
check other configurations in my.ini
in-place upgrade vs dump upgrade
create change_mysql_version-* for new version
run mysql_upgrade to finish the upgrade

Error: Table 'performance_schema.session_variables' doesn't exist
Solution:
    solution 1:
    mysql -u root -p
    set @@global.show_compatibility_56=ON;
    
    solution 2:
    mysql_upgrade -u root -p --force
    systemctl restart mysqld

Delete ibdata1 and ib_logfile* files
When you start MySQL the ibdata1 and ib_log files will be recreated.


HOW TO ADD NEW MYSQL VERSION WITH MSI VERSIONS
------------------------------------------------------------------------------------------------------------------------
uninstall mysql services
check system architecture: x86, x64, win32, win64
download https://dev.mysql.com/get/Downloads/MySQL-5.5/mysql-5.5.58-winx64.msi
install by msi version
stop mysql service
backup mysql directory
uninstall mysql
copy to C:\server
edit my.ini base my.ini from other mysql versions
check other configurations in my.ini
in-place upgrade vs dump upgrade
create change_mysql_version-* for new version
run mysql_upgrade to finish the upgrade

HOW TO ADD NEW PHP VERSIONS
------------------------------------------------------------------------------------------------------------------------
check system architecture: x86, x64, win32, win64
check MSVC runtime version: VC9, VC11, VC14, VC15
check Thread Safe or Non-Thread Safe: empty, ts, nts
access http://windows.php.net/downloads/releases/archives/
download new php version
download extensions:
    memcache: https://github.com/nono303/PHP7-memcache-dll
    xdebug: https://xdebug.org/download/historical
    ioncube: https://www.ioncube.com/loaders.php
    imagick: https://mlocati.github.io/articles/php-windows-imagick.html
extract them to C:\server and C:\server\ext
copy php.ini-development to php.ini
edit php.ini base difference between php.ini-development and php.ini of other php versions. Use git diff or svn diff or any file-diff tools
copy httpd-php.conf from other php version and edit if need
create change_php_version-* for new version

HOW TO ADD NEW PHP MODULE VERSIONS
------------------------------------------------------------------------------------------------------------------------
check system architecture: x86, x64, win32, win64
check MSVC runtime version: VC9, VC11, VC14, VC15
check Thread Safe or Non-Thread Safe: empty, ts, nts
check PHP version


HOW TO BACKUP/COPY/MOVE MYSQL DATABASES
------------------------------------------------------------------------------------------------------------------------
https://rtcamp.com/tutorials/mysql/enable-innodb-file-per-table/
http://dev.mysql.com/doc/refman/5.0/en/innodb-multiple-tablespaces.html
https://rtcamp.com/tutorials/mysql/myisam-to-innodb/
http://dev.mysql.com/doc/refman/5.0/en/mysqldump-definition-data-dumps.html


HOW TO ENABLE INNODB FILE PER TABLE OR BACKUP/COPY/MOVE SEVERAL DATABASES BY DUMP TOOL
-------------------------------------------------------------
Export some tables
	mysqldump -hlocalhost -uroot  -proot --force --routines --events --flush-privileges --add-drop-table=false --comments=false --create-options=true --extended-insert=true --set-charset=true --dump-date=false --lock-tables=false --add-locks=false --compact=false --no-data=false --disable-keys=false database1  table1 table2 | gzip > database1_tables.sql.gz
Export a database
	mysqldump -hlocalhost -uroot  -proot --force --routines --events --flush-privileges --add-drop-table=false --comments=false --create-options=true --extended-insert=true --set-charset=true --dump-date=false --lock-tables=false --add-locks=false --compact=false --no-data=false --disable-keys=false database1  | gzip > database1.sql.gz
Import a database
	mysql -hlocalhost -uroot -proot database1 --force < "C:\database1.sql"
Backup all databases exclude some (recommended)
	mysql -uroot -proot --skip-column-names --execute="SELECT schema_name FROM information_schema.schemata WHERE schema_name NOT IN ('mysql','information_schema','performance_schema','magento17','test');"  | awk '{print}' ORS=' '
	mysqldump -uroot  -proot --force --routines --events --flush-privileges --add-drop-table=false --comments=false --create-options=true --extended-insert=true --set-charset=true --dump-date=false --lock-tables=false --add-locks=false --compact=false --no-data=false --disable-keys=false --databases database1 database2 > several-dbs.sql
Backup all databases
	mysqldump -uroot  -proot --force --routines --events --flush-privileges --add-drop-table=false --comments=false --create-options=true --extended-insert=true --set-charset=true --dump-date=false --lock-tables=false --add-locks=false --compact=false --no-data=false --disable-keys=false --all-databases > all-dbs.sql
Drop Databases: mysql  -uroot -proot -e "SELECT DISTINCT CONCAT ('DROP DATABASE ',TABLE_SCHEMA,' ;') FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA <> 'mysql' AND TABLE_SCHEMA <> 'information_schema';" | tail -n+2 > drop.sql
	mysql -uroot -proot < drop.sql
	SELECT table_name, table_schema, engine FROM information_schema.tables WHERE engine = 'InnoDB';
Remove InnoDB files
	stop mysql service: service mysql stop
	rm /d/server/data/ibdata1 /d/server/data/ib_logfile0 /d/server/data/ib_logfile1
	rm *.log
	rm *.err
Enable innodb_file_per_table
	vim /etc/mysql/my.cnf
	innodb_file_per_table = 1
	innodb_file_format = barracuda
Import from mysqldump
	start mysql service: service mysql start
	mysql -uroot -proot --force < several-dbs.sql
	mysql_upgrade --force
	rm drop.sql all-dbs.sql
	
	
HOW TO MOVE A INNODB TABLE DATA TO OTHER DATABASE WITH MODE INNODB_FILE_PER_TABLE ENABLED
-------------------------------------------------------------
Case 1: if you have a “clean” backup of an .ibd file
	ALTER TABLE tbl_name DISCARD TABLESPACE;
	Copy the backup .ibd file to the proper database directory
	ALTER TABLE tbl_name IMPORT TABLESPACE;
Case 2: if you have not, in same mysql instance, to move an .ibd file and the associated table from one database to another, use a RENAME TABLE statement
	RENAME TABLE db1.tbl_name TO db2.tbl_name;
Note: A innodb table has two files but a MyISAM table is stored on disk in three files

	
HOW TO COPY/MOVE A INNODB DATABASE (STRUCTURE AND DATA) TO OTHER SERVER WITH MODE INNODB_FILE_PER_TABLE ENABLED
-------------------------------------------------------------
On old server
	+ create database definition
		mysqldump -hlocalhost -uroot  -proot --force  --no-data --routines --events --flush-privileges --add-drop-table=false --comments=false --create-options=true --extended-insert=true --set-charset=true --dump-date=false --lock-tables=false --add-locks=false --compact=false --no-data=true --disable-keys=false test  | gzip > test-definition.sql.gz
	+ stop service
	+ back up database binary files
	+ start service
On new server
	+ create new database with same name
	+ run database definition sql
	+ stop service
	+ replace with the database binary files
	+ start service


OTHERS
------------------------------------------------------------------------------------------------------------------------
if apache services has problems, please check in references:
Apache 2.4 readme
httpd.apache.org/docs/2.4/upgrading.html
httpd.apache.org/docs/2.4/new_features_2_4.html
keyword: archive {detail of package version}
https://support.microsoft.com/en-us/help/2977003/the-latest-supported-visual-c-downloads
https://www.apachelounge.com/download/
https://windows.php.net/downloads/releases/archives/
https://xdebug.org/files/
https://www.ioncube.com/loaders.php
https://github.com/nono303/PHP7-memcache-dll