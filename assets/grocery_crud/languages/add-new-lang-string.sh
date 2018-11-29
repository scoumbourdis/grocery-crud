#!/bin/sh
for i in *.php
do
   echo "\n" >> $i
   echo "\t/* Added in version 1.6.1 */" >> $i
   echo "\t\$lang['list_clone'] = 'Clone';\n" >> $i
done