<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
$fichier = $root.'config/config.php';
if (file_exists($fichier) && (filesize($fichier) > 0)){
	header('location: index.php?etape=5');
}


$tpl->assign_vars(array(
	'L_EXPLAIN_MySQL'			=> $lang['L_EXPLAIN_MySQL'],
	'L_ADRESSE_BASE_DONNEES'	=> $lang['L_ADRESSE_BASE_DONNEES'],
	'L_NOM_BASE_DONNEES'		=> $lang['L_NOM_BASE_DONNEES'],
	'L_NOM_UTILISATEUR'			=> $lang['L_NOM_UTILISATEUR'],
	'L_MOT_DE_PASSE'			=> $lang['L_MOT_DE_PASSE'],
	'L_SAISISSEZ_PARAMETRES'	=> $lang['L_SAISISSEZ_PARAMETRES'],
	'L_TESTER_ACCES_BASE'		=> $lang['L_TESTER_ACCES_BASE'],
	'L_PATIENTER'				=> $lang['L_PATIENTER'],
	'IMG_VALIDE'				=> $img['valide'],
	'IMG_INVALIDE'				=> $img['invalide'],
));
?>