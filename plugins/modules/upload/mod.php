<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Upload
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

define('UPLOAD_PATH', dirname(__FILE__));

global $cf;
$rootdir = !empty($cf->config['upload_rootdir']) ? $cf->config['upload_rootdir'] : 'upload';
define('UPLOAD_ROOTDIR', $rootdir);
unset($rootdir);

require_once(UPLOAD_PATH.'/class/class_core.php');
Core::setup();

require_once(UPLOAD_PATH.'/prerequis.php');

class controller_upload extends Controller
{
	function init()
	{
		global $root,$erreur;
		$this->setPath(UPLOAD_PATH);
		if (!file_exists($root.UPLOAD_ROOTDIR))
		{
			$msg = sprintf($erreur[1600],UPLOAD_ROOTDIR);
			error404($msg);
			exit;
		}
	}
	
	function getActions()
	{
        return array(
            'index' 	=> 'actions/action_lister',
            'supprimer'	=> 'actions/action_supprimer',
        );
	}
}

$controller = new controller_upload();
$controller->dispatch();
?>