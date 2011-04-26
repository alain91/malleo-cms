<?php
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
		global $session,$tpl,$droits,$module,$img,$lang;
		
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
					'LIBELLE'	=> 'Nouveau', //$lang['L_NOUVEAU'],
					'LIEN'		=> formate_url('action=editer',true))
			);
		}
		
		$tpl->assign_vars(array(
			'I_EDITER' => $img['editer'],
			'I_DELETE' => $img['effacer'],
			));

		$tpl->assign_block_vars('quotes', array('ID' => 1, 'DATE' => Date("j, n, Y"), 'CONTENU'=>'contenu1', 'AUTEUR' => "auteur1"));
		$tpl->assign_block_vars('quotes.edit', array('L_EDIT' => 'Editer', 'U_EDIT' => formate_url('action=editer&id=1',true)));
		$tpl->assign_block_vars('quotes.delete', array('L_DELETE' => 'Supprimer', 'U_DELETE' => formate_url('action=supprimer&id=1',true)));

		$tpl->assign_block_vars('quotes', array('ID' => 2, 'DATE' => Date("j, n, Y"), 'CONTENU'=>'contenu2', 'AUTEUR' => "auteur2"));
		$tpl->assign_block_vars('quotes.edit', array('L_EDIT' => 'Editer', 'U_EDIT' => formate_url('action=editer&id=2',true)));
		$tpl->assign_block_vars('quotes.delete', array('L_DELETE' => 'Supprimer', 'U_DELETE' => formate_url('action=supprimer&id=1',true)));
		return;
		/*
		$tpl->assign_block_vars('liste_vide', array('CONTENU'=>htmlentities('Aucun élément')));	
		*/
		
	}
}
?>