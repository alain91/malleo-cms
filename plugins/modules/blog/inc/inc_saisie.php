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
load_lang_mod('blog');
$edit_id_cat = null;
$action = $navlink_edit ='';
$titre_page = $lang['SAISIE_MSG_BLOG'];
if (isset($_POST['action']) ||isset($_GET['action']) )
{
	$action = isset($_POST['action'])? $_POST['action']: $_GET['action'];
}

$tpl->assign_vars(array(
	'HIDDEN'		=> '<input type="hidden" name="action" value="poster" />',
));

if ($action=='poster' && (empty($_POST['titre_billet']) || empty($_POST['billet']))){
	erreur_saisie('erreur_saisie',$lang['L_REMPLISSEZ_CHAMPS'],array(
		'TITRE'		=> isset($_POST['titre_billet'])?stripslashes($_POST['titre_billet']):'',
		'BILLET'	=> isset($_POST['billet'])?stripslashes($_POST['billet']):'',
		'TAGS'		=> isset($_POST['tags'])?stripslashes($_POST['tags']):''));	
	$action='';
}
		
switch($action)
{
	// ---------------------------------------------------------------------------------------------
	// ENREGISTREMENT Billet
	case 'poster':
		if (!$droits->check($module,0,'poster'))
		{
			error404(518);
			exit;
		}
		// nettoyage du message saisis
		$blog->clean($_POST);
		// enregistrement billet
		$blog->ajouter_billet();
		break;
	// ---------------------------------------------------------------------------------------------
	// ENREGISTREMENT Commentaire		
	case 'commenter' : 
		if (!$droits->check($module,0,'commenter') || $cf->config['blog_ok_coms'] == 0)
		{
			error404(519);
			exit;
		}
		// nettoyage du message saisis
		$blog->saisie['pseudo'] = $blog->saisie['mail'] = $blog->saisie['site'] = null;
		$blog->clean($_POST);
		// On enregistre le commentaire
		$blog->ajouter_commentaire();
		break;

	// ---------------------------------------------------------------------------------------------
	// SUPPRESSION du billet		
 	case 'supprimer':
		if (!isset($_GET['confirme']) || $_GET['confirme'] != 1){
			error404(520);break;
		}
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			$blog->clean($_GET);
			$blog->infos_billet();
			if (!$droits->check($module,0,'supprimer') && ($blog->auteur != $user['user_id'])){
				error404(520);
				exit;
			}
			$blog->supprime_billet();
		}
		break;
	// ---------------------------------------------------------------------------------------------
	// SUPPRESSION d'un commentaire
	case 'supprimer_coms':
		if (!session_id()) @session_start();
		if (!array_key_exists('jeton',$_GET) 
			|| $_GET['jeton'] != $_SESSION['jeton'] 
			|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
			error404(56);
		}else{
			$blog->clean($_GET);
			$blog->Get_Coms();
			if (!$droits->check($module,0,'supprimer')&& ($blog->commentaire['user_id'] != $user['user_id']))
			{
				error404(522);
				exit;
			}
			$blog->supprime_commentaire();
		}
		break;
	// ---------------------------------------------------------------------------------------------
	// ENREGISTREMENT EDITION Billet
	case 'edit':
		$blog->clean($_POST);
		$blog->infos_billet();
		if (!$droits->check($module,0,'editer') && ($blog->auteur != $user['user_id'])){
			error404(521);
			exit;
		}
		// MAJ des donnees
		$blog->update_billet();
		break;
		
	// ---------------------------------------------------------------------------------------------
	// PREPARATION Edition
	case 'editer':
		$blog->clean($_GET);
		$blog->infos_billet();
		if (!$droits->check($module,0,'editer') && ($blog->auteur != $user['user_id'])){
			error404(521);
			exit;
		}
		$blog->id_billet = intval($_GET['id_billet']);
		$blog->infos_billet();
		$tpl->assign_vars(array(
			'TITRE'		=> $blog->titre_billet,
			'BILLET'	=> $blog->billet,
			'TAGS'		=> $blog->tags,
			'HIDDEN'	=> '<input type="hidden" name="action" value="edit" />
							<input type="hidden" name="id_billet" value="'.$blog->id_billet.'" />'
		));
		$edit_id_cat = $blog->id_cat;
		$navlink_edit = '&id_billet='.$blog->id_billet.'&action=editer';
		$titre_page = $lang['EDITION_MSG_BLOG'];
	
	// ---------------------------------------------------------------------------------------------
	// PREPARATION SAISIE Billet
	default :
		if (!$droits->check($module,0,'poster')){
			error404(518);
			exit;
		}
		include($root.'fonctions/fct_formulaires.php');
		$tpl->set_filenames(array(
				'blog' => $root.'plugins/modules/blog/html/saisie.html'
		));
		// On charge le wysiwyg
		if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');
		$fractions_date = explode(':',date('j:n:Y:H:i',$blog->date_parution));
		$blog->module=$module;
		$liste_cats = $blog->lister_options_categories();
		if ($liste_cats=='') error404(525);
		$tpl->assign_vars(array(
			'LISTE_CAT'			=> $liste_cats,
			'LISTE_JOURS'		=> lister_chiffres(1,31,$fractions_date[0]),
			'LISTE_MOIS'		=> lister_chiffres(1,12,$fractions_date[1]),
			'LISTE_ANNEES'		=> lister_chiffres(2000,2020,$fractions_date[2]),
			'LISTE_HEURES'		=> lister_chiffres(0,23,$fractions_date[3]),
			'LISTE_MINUTES'		=> lister_chiffres(0,59,$fractions_date[4]),
			'TITRE_PAGE'		=> $titre_page
		));
		
		//
		// Titre de page 
		$tpl->titre_navigateur = $lang['L_MENU_BLOG'].' :: '.$titre_page;
		$tpl->titre_page = $titre_page;

		// Navlinks
		$session->make_navlinks($lang['L_MENU_BLOG'], formate_url('',true));
		$session->make_navlinks($titre_page,formate_url('mode=saisie'.$navlink_edit,true));
		break;
}


?>