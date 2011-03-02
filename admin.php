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
define('PROTECT',true);
define('PROTECT_ADMIN',true);
$root = './';
$user = array();
require_once($root.'/chargement.php');
$style_name=load_style();
$lang=$erreur=array();
load_lang('defaut');

// Seul les fondateurs ont acces en zone admin
if ($user['level']<10) {error404(22); exit; }

// Entetes
$tpl->assign_vars(array(
	'ROOT_STYLE'		=>	$root,
	'NOM_SITE'			=>	$cf->config['nom_site'],
	'CHARSET'			=>	$cf->config['charset'],
	'STYLE'				=>	$style_name,
));

// CHARGEMENT du module demandé
$module =  (isset($_GET['module']))? $_GET['module']:$cf->config['default_module_admin'];
$base_formate_url = 'admin.php?module='.$module;
$session->make_navlinks($lang['L_ACCUEIL'],formate_url('index.php?module='.$cf->config['default_module']));
$session->make_navlinks($lang['L_ACCUEIL_ADMIN'],formate_url('admin.php?module='.$cf->config['default_module_admin']));

if (!session_id()) session_start();
// Verification de la session Fondateur par digicode
if ($cf->config['activer_digicode'] 
	&& (!isset($_SESSION['digicode_TTL']) || ($_SESSION['digicode_TTL']<($session->time-900)))){
	load_lang('digicode');
	include_once($root.'class/class_digicode.php');
	$digicode = new digicode();
	$digicode->retour = $base_formate_url;
	$session->make_navlinks($lang['L_ADMIN_AUTH_DIGICODE'],formate_url('',true));
	if (isset($_POST['entrer'])){
		$digicode->verifier_code($_POST);
	}else{
		$digicode->afficher_digicode();
	}
	define('ERROR_404',true);
}else{
	// Mise a jour de la date d'activite de la session fondateur
	if ($cf->config['activer_digicode']) $_SESSION['digicode_TTL'] = $session->time;
	
	if (isset($_POST['IRQ']))
	{
		include_once($root.'admin/includes/inc_'.eregi_replace("[^a-z0-9_-]",'',$_POST['IRQ']).'.php');
		exit;
	}
	$file = $root.$module;
	if (file_exists($file) && is_file($file) 
		&& (preg_match('#'.preg_quote(dirname(realpath(__FILE__)), '#').'#i',dirname(realpath($file)))))
	{
		include_once($file);
	}else{
		error404(23);
	}
}

// AFFICHAGE de la page
include_once($root.'page_haut.php');
$tpl->pparse('body_admin');
include_once($root.'page_bas.php');
$tpl->afficher_page();
?>