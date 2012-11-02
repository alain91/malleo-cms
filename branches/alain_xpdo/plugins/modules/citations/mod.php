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

require_once(CITATIONS_PATH.'/class/class_core.php');
Core::setup();

$id_version = 0;

require_once(CITATIONS_PATH.'/prerequis.php');

class controller_citations extends Controller
{
	function init()
	{
		$this->setPath(CITATIONS_PATH);
	}
	
	function getActions()
	{
        return array(
            'index' 	=> 'actions/action_lister',
            'editer'	=> 'actions/action_editer',
            'supprimer'	=> 'actions/action_supprimer',
        );
	}
}

$controller = new controller_citations();
$controller->dispatch();
?>