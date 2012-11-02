<?php
define('PROTECT',true);
define('PROTECT_ADMIN',true);
$root = '../';
include_once($root.'install/chargement.php');
		
$etape = null;
if(isset($_GET['etape'])||isset($_POST['etape'])){
	$etape=(isset($_POST['etape']))?$_POST['etape']:$_GET['etape'];
}
switch($etape){
	// Acceptation de la licence
	case '0':
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape0.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_INSTALLATION_MALLEO'];
		include_once($root.'install/etape0.php');
		break;
	// VERSION des COMPOSANTS
	case '1': 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape1.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_VERIFICATION_VERSIONS'];
		include_once($root.'install/etape1.php');
		break;
	// VERIFICATION des CHMODS
	case '2': 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape2.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_VERIFICATION_CHMODS'];
		include_once($root.'install/etape2.php');
		break;
	// SAISIE des PARAMETRES MySQL
	case '3': 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape3.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_SAISIE_PARAMETRES'];
		include_once($root.'install/etape3.php');
		break;
	// CREATION du fichier config.php
	case '4': 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape4.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_CREATION_CONFIG'];
		include_once($root.'install/etape4.php');
		break;
	// CREATION de la base de données
	case '5': 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape5.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_CREATION_BASE'];
		include_once($root.'install/etape5.php');
		break;
	// CREATION du Fondateur
	case '6': 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape6.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_CREATION_FONDATEUR'];
		include_once($root.'install/etape6.php');
		break;
	// INSTALLATION des modules
	case '7':
		include_once($root.'install/etape7.php');
		break;
	// SUPPRESSION du dossier install/
	case '8':
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/etape8.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_SUPPRESSION_DOSSIER'];
		$tpl->assign_vars(array(
			'L_EXPLAIN_SUPPRESSION_DOSSIER' => $lang['L_EXPLAIN_SUPPRESSION_DOSSIER']
		));
		break;
	default: 
		$tpl->set_filenames(array(
		  'body_install' => $root.'install/html/install.html'
		));
		$tpl->titre_page = $tpl->titre_navigateur = $lang['L_INSTALLATION_MALLEO'];
		$tpl->assign_vars(array(
			'L_PRESENTATION_MALLEO' => $lang['L_PRESENTATION_MALLEO'],
			'L_PRESENTATION_LICENCE' => $lang['L_PRESENTATION_LICENCE'],
			'L_PRESENTATION_ETAPE1' => $lang['L_PRESENTATION_ETAPE1'],
			'L_PRESENTATION_ETAPE2' => $lang['L_PRESENTATION_ETAPE2'],
			'L_PRESENTATION_ETAPE3' => $lang['L_PRESENTATION_ETAPE3'],
			'L_PRESENTATION_ETAPE4' => $lang['L_PRESENTATION_ETAPE4'],
			'L_PRESENTATION_ETAPE5' => $lang['L_PRESENTATION_ETAPE5'],
			'L_PRESENTATION_ETAPE6' => $lang['L_PRESENTATION_ETAPE6'],
			'L_PRESENTATION_ETAPE7' => $lang['L_PRESENTATION_ETAPE7'],
			'L_PRESENTATION_ETAPE8' => $lang['L_PRESENTATION_ETAPE8'],

			'URL_IMAGE_LOGO'		=> $root.'data/images/logo.png'
		));
		break;
}

$tpl->assign_vars(array(
	'L_ACCUEIL' 			=> $lang['L_ACCUEIL'],
	'L_LIBELLE_LICENCE' 	=> $lang['L_LIBELLE_LICENCE'],
	'L_LIBELLE_ETAPE1' 		=> $lang['L_LIBELLE_ETAPE1'],
	'L_LIBELLE_ETAPE2' 		=> $lang['L_LIBELLE_ETAPE2'],
	'L_LIBELLE_ETAPE3' 		=> $lang['L_LIBELLE_ETAPE3'],
	'L_LIBELLE_ETAPE4' 		=> $lang['L_LIBELLE_ETAPE4'],
	'L_LIBELLE_ETAPE5' 		=> $lang['L_LIBELLE_ETAPE5'],
	'L_LIBELLE_ETAPE6' 		=> $lang['L_LIBELLE_ETAPE6'],
	'L_LIBELLE_ETAPE7'		=> $lang['L_LIBELLE_ETAPE7'],
	'L_LIBELLE_ETAPE8'		=> $lang['L_LIBELLE_ETAPE8'],
	'L_ALERTE_CORRECTION' 	=> $lang['L_ALERTE_CORRECTION']
));


include_once($root.'install/page_haut.php');
$tpl->pparse('body_install');
include_once($root.'install/page_bas.php');
$tpl->afficher_page();
?>