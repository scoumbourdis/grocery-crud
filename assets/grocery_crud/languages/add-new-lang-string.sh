#!/bin/sh
for i in *.php
do 
   echo -e "\n\t\$lang['list_view'] = 'View';" >> $i
done
