#!/bin/sh

USER="root"
GROUP="apache"
VER="0.7.0_dev"

#welcome page
echo "-------------------------------------\n";
echo "Welcome to the mandrigo CMS Installer\n";
echo "Mandrigo CMS version $VER\n";
echo "Copyright 2004-2007 Mandrigo CMS Group\n";
echo "-------------------------------------\n\n";

#license
echo "-------------------------------------\n";
echo "Licence:\n";
echo "	
    This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.\n

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.\n\n";
echo "Please enter in y if you read and understand this agreement or n if you do not\n";

read LICENSE

if [ "$LICENSE" -ne "y" ]; then
	echo "Installer halted";
	exit 0;
fi
echo "-------------------------------------\n\n";

#package selection
echo "-------------------------------------\n";
echo "Package Selection\n";
echo "Please enter in y if you want to install each of the packages given or n if you do not\n";
echo "Options:\n";
echo "1 - core\n";
read ICORE
echo "2 - admin\n";
read IADMIN
echo "3 - login_manager\n";
read ILOGIN
echo "4 - packages\n";
read IPACKAGES

echo "-------------------------------------\n\n";

echo "-------------------------------------\n";
echo "Path Selection\n";
echo "Web Directory Path (ie /var/www/htdocs/\n";
read CWEBDIR
if [ "$IADMIN" -eq "y" ]; then
	echo "Admin Directory Path (ie /var/www/htdocs/admin/\n";
	read AWEBDIR
fi
if [ "$ILOGIN" -eq "y" ]; then
	echo "Login Manager Directory Path (ie /var/www/htdocs/login_manager/\n";
	read LWEBDIR
fi
echo "External Directory Path (ie /var/www/)\n";
read EXTERNALDIR
echo "TMP Directory Path (ie /tmp/)\n";
read TMPDIR
echo "-------------------------------------\n\n";

echo "-------------------------------------\n";
echo "Install Section\n";

./install_tools/mkinstalldirs.sh $TMPDIR/mandrigo/admin
./install_tools/mkinstalldirs.sh $TMPDIR/mandrigo/login_manager
./install_tools/mkinstalldirs.sh $TMPDIR/mandrigo/packages

if [ "$TMPDIR" -ne "/tmp/" ]; then
	chmod -R 0777 $TMPDIR
fi

echo "Installing Mandrigo core...";
tar -xzf mandrigo-core.tar.gz -C $TMPDIR/mandrigo/
./install_tools/mkinstalldirs.sh $CWEBDIR
cp -r $TMPDIR/mandrigo/www/ $CWEBDIR
./install_tools/mkinstalldirs.sh $EXTERNALDIR
cp -r $TMPDIR/mandrigo/templates $EXTERNALDIR
cp -r $TMPDIR/mandrigo/logs $EXTERNALDIR
echo "done\n";

echo "Securing Mandrigo core...";
chown -R $USER:$GROUP $CWEBDIR
chown -R $USER:$GROUP $EXTERNALDIR
chmod -R 0755 $CWEBDIR
chmod -R 0775 $CWEBDIR/config/
chmod -R 0775 $CWEBDIR/images/
chmod -R 0774 $EXTERNALDIR
echo "done\n";

if [ "$IADMIN" -eq "y" ]; then
	echo "Installing Mandrigo admin...";
	tar -xzf mandrigo-admin.tar.gz -C $TMPDIR/mandrigo/admin/
	./install_tools/mkinstalldirs.sh $AWEBDIR
	cp -r $TMPDIR/mandrigo/admin/ $AWEBDIR/
	echo "done\n";
	
	echo "Securing Mandrigo admin...";
	chown -R $USER:$GROUP $AWEBDIR
	chmod -R 0755 $AWEBDIR
	chmod -R 0775 $AWEBDIR/config/
fi

if [ "$ILOGIN" -eq "y" ]; then
	echo "Installing Mandrigo login_manager...";
	tar -xzf mandrigo-login_manager.tar.gz -C $TMPDIR/mandrigo/login_manager/
	./install_tools/mkinstalldirs.sh $LWEBDIR
	cp -r $TMPDIR/mandrigo/login_manager/ $LWEBDIR/
	echo "done\n";
	
	echo "Securing Mandrigo login_manager...";
	chown -R $USER:$GROUP $LWEBDIR
	chmod -R 0755 $LWEBDIR
	chmod -R 0775 $LWEBDIR/config/
fi

if [ "$IPACKAGES" -eq "y" ]; then
	echo "Installing Mandrigo packages...";
	tar -xzf mandrigo-login_manager.tar.gz -C $TMPDIR/mandrigo/packages/
	cp -r $TMPDIR/mandrigo/packages/ $CWEBDIR/inc/packages/
	echo "done\n";
	
	echo "Securing Mandrigo packages...";
	chown -R $USER:$GROUP $CWEBDIR/inc/packages/
	chmod -R 0775 $CWEBDIR/inc/packages/
	echo "done\n";
fi

echo "Cleaning up after install...";
rm -r $TMPDIR/mandrigo
echo "done\n";
echo "-------------------------------------\n\n";
echo "Mandrigo CMS successfully Installed\n";
echo "Please go to http://yoursitename/install/ to finish setting up mandrigo";
exit 0;