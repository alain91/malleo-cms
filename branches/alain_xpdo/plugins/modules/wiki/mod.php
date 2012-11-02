<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
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
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
$id_version = 0;
require_once($root.'plugins/modules/wiki/prerequis.php');

// Autorisations
if (!$droits->check($module,0,'voir')){
	error404(903);
}

// Navlinks
$session->make_navlinks(array(
	$module	=> formate_url('',true),
));

$mode = null;
if(isset($_GET['mode'])||isset($_POST['mode'])){
	$mode=(isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
}

// PAGE PRECISEE
if (isset($_GET['t']) && $mode != 'saisie_enregistrer'){
	$wiki->clean($_GET);
	if (!$wiki->page_existe() && (strlen($wiki->t) > 0)){
		$mode = 'saisie';
	}elseif(strlen($wiki->t)  == 0){
		$wiki->t = $cf->config['wiki_page_defaut'];
	}
// Enregistrement
}elseif($mode == 'saisie_enregistrer'){
	$wiki->clean($_POST);
	$wiki->page_existe();
// Page par defaut
}elseif($mode==null){
	$wiki->t = $cf->config['wiki_page_defaut'];
	if (!$wiki->page_existe()){
		$mode = 'saisie';
	}
}

switch($mode){
	case 'saisie':
		// Saisie d'une nouvelle page web
		if (!$droits->check($module,0,'ecrire')){
			error404(900);
		}
		// Navlinks
		$session->make_navlinks(array(
			$wiki->t	=> formate_url('t='.$wiki->t,true),
		));
		$tpl->set_filenames(array(
			'wiki' => $root.'plugins/modules/wiki/html/saisie.html'
		));
		if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
		$wiki->saisie_texte();
		break;
	case 'saisie_enregistrer':
		if (!$droits->check($module,0,'ecrire')){
			error404(900);
		}
		if (empty($wiki->titre) || empty($wiki->texte)){
			error404(906);
		}
		$wiki->enregistrer_page();
		// On affiche une fenetre de confirmation
		affiche_message('wiki','L_PAGE_ENREGISTREE',formate_url('t='.$wiki->t,true));
		break;
	case 'supprimer_version':
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			if (!$droits->check($module,0,'supprimer')){
				error404(901);
			}
			$wiki->clean($_GET);
			$wiki->supprimer_version();
			// On affiche une fenetre de confirmation
			affiche_message('wiki','L_VERSION_SUPPRIMEE',formate_url('t='.$wiki->infos['tag'].'&mode=historique',true));
		}
		break;
	case 'restaurer_version':
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			if (!$droits->check($module,0,'moderer')){
				error404(905);
			}
			$wiki->clean($_GET);
			$wiki->restaurer_version();
			// On affiche une fenetre de confirmation
			affiche_message('wiki','L_VERSION_RESTAUREE',formate_url('t='.$wiki->infos['tag'].'&mode=historique',true));
		}
		break;
	case 'supprimer_page':
		if (!$droits->check($module,0,'supprimer')){
			error404(901);
		}
		$wiki->clean($_GET);
		$wiki->supprimer_page();
		// On affiche une fenetre de confirmation
		affiche_message('wiki','L_PAGE_SUPPRIMEE',formate_url('',true));
		break;
	case 'mes_contributions':
		$tpl->set_filenames(array(
			'wiki' => $root.'plugins/modules/wiki/html/liste_contributions.html'
		));
		// Navlinks
		$session->make_navlinks(array(
			$lang['L_MES_CONTRIBUTIONS'] => formate_url('mode=mes_contributions',true)
		));
		$tpl->titre_navigateur = $tpl->titre_page = $lang['L_MES_CONTRIBUTIONS'];
		
		$start = (isset($_GET['start']))?intval($_GET['start']):0;
		// PAGINATION
		include($root.'fonctions/fct_affichage.php');
		
		$wiki->afficher_contributions();
		break;
	case 'historique':
		//  Affiche l'historique d'une page
		if (isset($_POST['enregistrer'])){
			if (!session_id()) @session_start();
			if (!array_key_exists('jeton',$_POST) 
				|| $_POST['jeton'] != $_SESSION['jeton'] 
				|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
				error404(56);
			}else{
				$wiki->clean($_POST);
				$wiki->update_etat_wiki();
			}
		}		
		$tpl->set_filenames(array(
			'wiki' => $root.'plugins/modules/wiki/html/historique_page.html'
		));
		$start = (isset($_GET['start']))?intval($_GET['start']):0;
		// PAGINATION
		include($root.'fonctions/fct_affichage.php');
		
		$wiki->afficher_historique();
		// Autorisations
		if ($droits->check($module,0,'proteger') || $droits->check($module,0,'moderer')){
			$tpl->assign_block_vars('options_wiki',array());
			if ($droits->check($module,0,'proteger')) $tpl->assign_block_vars('options_wiki.verrouiller',array());
			if ($droits->check($module,0,'moderer')) $tpl->assign_block_vars('options_wiki.terminer',array());
		}
		
		// Navlinks
		$l_historique = sprintf($lang['L_HISTORIQUE_FORMATE'],$wiki->t);
		$session->make_navlinks(array(
			$wiki->t => formate_url('t='.$wiki->t,true),
			$l_historique => formate_url('t='.$wiki->t.'&mode=historique',true)
		));
		$tpl->titre_navigateur = $tpl->titre_page = $l_historique;
		break;
	case 'version':
		// Affiche une version d'une page web
		$id_version = intval($_GET['id_version']);
	default:
		if (!$droits->check($module,0,'lire')){
			error404(904);
		}
		// Affiche la derniere page web d'un tag donne
		$tpl->set_filenames(array(
			'wiki' => $root.'plugins/modules/wiki/html/page.html'
		));
		//
		// Outils de posting
		include_once($root.'class/class_posting.php');
		$post=new posting();
		
		$wiki->infos_page($id_version);
		
		// raccourcis
		if ($droits->check($module,0,'ecrire')){
		
			if ($wiki->infos['protege']==false){
				$tpl->options_page[] = array(
						'ICONE'		=> $img['wiki_modifier_page'],
						'LIBELLE'	=> $lang['L_EDITER'],
						'LIEN'		=> formate_url('t='.$wiki->infos['tag'].'&mode=saisie',true));			
			}
			$tpl->options_page[] = array(
					'ICONE'		=> $img['wiki_historique_page'],
					'LIBELLE'	=> $lang['L_HISTORIQUE'],
					'LIEN'		=> formate_url('t='.$wiki->infos['tag'].'&mode=historique',true));
		}
		$wiki->afficher_page();
		
		//Avertissement version
		if ($mode=='version'){
			$wiki->avertissement($lang['L_AVERTISSEMENT_MODE_VERSION']);
		// Avertissement page en cours de redaction
		}elseif($wiki->infos['termine']==0){
			$wiki->avertissement($lang['L_AVERTISSEMENT_NON_TERMINE']);
		}
		
		// Navlinks
		$session->make_navlinks(array(
			$wiki->infos['tag'] => formate_url('t='.$wiki->infos['tag'],true)
		));
		$tpl->titre_navigateur = $wiki->infos['titre'].' :: '.$module;
		$tpl->titre_page = $wiki->infos['titre'];
		
		// Incrementation du compteur de visualisations
		$wiki->incremente();
		break;
}


// raccourcis
if ($droits->check($module,0,'ecrire')){
	$tpl->options_page[]= array(
			'ICONE'		=> $img['wiki_mes_contributions'],
			'LIBELLE'	=> $lang['L_MES_CONTRIBUTIONS'],
			'LIEN'		=> formate_url('mode=mes_contributions',true));
}
?>