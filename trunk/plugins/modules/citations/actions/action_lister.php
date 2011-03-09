<?php

defined('PROTECT') OR die("Tentative de Hacking");
defined('CITATIONS_PATH') OR die("Tentative de Hacking");

global $root;

require_once($root.'/class/class_action.php');

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

		/*
		$tpl->assign_block_vars('liste', array('CONTENU'=>'contenu1', 'AUTEUR' => "auteur1"));
		$tpl->assign_block_vars('liste', array('CONTENU'=>'contenu2', 'AUTEUR' => "auteur2"));
		*/
		$tpl->assign_block_vars('liste_vide', array('CONTENU'=>htmlentities('Aucun élément')));	
	}
}
?>