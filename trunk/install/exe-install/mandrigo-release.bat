set selfdir=C:\Documents and Settings\wijesek\Desktop
set sourcedir=C:\MandrigoSVN\
set destdir=C:\MandrigoReleases\
set tortoisedir=C:\Program Files\TortoiseSVN\bin\
cd %tortoisedir%

rem TortoiseProc.exe /notempfile /command:checkout /path:"%sourcedir%" /url:"https://mandrigo.svn.sourceforge.net/svnroot/mandrigo/trunk" /closeonend

mkdir %destdir%
mkdir %destdir%\www
mkdir %destdir%\www\config
mkdir %destdir%\www\inc
mkdir %destdir%\www\inc\packages
mkdir %destdir%\templates
mkdir %destdir%\logs
mkdir %destdir%\admin
mkdir %destdir%\admin\templates
mkdir %destdir%\admin\www
mkdir %destdir%\admin\packages\
mkdir %destdir%\admin\packages\mga_language
mkdir %destdir%\admin\packages\mga_package
mkdir %destdir%\admin\packages\mga_users
mkdir %destdir%\admin\packages\mga_pcontent
mkdir %destdir%\login_manager
mkdir %destdir%\login_manager\templates
mkdir %destdir%\login_manager\www
mkdir %destdir%\packages

rem list packages here
mkdir %destdir%\packages\mg_fmail
mkdir %destdir%\packages\mg_gallery
mkdir %destdir%\packages\mg_menu
mkdir %destdir%\packages\mg_news
mkdir %destdir%\packages\mg_newsreader
mkdir %destdir%\packages\mg_pcontent
mkdir %destdir%\packages\mg_profile
mkdir %destdir%\packages\mg_sitemap

rem /
copy /y %sourcedir%\mandrigo\license.txt %destdir%

rem /www
copy /y %sourcedir%\mandrigo\changelog.txt %destdir%\www
copy /y %sourcedir%\mandrigo\index.php %destdir%\www
xcopy /y /s %sourcedir%\mandrigo\config\* %destdir%\www\config
xcopy /y /s %sourcedir%\mandrigo\inc\* %destdir%\www\inc


rem /templates
copy /y %sourcedir%\mandrigo\templates\* %destdir%\templates

rem /logs
copy /y %sourcedir%\mandrigo\logs\* %destdir%\logs

rem /admin
copy /y %sourcedir%\mandrigo\templates\admin\* %destdir%\admin\templates
xcopy /y /s %sourcedir%\admin\* %destdir%\admin\www
copy /y %sourcedir%\mga_language\* %destdir%\admin\packages\mga_language
copy /y %sourcedir%\mga_package\* %destdir%\admin\packages\mga_package
copy /y %sourcedir%\mga_pcontent\* %destdir%\admin\packages\mga_pcontent
copy /y %sourcedir%\mga_users\* %destdir%\admin\packages\mga_users

rem /login_manager
copy /y %sourcedir%\mandrigo\templates\login\* %destdir%\login_manager\templates
xcopy /y /s %sourcedir%\login_manager\* %destdir%\login_manager\www

rem /packages
Xcopy /y /s %sourcedir%\mg_fmail\* %destdir%\packages\mg_fmail
Xcopy /y /s %sourcedir%\mg_gallery\* %destdir%\packages\mg_gallery
Xcopy /y /s %sourcedir%\mg_menu\* %destdir%\packages\mg_menu
Xcopy /y /s %sourcedir%\mg_news\* %destdir%\packages\mg_news
Xcopy /y /s  %sourcedir%\mg_newsreader\* %destdir%\packages\mg_newsreader
Xcopy /y /s  %sourcedir%\mg_pcontent\* %destdir%\packages\mg_pcontent
Xcopy /y /s  %sourcedir%\mg_profile\* %destdir%\packages\mg_profile
Xcopy /y /s  %sourcedir%\mg_sitemap\* %destdir%\packages\mg_sitemap

cd %selfdir%