<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|
| Please read Licence_CeCILL_V2-en.txt
| SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
*/

defined('PROTECT') OR die("Tentative de Hacking");

class Core
{
	static function setup()
	{
		static $run;

		// This function can only be run once
		if ($run === TRUE)
			return;
		
		error_reporting(E_ALL);
		spl_autoload_register(array(get_class(), 'auto_load'));
		set_magic_quotes_runtime(0);
		
		$run = TRUE;
	}
	
	static function auto_load($class)
	{
		global $root;
		
		if (class_exists($class, FALSE))
			return TRUE;

		$search_list = array(UPLOAD_PATH, $root);
		foreach($search_list as $path)
		{
			$file = $path.'/class/class_'.$class.'.php';
			$file = strtolower($file);
			if (file_exists($file))
			{
				include_once($file);
				break;
			}
		}
		return class_exists($class, FALSE);
	}

}

?>