<?php

defined('PROTECT') OR die("Tentative de Hacking");

abstract class Controller
{
	abstract function init();
	
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
	
	abstract function getActions();

}
?>