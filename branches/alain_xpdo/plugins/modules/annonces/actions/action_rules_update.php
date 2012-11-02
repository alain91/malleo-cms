<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Annonces
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

defined('ANNONCES_PATH') OR die("Tentative de Hacking ".basename(__FILE__));

class action_rules_update extends Action
{
	function init()
	{
		global $droits,$module;
		// DO NOTHING
	}
	
	function run()
	{
		global $user,$droits,$module,$base_formate_url;

		defined('PROTECT_ADMIN') OR define('PROTECT_ADMIN',true);
		
		$droits->delete_regle('module',$module);
		$droits->init_regles($module);
	
		header('location: '.$base_formate_url);
		exit;
	}
}
?>