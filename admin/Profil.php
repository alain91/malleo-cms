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
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
require_once($root.'plugins/modules/membres/prerequis.php');
load_lang('utilisateurs');

if (isset($_GET['action']) || isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
	
	switch($action)
	{
		case 'enregistrer':
			$liste_choix = $chps_o;
			if (isset($_POST['liste'])){
				foreach($_POST['liste'] as $key=>$val)
				{
					$liste_choix[] = $val;
				}
			}
			$file = @fopen(PATH_LISTE_CHAMPS_PROFILE, 'w');
			@fwrite($file,serialize($liste_choix));
			@fclose($file);
			break;
	}
}

$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_choix_champs.html'
));

// Récupération des champs configurés dans la modélisation
include_once($root.'class/class_modelisation.php');
$md = new Modelisation();
$md->page = 'Utilisateurs'; // Nom du champs page dans la table de modélisation
// Nous ne sommes pas dans la configuration d'une liste de champs comma la config
// donc on déporte toutes les sorties de fonctions 
$md->deporter = true;
$md->generer_saisie('DEPORTER'); 

// récupération des champs déjà sélectionnés 
$chps_coches = array();
if (file_exists(PATH_LISTE_CHAMPS_PROFILE))
{
	$chps_coches = unserialize(file_get_contents(PATH_LISTE_CHAMPS_PROFILE));
}

$cols=0;
foreach ($md->liste_champs as $key=>$val){

	if($cols%5==0)$tpl->assign_block_vars('cols', array());
	$cols++;
	$checked = $disabled = '';
	if (in_array($key,$chps_o)){
		$checked = ' checked'; 
		$disabled = ' disabled';
	}
	if (in_array($key,$chps_coches)){
		$checked = ' checked'; 
	}
	if ($key=='user_id'){
		$ckecked='';
		$disabled = ' disabled';
	}
	$tpl->assign_block_vars('cols.liste_champs', array(
		'VALUE'		=> $key,
		'LANG'		=> $lang[$val['lang']],
		'CHECKED'	=> $checked,
		'DISABLED'	=> $disabled
	));
}

$tpl->assign_vars(array(
	'COLS'						=> $cols
));
?>