<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  BalHack - http://www.balhack.com
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Balhack & Stephane RAJALU
|  Copyright (c) 2008-2009, Balhack & Stephane RAJALU All Rights Reserved
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
load_lang('bots');
$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_bots_gestion.html'
));
$hidden = '<input type="hidden" name="action" value="ajouter" />';
// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'supprimer':	$bots->supprime_bot(intval($_GET['id_robot']));		header('location: '.$base_formate_url);break;
		case 'activer':		$bots->active_robot(intval($_GET['id_robot']));		header('location: '.$base_formate_url);break;
		case 'desactiver':	$bots->desactive_robot(intval($_GET['id_robot']));	header('location: '.$base_formate_url);break;				
		case 'ajouter' :	$bots->ajoute_robot($_POST);						header('location: '.$base_formate_url);break;
		case 'editer':		$bots->edite_robot($_POST);							header('location: '.$base_formate_url);break;
		case 'edit':
			$sql = 'SELECT id_robot, robot_user_agent, robot_name, robot_url  
					FROM ' . TABLE_ROBOTS . '
					WHERE id_robot='.intval($_GET['id_robot']);
			if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
			$row = $c->sql_fetchrow($resultat);
			$tpl->assign_vars(array(
				'ROBOT_NAME'	=> $row['robot_name'],
				'USER_AGENT'	=> $row['robot_user_agent'],
				'ROBOT_URL'		=> $row['robot_url'],
				'L_AJOUTER_ROBOT'	=> $lang['L_EDITER_ROBOT'],
				'L_AJOUTER'			=> $lang['L_EDITER'],
			));
			$hidden = '<input type="hidden" name="action" value="editer" />
						<input type="hidden" name="id_robot" value="'.$row['id_robot'].'" />';
			break;
	}
}

function str_court($string){
	if(strlen($string)>=40){
		$string = substr($string, 0, 37).'...';
	}
	return $string;
}


//
// AFFICHAGE
$sql = 'SELECT id_robot, robot_user_agent, robot_name, robot_url, robot_actif 
		FROM ' . TABLE_ROBOTS . '
		ORDER BY robot_name ASC';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1300,__FILE__,__LINE__,$sql); 
$i=0;
$nbre_bots_inactifs = 0;
while($row = $c->sql_fetchrow($resultat))
{
	$couleur = ($i % 2) ? "row1" : "row2" ; 
	$i++;
	$handle = ($row['robot_actif']==1)?	'liste_actifs':'liste_inactifs';
	if ($row['robot_actif']==0) $nbre_bots_inactifs++;
	$tpl->assign_block_vars($handle, array(
		'ROW_COL'				=> $couleur,
		'ROBOT_NAME'			=> $row['robot_name'],
		'ROBOT_NAME_COURT'		=> str_court($row['robot_name']),
		'ROBOT_USER_AGENT'		=> $row['robot_user_agent'],
		'ROBOT_USER_AGENT_COURT'=> str_court($row['robot_user_agent']),
		'ROBOT_URL'				=> $row['robot_url'],
		'ROBOT_URL_COURT'		=> str_court($row['robot_url']),
		'U_EDITER'				=> formate_url('action=edit&id_robot='.$row['id_robot'],true),
		'U_SUPPRIMER'			=> formate_url('action=supprimer&id_robot='.$row['id_robot'],true),
		'U_ACTIVER'				=> formate_url('action=activer&id_robot='.$row['id_robot'],true),
		'U_DESACTIVER'			=> formate_url('action=desactiver&id_robot='.$row['id_robot'],true)
	));
}
if ($nbre_bots_inactifs == 0) $tpl->assign_block_vars('liste_inactifs_vide', array());


$tpl->assign_vars(array(
	'I_SUPPR'		=> $img['effacer'],
	'I_EDIT'		=> $img['editer'],
	'I_ACTIVER'		=> $img['valide'],
	'I_DESACTIVER'	=> $img['invalide'],
	'HIDDEN'		=> $hidden
));

?>