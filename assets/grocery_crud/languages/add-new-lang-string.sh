#!/bin/sh
for i in *.php
do
   echo -e "\n" >> $i
   echo -e "\n\t\$lang['ui_day'] = 'dd';" >> $i
   echo -e "\n\t\$lang['ui_month'] = 'mm';" >> $i
   echo -e "\n\t\$lang['ui_year'] = 'yyyy';" >> $i
done
