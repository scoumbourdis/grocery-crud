<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

error_reporting(E_ALL|E_STRICT);
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
#include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
#include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';

class Elfinder_lib {

	function __construct($opts)
	{
		$connector = new elFinderConnector(new elFinder($opts), true);
		$connector->run();
	}
}


/* End of file Elf_lib.php */
/* Location: ./application/libraries/elfinder_lib/Elfinder_lib.php */
