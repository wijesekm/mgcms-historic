#!/bin/sh

TMPDIR=/tmp
USER=root
GROUP=apache
echo "Enter the directory for the Mandrigo Core to be installed to (ex /var/www/localhost/htdocs/).";
read COREDIR
echo "Enter the directory for the Mandrigo Admin to be installed to (ex /var/www/localhost/htdocs/).";
read ADMINDIR
echo "Enter the directory where plugin files will be placed (ex /var/www/localhost/htdocs/inc/packages/).";
read PACKAGEDIR
echo "Enter the directory where log files will be placed (ex /var/www/localhost/logs/).";
read LOGDIR
echo "Enter the directory where template files will be placed (ex /var/www/localhost/templates/).";
read TPLDIR

echo "Installing the display manager of Mandrigo CMS\n";
echo "Checking to see if $COREDIR exists.  If not will build directory tree.\n"
./mkinstalldirs.sh $COREDIR
echo "Checking to see if $PACKAGEDIR exists. If not will build directory tree.\n"
./mkinstalldirs.sh $PACKAGEDIR
echo "Checking to see if $LOGDIR exists. If not will build directory tree.\n\"
./mkinstalldirs.sh $LOGDIR
echo "Checking to see if $TPLDIR exists. If not will build directory tree.\n"
./mkinstalldirs.sh $TPLDIR

echo "Installing Mandrigo Core\n"
#####Extract and copy files
#tar -xzf mandrigo_core.tar.gz -C $TMPDIR
tar -cjf mandrigo_core.tar.bz2 -C $TMPDIR
cp -r $TMPDIR/mandrigo_core/* $COREDIR

###secures files and directories
echo "Securing Files and Directories\n";
chown -R $USER:$GROUP $TPLDIR
chown -R $USER:$GROUP $LOGDIR
chown -R $USER:$GROUP $PACKAGEDIR
chown -R $USER:$GROUP $COREDIR

chmod -R 0755 $COREDIR
chmod -R 0755 $PACKAGEDIR
chmod -R 0775 $TPLDIR
chmod -R 0775 $LOGDIR

echo \<IfModule mod_access.c\> > $LOGDIR/.htaccess
echo Deny From All >> $LOGDIR/.htaccess
echo \</IfModule\> >> $LOGDIR/.htaccess
echo \<IfModule mod_access.c\> > $TPLDIR/.htaccess
echo Deny From All >> $TPLDIR/.htaccess
echo \</IfModule\> >> $TPLDIR/.htaccess
echo \<IfModule mod_access.c\> > $PACKAGEDIR/.htaccess
echo Deny From All >> $PACKAGEDIR/.htaccess
echo \</IfModule\> >> $PACKAGEDIR/.htaccess
echo \<IfModule mod_access.c\> > $COREDIR/inc/.htaccess
echo Deny From All >> $COREDIR/inc/.htaccess
echo \</IfModule\> >> $COREDIR/inc/.htaccess
echo \<IfModule mod_access.c\> > $COREDIR/config/.htaccess
echo Deny From All >> $COREDIR/config/.htaccess
echo \</IfModule\> >> $COREDIR/config/.htaccess

echo "Cleaning up after install.\n\n"
rm -r /tmp/mandrigo*

echo "Installing Mandrigo CMS Admin\n";
#tar -xzf mandrigo_admin.tar.gz -C $TMPDIR
tar -cjf mandrigo_admin.tar.bz2 -C $TMPDIR
cp -r $TMPDIR/mandrigo_core/* $ADMINDIR

echo "Securing Files and Directoreis\n";
chown -R $USER:$GROUP $ADMINDIR
chmod -R 0755 $ADMINDIR

exit 0

