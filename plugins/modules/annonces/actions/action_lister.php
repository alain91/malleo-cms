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

class action_lister extends Action
{
	function init()
	{
		global $droits,$module,$lang;

		if (!$droits->check($module,0,'lire'))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $tpl,$droits,$module,$img;

		$tpl->set_filenames(array(
			'annonces' => ANNONCES_PATH.'/html/liste.html',
		));

		// Titre de page
		$tpl->titre_navigateur = $module;
		$tpl->titre_page = $module;

		if ($droits->check($module,0,'ecrire'))
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
		
		$annonces = Annonces::instance();

		$id_cat = !empty($_GET['id_cat']) ? intval($_GET['id_cat']) : 0;

		$target = formate_url('action=lister'.(empty($id_cat)?'':'&id_cat='.$id_cat),true);
		$target = str_replace('&amp;','&',$target);

		$tpl->assign_vars(array(
			'I_EDITER' 		=> $img['editer'],
			'I_DELETE' 		=> $img['effacer'],
			'TARGET_ON_CHANGE_ORDER' => $target,
			'CHEMIN_ICONES' => 'data/icones_annonces/',
		));

		$sort = !empty($_GET['sort']) ? trim($_GET['sort']) : '';
		if (empty($sort) || !array_key_exists($sort, $this->sort_options))
		{
			$sort = 'created_date';
		}

		$mode = !empty($_GET['mode']) ? trim($_GET['mode']) : '';
		if (empty($mode) || !array_key_exists($mode, $this->mode_options))
		{
			$mode = 'desc';
		}
		
		$view_not_approved = !empty($_GET['ViewNotApproved']) ? intval($_GET['ViewNotApproved']) : 0;
		if (empty($view_not_approved))
			$filter = 'approved_by <> 0';
		else
			$filter = 'approved_by = 0';
		
		if (!empty($id_cat))
		{
			$filter .= ' AND id_cat = '.(int)$id_cat;
		}
		$type = !empty($_GET['type']) ? intval($_GET['type']) : 0;
		if (!empty($type))
		{
			$filter .= ' AND type = '.(int)$type;
		}
		
		foreach ($this->type_options as $k => $v)
		{
			$checked  = ($k == $type) ? 'checked' : '';
			$tpl->assign_block_vars('type_options',array(
				'NAME' 		=> $v,
				'CHECKED'	=> $checked,
				'VALUE' 	=> $k));
		}

		$rows = $annonces->recuperer_tous($sort, $mode, $filter);
		if (empty($rows))
		{
			$tpl->assign_block_vars('liste_vide', array(
				'CONTENU'=>htmlentities('Aucun élément')
			));
			return;
		}

		// Creation du jeton de securite
		if (!session_id()) @session_start();
		$jeton = md5(uniqid(rand(), TRUE));
		$_SESSION['jeton'] = $jeton;
		$_SESSION['jeton_timestamp'] = $session->time;

		$tpl->assign_block_vars('liste', array());

		foreach ($rows as $row)
		{
			$this->render_view($annonces, $row);
		}
		
		foreach ($this->sort_options as $k => $v)
		{
			$tpl->assign_block_vars('liste.sort_options',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $annonces->selected($k, $sort),
				'VALUE' 	=> $k));
		}

		foreach ($this->mode_options as $k => $v)
		{
			$tpl->assign_block_vars('liste.mode_options',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $annonces->selected($k, $mode),
				'VALUE' 	=> $k));
		}

	}

	function render_view($annonces, $row)
	{
		global $tpl,$user,$lang,$droits,$module,$jeton,$type_options;

		$type = empty($row->type) ? '' : $this->type_options[intval($row->type)];

		$tpl->assign_block_vars('liste.item',array(
			'ID' 		=> $row->id,
			'TYPE'	 	=> $type,
			'TITRE' 	=> htmlentities($row->title),
			'CONTENU' 	=> htmlentities($row->contents),
			'PRIX'		=> $row->price,
			'DATE_APPROBATION' => empty($row->approved_date)?'-':date('d/m/Y',$row->approved_date),
			'IMAGE'	 	=> $row->picture,
		));

		if ($droits->check($module,0,'ecrire'))
		{
			$tpl->assign_block_vars('liste.item.edit', array(
				'U_EDIT' => formate_url('action=editer&id='.(int)$row->id,true)
			));
		}
		if ($droits->check($module,0,'supprimer'))
		{
			$tpl->assign_block_vars('liste.item.delete', array(
				'U_DELETE' => formate_url('action=supprimer&id='.(int)$row->id.'&jeton='.$jeton,true)
			));
		}
		if (empty($row->approved_by))
		{
			$tpl->assign_block_vars('liste.item.not_approved', array());
		}

	}

}
?>