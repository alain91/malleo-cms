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

defined('CITATIONS_PATH') OR die("Tentative de Hacking");

class action_supprimer extends Action
{
	function init()
	{
		global $droits,$module;
		// DO NOTHING
	}
	
	function run()
	{
		global $user,$droits,$module,$base_formate_url;
		
		if (empty($_GET['confirme']) || $_GET['confirme'] != 1){
			error404(520);
			exit;
		}
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
			exit;
		}else{
			$citations = Citations::instance();
			$citations->nettoyer($_GET);
			$citations->recuperer();
			if (!$droits->check($module,0,'supprimer') && ($citations->id != $user['user_id']))
			{
				error404(520);
				exit;
			}
			$citations->supprimer();
		}
		header('location: '.$base_formate_url);
		exit;
	}
}
?>