<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Citations
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

define('CITATIONS_PATH', dirname(__FILE__));

$id_version = 0;

global $root;

require_once($root.'/class/class_controller.php');

class controller_citations extends Controller
{
	function init()
	{
		global $droits,$module;
		
		require_once(CITATIONS_PATH.'/prerequis.php');
		// Autorisations
		if (!$droits->check($module,0,'voir'))
		{
			error404(1000);
		}
	}
	
	function getActions()
	{
        $path = CITATIONS_PATH.'/actions/';
		
        return array(
            'index' 	=> $path.'action_lister',
            'editer'	=> $path.'action_editer',
            'voir'		=> $path.'action_voir',
        );
	}
}

$controller = new controller_citations();
$controller->init();
$controller->run();

/*
include_once($root.'class/class_posting.php');
$post = new posting();
$post->module=$module;
*/

?>