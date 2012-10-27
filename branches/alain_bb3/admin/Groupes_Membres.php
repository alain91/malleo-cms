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
load_lang('groupes');
if (!isset($_GET['id_groupe'])&&!isset($_POST['id_groupe']))error404();

$group_id = intval($_GET['id_groupe']);
$tpl->set_filenames(array('body_admin' => $root.'html/admin_groupes_membres.html'));


// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch($action)
	{
		case 'ajouter':
			// RECHERCHE de l'ID correspondant au pseudo
			$group_id = intval($_POST['id_groupe']);
			$sql = 'SELECT `user_id` FROM '.TABLE_USERS.'
			WHERE `pseudo`=\''.str_replace("\'","''",$_POST['membre']).'\' LIMIT 1';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
			if ($c->sql_numrows($resultat)==0)
			{
				erreur_saisie('erreur_saisie',$lang['L_PSEUDO_DONT_EXISTS']);
			}else{
				$row = $c->sql_fetchrow($resultat);
				$sql = 'SELECT group_id FROM '.TABLE_GROUPES_INDEX.' 
						WHERE `user_id`=\''.$row['user_id'].'\'
						AND `group_id`='.$group_id.' LIMIT 1';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
				if ($c->sql_numrows($resultat)==0){
					// ENREGISTREMENT
					$sql = 'INSERT INTO '.TABLE_GROUPES_INDEX.' (group_id, user_id, accepte) 
							VALUES ('.$group_id.','.$row['user_id'].',1)';
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,42,__FILE__,__LINE__,$sql);
					header('location: '.$base_formate_url.'&id_groupe='.$group_id);
				}
			}
			break;
		case 'supprimer':
			$sql = 'DELETE FROM '.TABLE_GROUPES_INDEX.' WHERE group_id='.$group_id.' AND user_id='.intval($_GET['user_id']);
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,43,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url.'&id_groupe='.$group_id);
			break;
	}
}

$tpl->assign_block_vars('groupe_manuel', array());
$sql = 'SELECT g.user_id, u.pseudo
		FROM '.TABLE_GROUPES_INDEX.' AS g LEFT JOIN '.TABLE_USERS.' AS u 
		ON (g.user_id=u.user_id)
		WHERE group_id='.$group_id.'
		ORDER BY pseudo ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 


// AFFICHAGE de la liste des membres du groupe
if ($c->sql_numrows($resultat)==0)
{
	$tpl->assign_block_vars('liste_vide', array());
}else{
	while($row = $c->sql_fetchrow($resultat))
	{
		$tpl->assign_block_vars('liste_membres', array(
			'PSEUDO'	=> formate_pseudo($row['user_id'], $row['pseudo']),
			'S_SUPP'	=> formate_url('action=supprimer&user_id='.$row['user_id'].'&id_groupe='.$group_id,true)
		));
	}
}
	
	
$tpl->assign_vars(array(
	'I_EFFACER'			=> $img['effacer'],
	'S_RETOUR'			=> formate_url('admin.php?module=admin/Groupes.php'),
	'ID_GROUPE'			=> $group_id
));

?>