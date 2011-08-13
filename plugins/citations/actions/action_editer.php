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

class action_editer extends Action
{
	function init()
	{
		global $droits,$module;

		if (!$droits->check($module,0,'ecrire'))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $session,$tpl,$droits,$module,$img,$lang,$root,$cf,$cache,$base_formate_url,$style_name;

		$citations = Citations::instance();	
		
		if (!empty($_POST))
		{
			$_POST = Helper::cleanSlashes($_POST);
			$citations->nettoyer($_POST);
			if (empty($citations->id))
			{
				$citations->inserer();
			}
			else
			{
				$citations->modifier();
			}
			header('location: '.$base_formate_url);
			exit;
		}

		$tpl->set_filenames(array(
			'citations' => CITATIONS_PATH.'/html/form.html',
		));

		$_GET = Helper::cleanSlashes($_GET);
		$citations->nettoyer($_GET);
		$citations->recuperer();
		if (!$droits->check($module,0,'ecrire') && ($citations->user_id != $user['user_id'])){
			error404(521);
			exit;
		}
		
		// On charge le wysiwyg
		if (!empty($cf->config['wysiwyg_editor']))
			include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

		$tpl->assign_vars(array(
			'L_AUTEUR'	=> $lang['FORM_AUTEUR'],
			'L_CATEGORIE'	=> $lang['FORM_CATEGORIE'],
			'L_BILLET'	=> $lang['FORM_BILLET'],
			'AUTEUR'	=> $citations->auteur,
			'BILLET'	=> $citations->billet,
			'ID'		=> $citations->id,
		));

		$titre_page = $lang['FORM_PAGE_TITRE'];

	}
}


?>