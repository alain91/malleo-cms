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

class Controller
{
	function Controller()
	{
	}
	
	function init()
	{};
	
	function run()
	{
		$actions = $this->getActions();
		
		$mode = null;
		if (isset($_GET['mode']) ||isset($_POST['mode']))
		{
			$mode = (isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
		}
		
		$filename = null;
		if( in_array($mode, array_keys($actions)) )
		{
			$filename = $actions[$mode];
		}
		else
		{
			$filename = $actions['index'];
		}

		require_once($filename.'.php');
		$class = basename($filename);
		$action = new $class;
		$action->run();
	}
	
	function getActions() {};

}
?>