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
	var $type_options;
	var $sort_options;
	var $mode_options;
	var $forgiven_tags;
	
	function init()
	{
		global $droits,$module,$lang;

		if (!$droits->check($module,0,'voir'))
		{
			error404(518);
			exit;
		}

		$this->type_options = array(0 => $lang['sa_group_all']);
		for ($i = 1; $i <= 9; $i++) {
			if (!empty($lang['sa_group_'.$i]))
				$this->type_options[$i] = $lang['sa_group_'.$i];
			else
				break;
		}

		$this->sort_options = array(
			'title' => $lang['sa_sort_title'],
			'date_created' => $lang['sa_sort_date'],
			'price' => $lang['sa_sort_price']);
			
		$this->mode_options = array(
			'asc' => $lang['sa_mode_asc'],
			'desc' => $lang['sa_mode_desc']);

		$this->forgiven_tags = array('code', 'math', 'html');
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

		$tpl->assign_vars(array(
			'L_DESCRIPTION'	=> 'Champ description',
			'L_NO_SMALLADS'	=> $lang['sa_no_smallads'],
			'L_PRICE'		=> $lang['sa_db_price'],
			'L_PRICE_UNIT'	=> $lang['sa_price_unit'],
			'I_EDITER' 		=> $img['editer'],
			'I_DELETE' 		=> $img['effacer'],
			'L_EDITER'		=> 'l_editer',
			'L_DELETE'		=> 'l_delete',
			'L_CONFIRM_DELETE'	=> 'l_confirm_delete',
			'L_NOT_APPROVED' => $lang['sa_not_approved'],
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
		
		$type = !empty($_GET['type']) ? intval($_GET['type']) : 0;
		$filter = '1';
		if (!empty($type))
		{
			$filter .= ' AND type = '.(int)$type; 
		}
	
		$annonces = Annonces::instance();
		$id_cat = !empty($_GET['id_cat']) ? intval($_GET['id_cat']) : 0;
		if (!empty($id_cat))
		{
			$filter .= ' AND id_cat = '.(int)$id_cat; 
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
	
		foreach ($rows as $row)
		{
			$this->render_view($annonces, $row);			
		}
		
		foreach ($this->type_options as $k => $v)
		{
			$checked  = ($k == $type) ? 'checked' : '';
			$tpl->assign_block_vars('type_options',array(
				'NAME' 		=> $v,
				'CHECKED'	=> $checked,
				'VALUE' 	=> $k));
		}
		
		foreach ($this->sort_options as $k => $v)
		{
			$tpl->assign_block_vars('sort_options',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $annonces->selected($k, $sort),
				'VALUE' 	=> $k));
		}

		foreach ($this->mode_options as $k => $v)
		{
			$tpl->assign_block_vars('mode_options',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $annonces->selected($k, $mode),
				'VALUE' 	=> $k));
		}
	}
	
	function render_view($annonces, $row)
	{
		global $tpl,$user,$lang,$droits,$module,$jeton,$type_options;
		
		$tpl->assign_block_vars('item',array(
			'ID' 		=> $row->id,
			'TYPE'	 	=> $this->type_options[intval($row->type)],
			'TITRE' 	=> htmlentities($row->title),
			'CONTENU' 	=> htmlentities($row->contents),
			'PRICE'		=> $row->price,
			'DATE_CREATED' => $row->created_date,
			'DATE_UPDATED' => $row->updated_date,
			'PICTURE'	 => $row->picture,
		));
		
		if ($droits->check($module,0,'ecrire')
			|| ($row['id'] == $user['user_id']))
		{
			$tpl->assign_block_vars('item.edit', array(
				'U_EDIT' => formate_url('action=editer&id='.(int)$row->id,true)
			));
		}
		if ($droits->check($module,0,'supprimer')
			|| ($row['id'] == $user['user_id']))
		{
			$tpl->assign_block_vars('item.delete', array(
				'U_DELETE' => formate_url('action=supprimer&id='.(int)$row->id.'&jeton='.$jeton,true)
			));
		}
			
		$tpl->assign_block_vars('item.login',array());
		$tpl->assign_block_vars('item.pm',array());
		$tpl->assign_block_vars('item.mail',array());	
	}

}
?>