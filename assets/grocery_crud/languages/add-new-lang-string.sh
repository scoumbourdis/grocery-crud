for i in *.php
do 
   echo -e "\n\n\t/* Added in version 1.4 */\n\t\$lang['list_view'] = 'View';" >> $i
done