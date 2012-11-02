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

		if (!$droits->check($module,0,'ecrire') && ($annonces->created_by != $user['user_id']))
		{
			error404(521);
			exit;
		}
		
		if (!empty($_POST) AND !$this->verifier_jeton($_POST))
		{
			error404(56);
			exit;
		}
		
		if (!empty($_GET) AND !$this->verifier_jeton($_GET))
		{
			error404(56);
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
				$x = $annonces->modifier();
				var_dump($x);
				if (!empty($_POST['approve']))
					$annonces->approuver();
			}
			header('location: '.$_SERVER['REQUEST_URI']);
			exit;
		}

		$tpl->set_filenames(array(
			'annonces' => ANNONCES_PATH.'/html/form.html',
		));
		
		$_GET = Helper::cleanSlashes($_GET);
		$annonces->nettoyer($_GET);
		$annonces->recuperer();
		
		// On charge le wysiwyg
		if (!empty($cf->config['wysiwyg_editor']))
			include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

		$tpl->assign_vars(array(
			'JETON'		=> $_GET['jeton'],
			'AUTEUR'	=> $annonces->created_by,
			'TITRE'		=> $annonces->title,
			'CONTENU'	=> $annonces->contents,
			'ID'		=> $annonces->id,
			'PRIX'		=> $annonces->price,
			'DATE_CREATION'		=> empty($annonces->created_date)?'':date('d/m/Y',$annonces->created_date),
			'DATE_MODIFICATION'	=> empty($annonces->updated_date)?'':date('d/m/Y',$annonces->updated_date),
			'DATE_APPROBATION'	=> empty($annonces->approved_date)?'':date('d/m/Y',$annonces->approved_date),
			'DATE_DEBUT' 	=> empty($annonces->start_date)?'':date('d/m/Y',$annonces->start_date),
			'NB_SEMAINES'	=> $annonces->max_weeks,
		));
		
		$annoncescategories = AnnoncesCategories::instance();
		$categories = $annoncescategories->recuperer_tous();
		foreach ($categories as $cat)
		{
			$tpl->assign_block_vars('cats',array(
				'VALUE' => $cat['id_cat'],
				'NAME' => $cat['title_cat'],
				'SELECTED' => $this->selected($cat['id_cat'],$annonces->id_cat),
			));
		}
		
		foreach ($this->type_options as $k => $v)
		{
			if ($k==0) continue;
			$tpl->assign_block_vars('type_options',array(
				'VALUE' 	=> $k,
				'NAME' 		=> $v,
				'SELECTED' => $this->selected($k,$annonces->type),
			));
		}
		
		if ($droits->check($module,0,'approuver'))
		{
			$tpl->assign_block_vars('approuver',array());
		}
		
	}
}

?>