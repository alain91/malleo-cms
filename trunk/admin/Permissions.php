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
load_lang('droits');

$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_permissions.html'
));

// initialisation
$droits->regles = $cache->appel_cache('listing_regles',true);
$droits->liste_groupes = array(1,2,3);
$module = null;

// Enregistrement des changements
if (isset($_POST['enregistrer'])){
	$droits->clean_regles_recues($_POST);
}

// Selections par defaut
$tpl->assign_vars(array(
	'CHECK_PARTIELLE'	=> '',
	'CHECK_TOTALE'		=> 'checked="checked"',
	'CHECK_DEFAUT'		=> 'checked="checked"',
	'CHECK_TOUS'		=> '', 
	'CHECK_UTILISATEURS'=> '' 
));

// Changement de vue
if (isset($_POST['generer']) || isset($_GET['generer'])){

	//Choix des groupes
	$liste_groupes = '';
	if (isset($_POST['defaut']) || isset($_GET['defaut'])){
		// Groupes par defaut : invites, membres, admins
		$liste_groupes = array(1,2,3);
	}
	if (isset($_POST['tous']) || isset($_GET['tous'])){
		// Groupes crees par le webmaster
		$liste_groupes = (is_array($liste_groupes))? array_merge($liste_groupes,$droits->lister_groupes_manuels()):$droits->lister_groupes_manuels();
	}
	if (isset($_POST['utilisateurs']) || isset($_GET['utilisateurs'])){
		// Groupes utilisateurs (chaque utilisateur possede un groupe personnel)
		$liste = (isset($_POST['utilisateurs']))? $_POST['liste_utilisateurs']:$_GET['utilisateurs'] ;
		$liste = $droits->search_users(explode(',',$liste));
		$nbre_users = count($liste);
		for ($i=0;$i<$nbre_users;$i++){
			$liste_groupes =  (is_array($liste_groupes))? array_merge($liste_groupes, array($droits->search_id_group($liste[$i]))):array($droits->search_id_group($liste[$i]));
		}
	} 
	if ($liste_groupes != '') $droits->liste_groupes = $liste_groupes;
	
	// Choix des modules affiches
	if (isset($_POST['vue']) && $_POST['vue']=='partielle' && isset($_POST['noeuds'])){
		$module = $_POST['noeuds'];
	}elseif(isset($_GET['noeuds'])){
		$module = explode(',',$_GET['noeuds']);
	}
	$tpl->assign_vars(array(
		'CHECK_PARTIELLE'	=> ($module!=null)?														'checked="checked"':'',
		'CHECK_TOTALE'		=> ($module==null)?														'checked="checked"':'',
		'CHECK_DEFAUT'		=> (isset($_POST['defaut'])			|| isset($_GET['defaut']))?			'checked="checked"':'',
		'CHECK_TOUS'		=> (isset($_POST['tous'])			|| isset($_GET['tous']))?			'checked="checked"':'',
		'CHECK_UTILISATEURS'=> (isset($_POST['utilisateurs'])	|| isset($_GET['utilisateurs']))?	'checked="checked"':''
	));
	$droits->lister_modules('liste_modules',$module);
}

// liste des modules
$sql = 'SELECT module, virtuel FROM '.TABLE_MODULES.' ORDER BY module ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,52,__FILE__,__LINE__,$sql);
$liste_modules='';
while($row = $c->sql_fetchrow($resultat))
{
	$virtuel = ($row['virtuel']!='')? ' ('.$row['virtuel'].'()':'';
	$selected = (isset($module) && in_array($row['module'],$module))? ' selected="selected"':'';
	$liste_modules .= "\n".'<option value="'.$row['module'].'"'.$selected.'>'.$row['module'].$virtuel .'</option>';
}

$tpl->assign_vars(array(
	'I_DELETE'						=>	$img['effacer'],
	'LISTE_MODULES'					=>	$liste_modules,
));
?>