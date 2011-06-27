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
		global $droits,$module;

		if (!$droits->check($module,0,'voir'))
		{
			error404(518);
			exit;
		}
	}

	function run()
	{
		global $tpl,$droits,$module;

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
		global $session,$tpl,$droits,$module,$img,$lang,$user;
		
		$items_per_page = $smallads->config_get('items_per_page', ITEMS_PER_PAGE);
		$max_links      = $smallads->config_get('max_links', MAX_LINKS);

		$tpl->assign_vars(array(
			'I_EDITER' 	=> $img['editer'],
			'I_DELETE' 	=> $img['effacer'],
			'L_EDIT' 	=> 'Editer',
			'L_DELETE'	=> 'Supprimer',
			'L_CONFIRM_DELETE' => 'Confirmer la suppression',
			'C_LIST'         => $smallads->access_ok(LIST_ACCESS),
			'C_DESCRIPTION'	 => FALSE,
			'DESCRIPTION'	 => 'Champ description',
			'THEME'			 => get_utheme(),
			'LANG'			 => get_ulang(),
			'C_NB_SMALLADS'	 => ($nbr_smallads > 0) ? 1 : 0,
			'L_NO_SMALLADS'	 => $LANG['sa_no_smallads'],
			'L_LIST_NOT_APPROVED'		=> $LANG['sa_list_not_approved'],
			'L_PRICE'		 => $LANG['sa_db_price'],
			'L_PRICE_UNIT'	 => $LANG['sa_price_unit'],
			'C_ADD'			 => $c_add,
			'URL_ADD'		 => $url_add,
			'TARGET_ON_CHANGE_ORDER' => 'smallads.php?',
			'PAGINATION'     => $Pagination->display('smallads' . url('.php?p=%d'.$qs), $nbr_smallads, 'p', $items_per_page, $max_links),
		));

		$sort = !empty($_GET['sort']) ? $_GET['sort'] : '';
		if (empty($sort) || !array_key_exists($sort, $sort_options))
		{
			$sort = 'date_created';
		}
		
		$mode = !empty($_GET['mode']) ? $_GET['sort'] : '';
		if (empty($mode) || !array_key_exists($mode, $mode_options))
		{
			$mode = 'desc';
		}
		
		$type = !empty($_GET['type']) ? intval($_GET['type']) : 0;

		$view_not_approved = retrieve(GET, 'ViewNotApproved', 0, TINTEGER);
		$filter = array('(approved = 1)');
		if ($view_not_approved)
		{
			if ($smallads->access_ok(DELETE_ACCESS))
			{
				$filter = array('(approved = 0)');
			}
		}
		
		if (!empty($type))
		{
			$filter[] = '(type = '.intval($type).')'; 
		}
	
		$annonces = Annonces::instance();
		$rows = $annonces->recuperer_tous();
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
			$this->render_view($smallads, $row);
		}
		
		foreach ($type_options as $k => $v)
		{
			$checked  = ($k == $type) ? 'checked' : '';
			$tpl->assign_block_vars('type_options',array(
				'NAME' 		=> $v,
				'CHECKED'	=> $checked,
				'VALUE' 	=> $k));

			if ($k == 0) continue; // don't display 'All' option if edit form
			$tpl->assign_block_vars('type_options_edit',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $smallads->selected($k, intval($row['type'])),
				'VALUE' 	=> $k));
		}
		
		foreach ($sort_options as $k => $v)
		{
			$tpl->assign_block_vars('sort_options',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $smallads->selected($k, $sort),
				'VALUE' 	=> $k));
		}

		foreach ($mode_options as $k => $v)
		{
			$tpl->assign_block_vars('mode_options',array(
				'NAME' 		=> $v,
				'SELECTED'	=> $smallads->selected($k, $mode),
				'VALUE' 	=> $k));
		}
	}
	
	function render_view($smallads, $row)
	{
		global $Template, $User, $LANG, $type_options;
		
		$user 		= $User->get_id();
		$id_created = (int)$row['id_created'];
		$c_edit 	= FALSE;
		$url_edit	= '';
		$c_delete	= FALSE;
		$url_delete	= '';
				
		$v = $smallads->check_access(UPDATE_ACCESS, (OWN_CRUD_ACCESS|CONTRIB_ACCESS), $id_created);
		if ($v)
		{
			$url_edit 	= url('.php?edit=' . $row['id']);
			$c_edit 	= TRUE;
		}
		
		$v = $smallads->check_access(DELETE_ACCESS, (OWN_CRUD_ACCESS|CONTRIB_ACCESS), $id_created);
		if ($v)
		{
			$url_delete	= url('.php?delete=' . $row['id']);
			$c_delete	= TRUE;
		}

		$is_user	= ((!empty($row['id_created']))
							&& ($row['id_created'] > 0));

		$is_pm 		= ((!empty($row['id_created']))
							&& (intval($row['id_created']) != $user)
							&& ($smallads->config_get('view_pm',0)));

		$is_mail 	= ((!empty($row['user_mail']))
							&& (!empty($row['user_show_mail']))
							&& (!empty($row['id_created']))
							&& (intval($row['id_created']) != $user)
							&& ($smallads->config_get('view_mail',0)));
		
		if ($is_mail)
		{
			$mailto = $row['user_mail'];
			$mailto .= "?subject=Petite annonce #".$row['id']." : ".$row['title'];
			$mailto .= "&body=Bonjour,";
		}
		
		$Template->assign_block_vars('item',array(
			'ID' 		=> $row['id'],
			'VID'		=> empty($row['vid']) ? '' : $row['vid'],
			'TYPE'	 	=> $type_options[intval($row['type'])],
			'TITLE' 	=> htmlentities($row['title']),
			'CONTENTS' 	=> second_parse($row['contents']),
			'PRICE'		=> $row['price'],
			
			'DB_CREATED' => (!empty($row['date_created'])) ? $LANG['sa_created'].gmdate_format('date_format', $row['date_created']) : '',
			'DB_UPDATED' => (!empty($row['date_updated'])) ? $LANG['sa_updated'].gmdate_format('date_format', $row['date_updated']) : '',
			'C_DB_APPROVED'	 => !empty($row['approved']) ? TRUE : FALSE,
			'L_NOT_APPROVED' => $LANG['sa_not_approved'],

			'C_EDIT' 	=> $c_edit,
			'URL_EDIT'	=> $url_edit,
			'C_DELETE'	=> $c_delete,
			'URL_DELETE' => $url_delete,
			'L_CONFIRM_DELETE' => $LANG['sa_confirm_delete'],
			'URL_VIEW'	=> url('.php?id=' . $row['id']),
		
			'C_PICTURE'	 => !empty($row['picture']) ? TRUE : FALSE,
			'PICTURE'	 => PATH_TO_ROOT.'/smallads/pics/'.$row['picture'],

			'USER'		=> $is_user ? '<a href="'.PATH_TO_ROOT.'/member/member'. url('.php?id=' . $row['id_created'], '-' . $row['id_created'] . '.php') .'">'.$row['login'].'</a>' : '',
			'USER_PM' 	=> $is_pm ? '&nbsp;: <a href="'.PATH_TO_ROOT.'/member/pm' . url('.php?pm=' . $row['id_created'], '-' . $row['id_created'] . '.php') . '"><img src="'.PATH_TO_ROOT.'/templates/' . get_utheme() . '/images/' . get_ulang() . '/pm.png" alt="pm" /></a>' : '',
			'USER_MAIL' => $is_mail ? '&nbsp;<a href="mailto:' . $mailto . '"><img src="'.PATH_TO_ROOT.'/templates/' . get_utheme() . '/images/' . get_ulang() . '/email.png" alt="' . $row['user_mail']  . '" title="' . $row['user_mail']  . '" /></a>' : '',
		));
	}

}
?>