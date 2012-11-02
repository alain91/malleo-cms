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
require_once($root.'plugins/modules/pages/prerequis.php');
$start = (isset($_GET['start']))?intval($_GET['start']):0;

if (isset($_GET['p']))
{
	if (!$droits->check($module,0,'voir')){
		error404(804);
	}
	// page
	$id_page = intval($_GET['p']);
	$tpl->url_canonique = formate_url('p='.$id_page,true);
	$tpl->set_filenames(array(
		'pages' => $root.'plugins/modules/pages/html/page_web.html'
	));
	//
	// Outils de posting
	include_once($root.'class/class_posting.php');
	$post=new posting();
				
 	if ($droits->check($module,0,'voir')){
		// Navlinks
		$session->make_navlinks(array(
			$module	=> formate_url('',true),
		));
	}
	$pages->afficher_page($id_page);
}else{
	// Liste / Saisie
	$mode = null;
	if(isset($_GET['mode'])||isset($_POST['mode'])){
		$mode=(isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
	}
	
	// Navlinks
	$session->make_navlinks(array(
		$module	=> formate_url('',true),
	));
	
	if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

	switch($mode){	
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_PAGES, 'id_page', 'ordre', 'ASC', intval($_GET['id_page']), $sens);
			header('location: '.$base_formate_url);
			break;	
		case 'nouveau':
			// Saisie d'une nouvelle page web
			if (!$droits->check($module,0,'ecrire')){
				error404(800);
			}
			// Navlinks
			$session->make_navlinks(array(
				$lang['L_SAISIE_PAGE_WEB']	=> formate_url('mode=nouveau',true),
			));
			$tpl->set_filenames(array(
				'pages' => $root.'plugins/modules/pages/html/saisie.html'
			));
			$pages->saisie_texte();
			break;
		case 'nouveau_enregistrer':
			if (!$droits->check($module,0,'ecrire')){
				error404(800);
			}
			$pages->clean($_POST);
			$pages->enregistrer_page();
			// On affiche une fenetre de confirmation
			affiche_message('pages','L_PAGE_ENREGISTREE',formate_url('p='.$pages->id_page,true));
			break;
		case 'supprimer':
			if (!isset($_GET['confirme']) || $_GET['confirme'] != 1 || !$droits->check($module,0,'supprimer')){
				error404(801);
			}
			if (!session_id()) @session_start();
			if (!array_key_exists('jeton',$_GET) 
				|| $_GET['jeton'] != $_SESSION['jeton'] 
				|| time() - $_SESSION['jeton_timestamp'] >= VALIDITE_JETON){
				error404(56);
			}else{
				$pages->clean($_GET);
				$pages->supprimer_page();
			}
			// On affiche une fenetre de confirmation
			affiche_message('pages','L_PAGE_SUPPRIMEE',formate_url('',true));
			break;
		case 'editer':
			if (!$droits->check($module,0,'editer')){
				error404(802);
			}
			$pages->clean($_GET);
			$tpl->set_filenames(array(
				'pages' => $root.'plugins/modules/pages/html/saisie.html'
			));
			$pages->saisie_texte($pages->id_page);
			break;
		case 'editer_enregistrer':
			if (!$droits->check($module,0,'editer')){
				error404(802);
			}
			$pages->clean($_POST);
			$pages->enregistrer_modification_page();
			// On affiche une fenetre de confirmation
			affiche_message('pages','L_MODIFICATION_ENREGISTREE',formate_url('p='.$pages->id_page,true));
			break;
		default:
			if (!$droits->check($module,0,'voir')){
				error404(803);
			}
			
			// LISTING des documents
			$tpl->set_filenames(array(
				'pages' => $root.'plugins/modules/pages/html/liste_pages.html'
			));
			$tpl->titre_navigateur = $tpl->titre_page = $lang['L_LISTE_PAGES'];
			
			$pages->lister_pages($start);
			break;
	}
}
?>