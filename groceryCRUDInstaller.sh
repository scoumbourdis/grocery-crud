#! /bin/bash
# bash script for installing grocery CRUD into a codeIgniter installation

# these are the flags that this script will use to enable/disable the options passed in from the switches
install_stable="false"
install_nightly="false"
set_paths="false"

# with these default PATHS you can run this script from the root of a codeIgniter Project
# and not have to worry about explicitly setting the paths
codeIgniter_base_path="./"
temp_folder_path='tempFolder'

# if the script was not passed in any switches display the usage statment
if [ $# -lt 1 ]
then
	error="1"
else
	error="0"
fi

# loop through the switches and set the flags
for a in $@
do
	if [ $a == "-stable" ]
	then   
		install_stable="true"
	elif [ $a == "-nightly" ]
	then
		install_nightly="true"
	elif [ $a == "-paths" ]
	then
		set_paths="true"
	else
		# if an invalid switch was given show the usage statment
		error="1"
	fi
done

#check to make sure either nightly or stable was selected atlest one and not both
if [ install_nightly == "true" ] && [ install_stable == "true"]
then
	echo "you must select either the stable release or the nightly build"
	error="1"
elif [ install_nightly == "false" ] && [ install_stable == "false"]
then
	echo "you must select either the stable release or the nightly build"
	error="1"
fi

echo ""

if [ $error -eq "1" ] # this is the usage statment
then
		echo "Usage: groceryCRUDInstaller [-stable] [-nightly] [-paths]"
		echo "	           -stable | downloads, extracts and installs grocery CRUD"
		echo "	          -nightly | downloads, extracts and installs the most current version of grocery CRUD repo files from gitHub"
		echo "	            -paths | prompts user to enter paths exclicitly for CodeIgniter install base and temp folder"
		echo ""
		echo "please note the order of the args does not matter"
		echo "no need to use -paths if the script is in the root of you codeIgniter Proj"
		echo ""
		exit 1
fi

if [ $set_paths == "true" ]
then
	echo ""
	
	# get the users input for the path of their codeIgniter Project
	# the only validation done is to insure the path is a directory
	while [ true ]
	do
		clear
		echo "please enter the the path of your codeIgniter project that you wish to install groceryCRUD"
		echo "example: /var/www/my_project/ or ~/public_html/my_project"
		read codeIgniter_base_path
		if [ -d $codeIgniter_base_path ]
		then
			break
		fi
		
		clear
		echo $codeIgniter_base_path
		echo "that is not a valid directory, please try again press ctr-c to exit"
	done
	
	# get the users input for the path of their chose the use for the temp folder
	while [ true ]
	do
		clear
		
		echo "please enter the name for the temporary files folder"
		echo "which will be located in the same folder this script"
		echo "example: /var/www/my_project/tempFolder or ~/public_html/my_project/tempFolder"
		
		read temp_folder_path
		
		if [ ! -d $temp_folder_path ]
		then
			break
		fi
		
		clear
		
		echo $temp_folder_path
		echo "That folder all ready exists and this script will permanantly delete it"
		echo "type \"CONITINUE\" to use it anyways or press enter to try different folder"
		
		read ans
		
		if [ $ans == "CONTINUE" ]
		then
			break;
		fi
		
	done
	
fi


echo -e "-------------------[ downloading grocery CRUD ZIP ]----------------------"
echo ""
echo "please note that this link has to manually update upon new stabe releases and maybe occasionally out of date"
echo ""

# if the temp folder allready remove it and then recreate it
# user has been notified of automatic deletation of this folder
if [ -d $temp_folder_path ]
then
	rm -fr $temp_folder_path
fi

mkdir $temp_folder_path

if [ $install_stable == "true" ]
then
	wget https://github.com/downloads/scoumbourdis/grocery-crud/grocery-CRUD-1.2.2.zip -O $temp_folder_path/grocery-CRUD.zip
elif [ $install_nightly == "true" ]
then
	wget https://github.com/scoumbourdis/grocery-crud/zipball/master -O $temp_folder_path/grocery-CRUD.zip
fi

echo ""
echo -e "-------------------[ unziping grocery CRUD ZIP ]----------------------"
echo ""

if [ $install_stable == "true" ]
then
	unzip -d $temp_folder_path $temp_folder_path/grocery-CRUD.zip
elif [ $install_nightly == "true" ]
then
	unzip -d $temp_folder_path $temp_folder_path/grocery-CRUD.zip
	current_dir=`pwd`
	cd $temp_folder_path/scoumbourdis*
	mv * ../
	cd current_dir
fi

echo ""
echo -e "-------------------[ copying grocery CRUD files to codeIgniter installation ]----------------------"
echo ""

# copy the config files
echo "copying confing files"
cp $temp_folder_path/application/config/grocery_crud.php $codeIgniter_base_path/application/config/
echo "done"

# copy the library files
echo "copying library files"
cp $temp_folder_path/application/libraries/grocery_crud.php $codeIgniter_base_path/application/libraries/
cp $temp_folder_path/application/libraries/image_moo.php $codeIgniter_base_path/application/libraries/
echo "done"

# copy the model files
echo "copying model files"
cp $temp_folder_path/application/models/grocery_crud_model.php $codeIgniter_base_path/application/models/
echo "done"

# if the assets folder doest not exist create it and copy index.html
if [ ! -d $codeIgniter_base_path/assets/ ]
then
	echo "creating assets folder and copying index.html"
	mkdir $codeIgniter_base_path/assets/
	cp $temp_folder_path/assets/index.html $codeIgniter_base_path/assets/
	echo "done"
fi

# copy the actual asset files
echo "copying asset files"
cp -r $temp_folder_path/assets/grocery_crud/ $codeIgniter_base_path/assets/
echo "done"

#removing temp folder
echo "removing temp folder"
rm -fr $temp_folder_path
echo "done"

echo -e "-------------------[ grocery CRUD Installation complete ]----------------------"


