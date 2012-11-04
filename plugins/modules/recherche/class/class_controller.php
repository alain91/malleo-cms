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
	private $path = false;
	
	function __construct()
	{
		$this->path = dirname(__FILE__);
		$this->init();
	}
	
	function init()
	{}
	
	function getPath()
	{
		return $this->path;
	}
	
	function setPath($value)
	{
		$this->path = $value;
	}
	
	function dispatch()
	{
		global $root;
		
		$actions = $this->getActions();
		
		$code = 'action';
		$action = !empty($_REQUEST[$code]) ? $_REQUEST[$code] : 'index';
		$filename = !empty($actions[$action]) ? $actions[$action] : null;

		if (!empty($filename))
		{
			require_once($this->path.'/'.$filename.'.php');
			$class = basename($filename);
			$action = new $class;
			$action->run();
			return;
		}
		header('index.php');
	}
	
	function getActions()
	{
		return array();
	}

}

?>