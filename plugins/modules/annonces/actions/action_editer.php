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
		global $tpl,$droits,$module,$img,$lang,$root,$cf,$base_formate_url;
		global $cache,$style_name; // pour fct_tinymce.php

		$annonces = Annonces::instance();	
		
		if (!empty($_POST['save']) OR !empty($_POST['approve']))
		{
			$_POST = Helper::cleanSlashes($_POST);
			$annonces->nettoyer($_POST);
			if (empty($annonces->id))
			{
				$annonces->inserer();
				if (!empty($_POST['approve']))
					$annonces->approuver();
			}
			else
			{
				$annonces->modifier();
				if (!empty($_POST['approve']))
					$annonces->approuver();
			}
			header('location: '.$base_formate_url);
			exit;
		}

		$tpl->set_filenames(array(
			'annonces' => ANNONCES_PATH.'/html/form.html',
		));

		$_GET = Helper::cleanSlashes($_GET);
		$annonces->nettoyer($_GET);
		$annonces->recuperer();
		if (!$droits->check($module,0,'ecrire') && ($annonces->user_id != $user['user_id'])){
			error404(521);
			exit;
		}
		
		// On charge le wysiwyg
		if (!empty($cf->config['wysiwyg_editor']))
			include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

		$tpl->assign_vars(array(
			'AUTEUR'	=> $annonces->created_by,
			'TITRE'		=> $annonces->title,
			'CONTENU'	=> $annonces->contents,
			'ID'		=> $annonces->id,
			'PRIX'		=> $annonces->price,
			'DATE_CREATION'	=> empty($annonces->created_date)?'':date('d/m/Y',$annonces->created_date),
			'DATE_MODIFICATION'	=> empty($annonces->updated_date)?'':date('d/m/Y',$annonces->updated_date),
			'DATE_APPROBATION'	=> empty($annonces->approved_date)?'':date('d/m/Y',$annonces->approved_date),
		));
		
		$model_categories = AnnoncesCategories::instance();
		$categories = $model_categories->recuperer_tous();
		foreach ($categories as $cat)
		{
			$tpl->assign_block_vars('cats',array(
				'VALUE' => $cat->id_cat,
				'NAME' => $cat->title_cat,
				'SELECTED' => $model_categories->selected($cat->id_cat,$annonces->id_cat),
			));
		}
		
		foreach ($this->type_options as $k => $v)
		{
			if ($k==0) continue;
			$tpl->assign_block_vars('type_options',array(
				'VALUE' 	=> $k,
				'NAME' 		=> $v,
				'SELECTED' => $model_categories->selected($k,$annonces->type),
			));
		}
		
	}
}

?>