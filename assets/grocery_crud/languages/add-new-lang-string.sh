#!/bin/sh
for i in *.php
do
   echo "\n" >> $i
   echo "\t/* Added in version 1.5.6 */" >> $i
   echo "\t\$lang['list_search_column'] = 'Search {column_name}';\n" >> $i
done