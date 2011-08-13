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
		global $session,$tpl,$droits,$module,$img,$lang,$user;

		$tpl->set_filenames(array(
			'citations' => CITATIONS_PATH.'/html/liste.html',
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

		$tpl->assign_vars(array(
			'I_EDITER' 	=> $img['editer'],
			'I_DELETE' 	=> $img['effacer'],
			'L_EDIT' 	=> 'Editer',
			'L_DELETE'	=> 'Supprimer',
			'L_CONFIRM_DELETE' => 'Confirmer la suppression',
			));

		$citations = Citations::instance();
		$rows = $citations->recuperer_tous();
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
			$tpl->assign_block_vars('quotes', array(
				'ID' => $row->id,
				'DATE' => date('d/m/Y',$row->date_add),
				'CONTENU'=> nl2br($row->billet),
				'AUTEUR' => $row->auteur,
			));
			if ($droits->check($module,0,'ecrire')
				|| ($citations->id == $user['user_id']))
			{
				$tpl->assign_block_vars('quotes.edit', array(
					'U_EDIT' => formate_url('action=editer&id='.$row->id,true)
				));
			}
			if ($droits->check($module,0,'supprimer')
				|| ($citations->id == $user['user_id']))
			{
				$tpl->assign_block_vars('quotes.delete', array(
					'U_DELETE' => formate_url('action=supprimer&id='.$row->id.'&jeton='.$jeton,true)
				));
			}
		}

	}
}
?>