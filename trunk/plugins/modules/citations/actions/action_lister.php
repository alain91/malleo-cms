<?php
defined('CITATIONS_PATH') OR die("Tentative de Hacking");

class action_lister extends Action
{
	function run()
	{
		global $session,$tpl,$droits,$module,$img,$lang;
		
		$tpl->set_filenames(array(
			'citations' => CITATIONS_PATH.'/html/liste.html',
		));
		
		// Titre de page 
		$tpl->titre_navigateur = $module;
		$tpl->titre_page = $module;

		if ($droits->check($module,0,'poster')){
			$tpl->options_page = array(
					3=>array(
					'ICONE'		=> $img['nouveau'],
					'LIBELLE'	=> 'Nouveau', //$lang['L_NOUVEAU'],
					'LIEN'		=> formate_url('mode=editer',true))
			);
		}
		
		$tpl->assign_vars(array(
			'I_EDITER' => $img['editer'],
			'I_DELETE' => $img['effacer'],
			));

		$tpl->assign_block_vars('quotes', array('ID' => 1, 'DATE' => Date("j, n, Y"), 'CONTENU'=>'contenu1', 'AUTEUR' => "auteur1"));
		$tpl->assign_block_vars('quotes.edit', array('L_EDIT' => 'Editer'));
		$tpl->assign_block_vars('quotes.delete', array('L_DELETE' => 'Supprimer'));

		$tpl->assign_block_vars('quotes', array('ID' => 2, 'DATE' => Date("j, n, Y"), 'CONTENU'=>'contenu2', 'AUTEUR' => "auteur2"));
		$tpl->assign_block_vars('quotes.edit', array('L_EDIT' => 'Editer'));
		$tpl->assign_block_vars('quotes.delete', array('L_DELETE' => 'Supprimer'));
		return;
		/*
		$tpl->assign_block_vars('liste_vide', array('CONTENU'=>htmlentities('Aucun élément')));	
		*/
		
		$Template->set_filenames(array(
			'quotes' => 'quotes/quotes.tpl'
		));

		$cat_cols = $quotes->config_get('cat_cols', 2);
		
		$num_subcats = count($QUOTES_CATS);
		if ($num_subcats > 1)
		{
			$i = 0;
			$first = true;
			foreach ($QUOTES_CATS as $id => $value)
			{
				if ($id != 0
					&& $value['visible']
					&& $value['id_parent'] == $category_id
					&& $quotes->cats->access_ok($id, QUOTES_LIST_ACCESS))
				{
					if ($first)
					{
						$Template->assign_vars(array(
							'C_SUB_CATS' => true
						));
						$first = false;
					}
					if ( $i % $cat_cols == 1 )
						$Template->assign_block_vars('row', array());
					$Template->assign_block_vars('row.list_cats', array(
						'ID' 			=> $id,
						'NAME' 			=> $value['name'],
						'WIDTH' 		=> intval(100 / $cat_cols),
						'SRC' 			=> $value['icon'],
						'IMG_NAME' 		=> $value['name'],
						'NUM_ITEMS' 	=> $value['num_items'],
						'U_CAT' 		=> url('quotes.php?cat=' . $id, 'category-' . $id . '+' . url_encode_rewrite($value['name']) . '.php'),
						'U_ADMIN_CAT' 	=> url('admin_quotes_cat.php?edit=' . $id),
						'C_CAT_IMG' 	=> !empty($value['icon'])
					));
				}
				$i++;
			}
		}
		
		$where = '(approved = 1)';
		if( intval($category_id) > 0 ) 
		{
			$where .= ' AND (idcat='.intval($category_id).')';
			$Template->assign_vars(array(
				'C_DESCRIPTION' => true,
				'DESCRIPTION' 	=> $QUOTES_CATS[$category_id]['description']
			));
		}

		$nbr_quotes = $Sql->query("SELECT COUNT(1) AS total FROM ".PREFIX . "quotes WHERE ".$where, __LINE__, __FILE__);
		$nbr_quotes = intval($nbr_quotes);

		import('util/pagination'); 
		$Pagination = new Pagination();

		$items_per_page   = $quotes->config_get('items_per_page', QUOTES_ITEMS_PER_PAGE);
		$max_links        = $quotes->config_get('max_links', QUOTES_MAX_LINKS);
		$quotes_list_size = $quotes->config_get('quotes_list_size', 1);
		
		$url = url('quotes.php?cat=' . $id . '&amp;p=%d', 'category-' . $category_id . '-%d+' . url_encode_rewrite($value['name']) . '.php');

		$Template->assign_vars(array(
			'C_EDIT'         => $quotes->cats->access_ok($category_id, QUOTES_CREATE_ACCESS|QUOTES_CONTRIB_ACCESS|QUOTES_WRITE_ACCESS),
			'C_LIST'         => $quotes->cats->access_ok($category_id, QUOTES_LIST_ACCESS),
			'PAGINATION'     => $Pagination->display($url, $nbr_quotes, 'p', $items_per_page, $max_links),
			'IN_MINI'        => 'checked="checked"',
			'L_ALERT_TEXT'   => $quotes->lang_get('require_text'),
			'L_DELETE_QUOTE' => $quotes->lang_get('q_delete'),
			'L_ADD_QUOTE'    => $quotes->lang_get('q_create'),
			'L_CONTENTS'     => $quotes->lang_get('q_contents'),
			'L_AUTHOR'       => $quotes->lang_get('q_author'),
			'L_IN_MINI'      => $quotes->lang_get('q_in_mini'),
			'L_REQUIRE'      => $quotes->lang_get('require'),
			'L_PSEUDO'       => $quotes->lang_get('pseudo'),
			'L_SUBMIT'       => $quotes->lang_get('submit'),
			'L_RESET'        => $quotes->lang_get('reset'),
			'L_ON'           => $quotes->lang_get('on'),
			'L_CATEGORY'		=> $quotes->lang_get('q_category'),
			'CATEGORIES_TREE'	=> $quotes->cats->build_select_form($category_id, 'idcat', 'idcat', 0,
										QUOTES_WRITE_ACCESS|QUOTES_CONTRIB_ACCESS, $CONFIG_QUOTES['auth'],
										IGNORE_AND_CONTINUE_BROWSING_IF_A_CATEGORY_DOES_NOT_MATCH)
			));
		

		if ($nbr_quotes > 0)
		{
			$result = $Sql->query_while("SELECT q.*
				FROM ".PREFIX."quotes q
				WHERE ".$where."
				ORDER BY q.timestamp DESC"
				. $Sql->limit($Pagination->get_first_msg($items_per_page, 'p'), $items_per_page),
				__LINE__, __FILE__);
				
			while ($row = $Sql->fetch_assoc($result))
			{
				$user_id = (int)$row['user_id'];
				$c_edit 	= FALSE;
				$l_edit		= '';
				$url_edit	= '';
				$c_delete	= FALSE;
				$l_delete	= '';
				$url_delete	= '';
				
				if ( $quotes->cats->access_ok($category_id, QUOTES_UPDATE_ACCESS|QUOTES_WRITE_ACCESS) )
				{
					$url_edit 	= url('.php?edit=' . $row['id']);
					$c_edit 	= TRUE;
					$l_edit 	= $quotes->lang_get('edit');
				}

				if ( $quotes->cats->access_ok($category_id, QUOTES_DELETE_ACCESS|QUOTES_WRITE_ACCESS) )
				{
					$url_delete	= url('.php?delete=' . $row['id']);
					$c_delete	= TRUE;
					$l_delete 	= $quotes->lang_get('delete');
				}
				
				$Template->assign_block_vars('quotes',array(
					'ID' 		=> $row['id'],
					'CONTENTS' 	=> ucfirst($row['contents']),
					'AUTHOR' 	=> ucfirst($row['author']),
					'DATE' 		=> $quotes->lang_get('on') . ': ' . gmdate_format('date_format', $row['timestamp']),
					'IN_MINI' 	=> $row['in_mini'] == 1 ? 'on' : 'off',
					'C_EDIT' 	=> $c_edit,
					'L_EDIT'	=> $l_edit,
					'URL_EDIT'	=> $url_edit,
					'C_DELETE'	=> $c_delete,
					'L_DELETE'	=> $l_delete,
					'URL_DELETE'	=> $url_delete,
					'THEME'		=> get_utheme(),
					'LANG'		=> get_ulang(),
					));
			}
		}
		else
		{
			$Template->assign_vars(array(
				'L_NO_ITEMS' => $quotes->lang_get('q_no_items'),
				'C_NO_ITEMS' => true
			));
		}
		$Sql->query_close($result);
	}
}
?>