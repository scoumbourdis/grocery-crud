#!/bin/sh
for i in *.php
do
   echo "\n" >> $i
   echo "\t/* Added in version 1.5.8 */" >> $i
   echo "\t\$lang['alert_delete_multiple'] = 'Are you sure that you want to delete those {items_amount} items?';\n" >> $i
   echo "\t\$lang['alert_delete_multiple_one'] = 'Are you sure that you want to delete this 1 item?';\n" >> $i
done