#!/bin/sh
for i in *.php
do
   echo "\n" >> $i
   echo "\t/* Added in version 1.5.2 */" >> $i
   echo "\t\$lang['list_more'] = 'More';\n" >> $i
done
