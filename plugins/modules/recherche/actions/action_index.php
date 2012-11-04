<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Annonces
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2012, Alain GANDON All Rights Reserved
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

defined('RECHERCHE_PATH') OR die("Tentative de Hacking");

class action_index extends Action
{
	function init()
	{
		global $droits,$module;

		if (!$droits->check($module,0,'lister'))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $tpl,$droits,$module,$img,$lang,$jeton;
		
		// Creation du jeton de securite
		$jeton = $this->creer_jeton();

		$tpl->set_filenames(array(
			'recherche' => ANNONCES_PATH.'/html/form.html',
		));
		
		$tpl->assign_vars(array(
			'DESCRIPTION'=>htmlentities('Description'),
		));

		// Titre de page
		$tpl->titre_navigateur = $lang['L_TITRE_PAGE'];
		$tpl->titre_page = $lang['L_TITRE_PAGE'];
	}
}
?>