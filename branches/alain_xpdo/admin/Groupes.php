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
$icone_par_defaut = $edit_icone = $liste_icones = '';
$chemin_icones = 'data/icones_groupes/';
$ext_ok = array('gif','GIF','png','PNG','jpg','JPG','jpeg','JPEG');
$hidden = '<input type="hidden" name="action" value="ajouter" />';
$visible = ' checked="checked"';

$tpl->set_filenames(array('body_admin' => $root.'html/admin_gestion_groupes.html'));

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch($action)
	{
		case 'move':
				$sens  = ($_GET['sens']=='up')? '+':'-';
				require_once($root.'fonctions/fct_formulaires.php');
				deplacer_id_tableau(TABLE_GROUPES, 'group_id', 'ordre', 'ASC', intval($_GET['id_groupe']), $sens, ' WHERE type=1');
				header('location: '.$base_formate_url);
				break;
		case 'ajouter':
				if ($_POST['boss']=='' || empty($_POST['titre']) || 
						empty($_POST['icone']) || empty($_POST['couleur'])){
						erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
								'TITRE'			=> stripslashes($_POST['titre']),
								'DESCRIPTION'	=> stripslashes($_POST['description']),
								'COULEUR'		=> stripslashes($_POST['couleur']),
								'BOSS'			=> stripslashes($_POST['boss'])));
				}else{		
					// RECHERCHE de l'ID correspondant au pseudo
					$sql = 'SELECT user_id FROM '.TABLE_USERS.' 
					WHERE pseudo=\''.str_replace("\'","''",$_POST['boss']).'\' LIMIT 1';	
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
					if ($c->sql_numrows($resultat)==1)
					{
						$row = $c->sql_fetchrow($resultat);
						// ENREGISTREMENT
						$sql = 'INSERT INTO '.TABLE_GROUPES.' (titre, description, icone, type, couleur, user_id, visible) 
								VALUES (
								"'.protection_chaine($_POST['titre']).'",
								"'.protection_chaine($_POST['description']).'",
								"'.protection_chaine($_POST['icone']).'",
								1,
								"'.protection_chaine($_POST['couleur']).'",
								'.$row['user_id'].',
								'.((isset($_POST['visible']))?'true':'false').'
								) ';
						if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,38,__FILE__,__LINE__,$sql);
					}
					header('location: '.$base_formate_url);
				}
				break;
		case 'editer':					
				if (($_POST['boss']==''&& $_POST['id_groupe']>3) || $_POST['titre']=='' || 
						$_POST['icone']=='' || $_POST['couleur']==''){
						erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
								'TITRE'			=> stripslashes($_POST['titre']),
								'DESCRIPTION'	=> stripslashes($_POST['description']),
								'COULEUR'		=> stripslashes($_POST['couleur']),
								'BOSS'			=> stripslashes($_POST['boss'])));
						$hidden = '<input type="hidden" name="id_groupe" value="'.intval($_POST['id_groupe']).'" />
									<input type="hidden" name="action" value="editer" />';
				}else{
					// RECHERCHE de l'ID correspondant au pseudo
					$sql = 'SELECT user_id FROM '.TABLE_USERS.' 
					WHERE pseudo="'.str_replace("\'","''",$_POST['boss']).'" LIMIT 1';
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql);
					if ($c->sql_numrows($resultat)==0){
						erreur_saisie('erreur_saisie',$lang['L_BOSS_DONT_EXISTS'],array(
								'TITRE'			=> stripslashes($_POST['titre']),
								'DESCRIPTION'	=> stripslashes($_POST['description']),
								'COULEUR'		=> stripslashes($_POST['couleur']),
								'BOSS'			=> stripslashes($_POST['boss'])));
						$hidden = '<input type="hidden" name="id_groupe" value="'.intval($_POST['id_groupe']).'" />
									<input type="hidden" name="action" value="editer" />';
					}else{
						$user_id= ($row = $c->sql_fetchrow($resultat))?$row['user_id']:'NULL';
						$sql = 'UPDATE '.TABLE_GROUPES.' SET 
									titre="'.		protection_chaine($_POST['titre']).'",
									description="'.	protection_chaine($_POST['description']).'",
									icone="'.		protection_chaine($_POST['icone']).'",
									couleur="'.		protection_chaine($_POST['couleur']).'",
									visible='.((isset($_POST['visible']))?'true':'false').',
									user_id='.		$user_id.'
								WHERE group_id='.intval($_POST['id_groupe']);
						if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,39,__FILE__,__LINE__,$sql);
						header('location: '.$base_formate_url);
					}
				}
				break;
		case 'supprimer':
				// Suppression des regles associees au groupe
				$droits->delete_regle('groupe',intval($_GET['id_groupe']));				
				// Suppression de l'index et de la table groupe
				$sql = 'DELETE g,i FROM '.TABLE_GROUPES.' AS g 
						LEFT JOIN '.TABLE_GROUPES_INDEX.' AS i
						ON	(g.group_id=i.group_id) 
						WHERE g.group_id='.intval($_GET['id_groupe']);
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,40,__FILE__,__LINE__,$sql);
				header('location: '.$base_formate_url);
				break;
		case 'edit':
				// PREPARATION de l'édition
				$sql = 'SELECT titre, description, icone, type, couleur, visible, g.user_id, u.pseudo 
				FROM '.TABLE_GROUPES.' AS g LEFT JOIN '.TABLE_USERS.' AS u ON (g.user_id=u.user_id) 
				WHERE group_id='.intval($_GET['id_groupe']).' LIMIT 1';	
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,39,__FILE__,__LINE__,$sql); 
				$row = $c->sql_fetchrow($resultat);
				$hidden = '<input type="hidden" name="id_groupe" value="'.intval($_GET['id_groupe']).'" /><input type="hidden" name="action" value="editer" />';
				$edit_icone = $row['icone'];
				$visible = ($row['visible']==true)?' checked="checked"':'';
				$tpl->assign_vars(array(
						'TITRE'			=> $row['titre'],
						'DESCRIPTION'	=> $row['description'],
						'COULEUR'		=> $row['couleur'],
						'BOSS'			=> $row['pseudo']
				));				
				break;
	}
}

