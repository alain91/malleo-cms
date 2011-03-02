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
global $lang;
load_lang('utilisateurs');

$pseudo = $action = '';
if (isset($_GET['action']) || isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
}
$user_id = intval((isset($_POST['user_id']))?$_POST['user_id']:$_GET['user_id']);


include_once($root.'fonctions/fct_formulaires.php');
// Récupération des champs configurés dans la modélisation
include_once($root.'class/class_modelisation.php');
$md = new Modelisation();
$md->page = 'Utilisateurs'; // Nom du champs page dans la table de modélisation
// Nous ne sommes pas dans la configuration d'une liste de champs comma la config
// donc on déporte toutes les sorties de fonctions 
$md->deporter = true;
$liste_champs = $md->generer_saisie('DEPORTER'); // Lancement du generateur et Récupération des champs configurés sous la forme champs1, champs2, ...

//
// Traitement de la demande
switch($action)
{

	case 'modifier':
		if ($user_id > 0)
		{
			$sql = 'SELECT '.$liste_champs.'  FROM '.TABLE_USERS.' WHERE user_id='.$user_id;
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,18,__FILE__,__LINE__,$sql);
			$row = $c->sql_fetchrow($resultat);
			
			$liste_set = '';
			foreach (explode(',',$liste_champs) as $key=> $val)
			{
				 if (isset($_POST[$val]) && $_POST[$val] != $row[$val])
				 {
					if ($liste_set != '') $liste_set .= ',';
					$liste_set .= ' '.$val.' = \''. $_POST[$val].'\'';
				 }
			}
			if ($liste_set != '')
			{
				$sql = 'UPDATE '.TABLE_USERS.' SET '.$liste_set.' WHERE user_id='.$user_id;
				if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,19,__FILE__,__LINE__,$sql);
			}
		}
		break;
}
$sql = 'SELECT '.$liste_champs.'  FROM '.TABLE_USERS.' WHERE user_id='.$user_id;
				
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,20,__FILE__,__LINE__,$sql);
if ($c->sql_numrows($resultat) == 0)
{
	$tpl->assign_block_vars('AUCUN_RESULTAT', array());
}else{
	$row = $c->sql_fetchrow($resultat);
	foreach ($md->liste_champs as $key=>$val)
	{
		$md->valeur_actuelle = $row[$val['nom_champs']];
		$tpl->assign_block_vars('ligne', array(
			'CASE'			=> $md->ajouter_champ($val['nom_champs'],$val['type_saisie'],$val['param']),
			'TITRE_CHAMPS'	=> (array_key_exists($val['lang'],$lang))?$lang[$val['lang']]:$val['lang']
		));
	}
	$pseudo = $row['pseudo'];
}

$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_edit_profiles.html'
));


$tpl->assign_vars(array(
	'L_PROFILE_OF'			=> sprintf($lang['L_PROFILE_OF'],$pseudo),
	'S_RETOUR'				=> formate_url('admin.php?module=admin/Utilisateurs.php')

));
?>