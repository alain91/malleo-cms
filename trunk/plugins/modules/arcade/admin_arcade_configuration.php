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

|------------------------------------------------------------------------------------------------------------
*/
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
load_lang('config');
load_lang_mod('arcade');

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'enregistrer':
			$cf->appel_config('MODIFIER', $_POST);
			header('location: '.$base_formate_url);
			break;
	}
	$cf->config('LECTURE');
}

include($root.'fonctions/fct_formulaires.php');
include($root.'class/class_modelisation.php');
$md = new Modelisation();

// Nom des boucles switch dans le .html
$md->nom_switch = 'liste_choix_config_arcade';
// Nom du champs page dans la table de modélisation
$md->page = 'Arcade';
// Nom de la fonction permettant d'avoir la valeur par défaut (optionnel)
$md->valeur_actuelle = 'valeur_variable_config';
// Lancement du generateur
$md->generer_saisie();

$tpl->set_filenames(array(
	  'body_admin' => $root.'plugins/modules/arcade/html/admin_arcade_configuration.html'
));
$tpl->assign_vars(array(
	'L_GESTION_PARAMETRES'	=> $lang['L_GESTION_PARAMETRES'],
	'L_EXPLAIN_PARAMETRES'	=> $lang['L_EXPLAIN_PARAMETRES'],
	'L_CONFIG_GENERALE'		=> $lang['L_CONFIG_GENERALE'],
	'L_PARAMETRE'			=> $lang['L_PARAMETRE'],
	'L_VALEUR'				=> $lang['L_VALEUR'],
	'L_ENREGISTRER'			=> $lang['L_ENREGISTRER']
));
?>