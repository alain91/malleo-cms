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

defined('ANNONCES_PATH') OR die("Tentative de Hacking");

class action_index extends Action
{
	function init()
	{
		global $droits,$module;

		if (!$droits->check($module,0,'voir'))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $tpl,$droits,$module,$img,$lang;

		$tpl->set_filenames(array(
			'annonces' => ANNONCES_PATH.'/html/liste_cat.html',
		));
		
		$tpl->assign_vars(array(
			'DESCRIPTION'=>htmlentities('Description'),
			'WIDTH'=>'23%',
		));

		// Titre de page
		$tpl->titre_navigateur = $lang['L_TITRE_PAGE'];
		$tpl->titre_page = $lang['L_TITRE_PAGE'];

		if ($droits->check($module,0,'poster'))
		{
			$tpl->options_page = array(
					3=>array(
					'ICONE'		=> $img['nouveau'],
					'LIBELLE'	=> 'Nouveau',
					'LIEN'		=> formate_url('action=editer',true))
			);
		}
		
		$this->lister();
	}

	function lister()
	{
		global $session,$tpl,$droits,$module,$img,$lang,$user,$jeton;
		global $type_options,$sort_options,$mode_options;

		$categories = AnnoncesCategories::instance();
		$rows = $categories->recuperer_tous();
		if (empty($rows))
		{
			$tpl->assign_block_vars('liste_vide', array(
				'CONTENU'=>htmlentities('Aucun élément')
			));
			return;
		}
	
		foreach ($rows as $row)
		{
			$this->render_view($categories, $row);			
		}
	}
	
	function render_view($categories, $row)
	{
		global $tpl,$user,$lang,$droits,$module,$jeton,$type_options;
		
		$tpl->assign_block_vars('item',array(
			'ID' 		=> $row['id_cat'],
			'TITRE' 	=> htmlentities($row['title_cat']),
			'PICTURE'	=> $row['picture_cat'],
			'URL'		=> formate_url('action=lister&id_cat='.$row['id_cat'],true)
			));
	}

}
?>