//
// LISTING des groupes
// type = 1 : groupes multi-users et non uniques
$sql = 'SELECT g.group_id, g.titre, g.description, g.icone, g.type, g.ordre, g.visible, g.couleur, g.user_id, u.pseudo
		FROM '.TABLE_GROUPES.' AS g LEFT JOIN '.TABLE_USERS.' AS u 
		ON (g.user_id=u.user_id)
		WHERE type=1 
		ORDER BY ordre ASC, titre ASC';

if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
$nbre_groupes = ($c->sql_numrows($resultat) - 3);
$t=1;
while($row = $c->sql_fetchrow($resultat))
{	
	$tpl->assign_block_vars('liste_groupes', array(
		'TITRE'			=> formate_groupe($row['titre'], $row['group_id'], $row['couleur']),
		'DESCRIPTION'	=> $row['description'],
		'VISIBLE'		=> ($row['visible']==true)? $lang['L_OUI']:$lang['L_NON'],
		'ICONE'			=> $chemin_icones.$row['icone'],
		'BOSS'			=> ($row['user_id']!=null)?formate_pseudo($row['user_id'],$row['pseudo']):$lang['L_NON'],
		'S_MEMBRES'		=> formate_url('admin.php?module=admin/Groupes_Membres.php&id_groupe='.$row['group_id']),
		'S_UP'			=> formate_url('action=move&sens=up&id_groupe='.$row['group_id'],true),
		'S_DOWN'		=> formate_url('action=move&sens=down&id_groupe='.$row['group_id'],true),
		'S_EDIT'		=> formate_url('action=edit&id_groupe='.$row['group_id'],true),
		'S_SUPP'		=> formate_url('action=supprimer&id_groupe='.$row['group_id'],true)
	));
	
	// On protege les groupes de base
	if ($row['group_id']>3){
		$tpl->assign_block_vars('liste_groupes.editables', array());
	
		// Monter / descendre
		if ($nbre_groupes>1 && $t>1) $tpl->assign_block_vars('liste_groupes.editables.monter',array());
		if ($nbre_groupes>1 && $t<$nbre_groupes) $tpl->assign_block_vars('liste_groupes.editables.descendre',array());
		$t++;
	}
}

//
// Listing des icones 
$ch = @opendir($chemin_icones);
while ($icone = @readdir($ch))
{
	$ext = pathinfo($icone);
	if ($icone != "." && $icone != ".." && in_array($ext['extension'],$ext_ok)) {
		$icone_par_defaut = ($icone_par_defaut == '')? (($edit_icone != '')?$edit_icone:$icone):$icone_par_defaut;
		$selected = ($edit_icone == $icone)?' selected':'';
		$liste_icones .= "\n ".'<option value="'.$icone.'"'.$selected.'>'.preg_replace('/.'.$ext['extension'].'/','',$icone).'</option>';
	}
}
@closedir($ch);


$tpl->assign_vars(array(
	'L_ADMIN_GESTION_GROUPES'			=>	$lang['L_ADMIN_GESTION_GROUPES'],
	'I_PICKER'							=>	$img['picker'],
	'ICONE_PAR_DEFAUT'					=>	$chemin_icones.$icone_par_defaut,
	'I_EDITER'							=>	$img['editer'],
	'I_EFFACER'							=>	$img['effacer'],
	'I_UP'								=>	$img['up'],
	'I_DOWN'							=>	$img['down'],
	'I_MEMBRES'							=>	$img['membres'],
	'VISIBLE'							=>	$visible,
	'HIDDEN'							=>	$hidden,
	'ICONE'								=>	$liste_icones
));

?>
