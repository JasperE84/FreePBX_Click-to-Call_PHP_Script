# FreePBX_Click-to-Call_PHP_Script
A PHP script for installation on a FreePBX server to provide a Click-To-Call WebAPI.
The script shows a JSON result.

Steps to implement:
- Place in /var/www/html/ 
- Configure the correct password, fetch from /etc/asterisk/manager.conf
- Open in browser: http://YOUR.PBX.IP/freepbx-click-to-call.php?exten=123&number=0612345678
- The extension with number 123 will now ring, when picked up, FreePBX will call 0612345678


This project has been partly developed in time donated by [Contour - Sheet metal supplier](https://www.contour.eu/en/)
Dit project is deels ontwikkeld ontwikkeld in de tijd van [Contour - Plaatwerkleverancier](https://www.contour.eu/plaatwerk/)
