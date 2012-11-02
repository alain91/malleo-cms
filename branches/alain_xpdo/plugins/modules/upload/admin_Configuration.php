<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
| Documentation : Support : 
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

defined('PROTECT_ADMIN') OR die("Tentative de Hacking");
define('UPLOAD_PATH', dirname(__FILE__));

global $root,$cf,$base_formate_url;

load_lang('config');
load_lang_mod('upload');

$error = false;

function upload_config_valider(&$args)
{
	global $root;
	
	if (!is_numeric($args['upload_filemaxsize'])
		|| !is_numeric($args['upload_dirmaxsize']))
	{
		return false;
	}
	else
	{
		$filesize = intval($args['upload_filemaxsize']);
		$dirsize = intval($args['upload_dirmaxsize']);
		if ($filesize < 1 || $filesize > 4
			|| $dirsize < 1 || $dirsize > 32
			|| $filesize >= $dirsize)
		{
			return false;
		}
		$args['upload_filemaxsize'] = $filesize;
		$args['upload_dirmaxsize'] = $dirsize;
	}
	$rootdir = trim($args['upload_rootdir']);
	if (!file_exists($root.$rootdir))
		return false;
	$args['upload_rootdir'] = $rootdir;
	return true;
}

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'enregistrer':
			if (!upload_config_valider($_POST))
			{
				$error = true;
			}
			else
			{
				$cf->appel_config('MODIFIER', $_POST);
				header('location: '.$base_formate_url);
			}
			break;
	}
	$cf->config('LECTURE');
}

include($root.'fonctions/fct_formulaires.php');
include($root.'class/class_modelisation.php');
$md = new Modelisation();

// Nom des boucles switch dans le .html
$md->nom_switch = 'liste_choix_config_upload';
// Nom du champs page dans la table de modélisation
$md->page = 'Upload';
// Nom de la fonction permettant d'avoir la valeur par défaut (optionnel)
$md->valeur_actuelle = 'valeur_variable_config';
// Lancement du generateur
$md->generer_saisie();

$tpl->set_filenames(array(
	  'body_admin' => UPLOAD_PATH.'/html/admin_config.html'
));

if ($error)
{
	$tpl->assign_block_vars('error', array(
		'MESSAGE'=>utf8_encode('Champ invalide, modification ignorée'),
	));
}
?>