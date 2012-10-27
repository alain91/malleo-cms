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
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
if (!$droits->check($module,0,'voir_membres')){
	error404(1030);
	exit;
}
require_once($root.'plugins/modules/membres/prerequis.php');
include($root.'fonctions/fct_formulaires.php');
if (!function_exists('formate_sexe')) include_once($root.'fonctions/fct_profil.php');
load_lang('utilisateurs');
unset($pagination);

// récupération de la liste des champs à afficher
// SINON on affiche seulement les champs obligatoires
if (file_exists(PATH_LISTE_CHAMPS_PROFILE))
{
	$chps_o = unserialize(file_get_contents(PATH_LISTE_CHAMPS_PROFILE));
}


// Récupération des champs configurés dans la modélisation
include_once($root.'class/class_modelisation.php');
$md = new Modelisation();
$md->page = 'Utilisateurs'; // Nom du champs page dans la table de modélisation
// Nous ne sommes pas dans la configuration d'une liste de champs comma la config
// donc on déporte toutes les sorties de fonctions 
$md->deporter = true;
$md->generer_saisie('DEPORTER'); // Lancement du generateur et Récupération des champs configurés sous la forme champs1, champs2, ...

// REFERENCEMENT
// Titre de page 
$tpl->titre_navigateur = $lang['L_LISTE_MEMBRES'];
$tpl->titre_page = $lang['L_LISTE_MEMBRES'];
// Navlinks
$session->make_navlinks($lang['L_LISTE_MEMBRES'],formate_url('',true));

// INIT
$where = $pagination = $jointure = '';
$start = (isset($_GET['start']) && $_GET['start']>0)? intval($_GET['start']):0;
$order = (isset($_GET['order']))? preg_replace('/[^a-z]/','',$_GET['order']):$cf->config['membres_order'];

$action = null;
if (isset($_GET['action']) ||isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
}
switch($action){
	case 'search':
		// RECHERCHE d'un utilisateur
		if (!$droits->check($module,0,'rechercher')){
			error404(1031);
		}
		if (isset($_POST['recherche']) || isset($_POST['recherche'])){
		
			// PSEUDO ou EMAIL saisi
			$recherche = (isset($_POST['recherche']))? $_POST['recherche']:$_GET['recherche'];
			$pagination = 'recherche='.$recherche.'&';
			$recherche = str_replace("\'","''",nettoyage_nom($recherche));
			$where = ' AND (pseudo LIKE \'%'.$recherche.'%\' OR email LIKE \'%'.$recherche.'%\')';
		}else{
		
			// Lettre ou mot clef saisi
			switch($_GET['chercher']){
				//Commencant par un chiffre
				case '123':	$where = 'AND pseudo REGEXP "^[0-9]"';break;
				// Commencant ni par une lettre, ni par un chiffre
				case '%':$where = 'AND pseudo REGEXP "^[^[:alnum:]]"';break; 
				// Dernier inscrits
				case $lang['L_TOUS']: $where = '';break;
				//Commencant par la lettre specifiee
				default :$where = 'AND pseudo REGEXP "^'.str_replace("\'","''",$_GET['chercher']).'"';break;
			}
			$pagination = 'action=search&chercher='.$_GET['chercher'].'&';			
		}
		break;
	case 'groupe':
		// AFFICHAGE d'un groupe
		if (!$droits->check($module,0,'voir_groupes')){
			error404(1032);
		}
		$id_groupe = intval($_GET['groupe']);
		$sql = 'SELECT titre, description, couleur, icone, g.user_id, u.avatar, u.pseudo, u.rang, u.msg 
				FROM '. TABLE_GROUPES .' AS g
				LEFT JOIN '.TABLE_USERS.' AS u
					 ON (g.user_id=u.user_id)
				WHERE g.group_id='.$id_groupe.' AND type=1 LIMIT 1';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
		$row = $c->sql_fetchrow($resultat);
		$tpl->assign_block_vars('infos_groupe', array());
		$tpl->assign_vars(array(
			'L_TITRE_GROUPE'	=> $row['titre'],
			'DESCRIPTION'		=> $row['description'],
			'AVATAR'			=> $row['avatar'],
			'COULEUR'			=> $row['couleur'],
			'ICONE'				=> 'data/icones_groupes/'.$row['icone'],
			'RANG'				=> formate_rang($row['rang'],$row['msg']),
			'PSEUDO'			=> formate_pseudo($row['user_id'],$row['pseudo']),
		));
		// Titre de page 
		$tpl->titre_navigateur = $row['titre'];
		$tpl->titre_page = $row['titre'];
		// Navlinks
		$session->make_navlinks(sprintf($lang['L_GROUPE_MEMBRES'],$row['titre']),formate_url('action=groupe&groupe='.$id_groupe,true));
		$tpl->meta_description = $row['description'];
		
		//
		// MEMBRES non acceptes
		if ($user['level'] == 10 || $user['user_id']==$row['user_id']){
			$sql = 'SELECT i.user_id, u.avatar, u.pseudo, u.rang, u.msg 
					FROM '.TABLE_GROUPES.' AS g
					LEFT JOIN '. TABLE_GROUPES_INDEX .' AS i 
						ON (g.group_id=i.group_id) 
					LEFT JOIN '. TABLE_USERS .' AS u 
						ON (i.user_id=u.user_id) 
					WHERE i.group_id='.$id_groupe.' AND accepte=0   
					ORDER BY pseudo ASC';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			if ($c->sql_numrows($resultat)>0){
				$tpl->assign_block_vars('en_attente', array());
				while($row = $c->sql_fetchrow($resultat)){
					$tpl->assign_block_vars('en_attente.liste', array(
						'PSEUDO'	=> formate_pseudo($row['user_id'],$row['pseudo']),
						'RANG'		=> formate_rang($row['rang'],$row['msg']),
						'S_EMAIL'	=> formate_url('index.php?module=messagerie&mode=newmail&a='.$row['pseudo']),
						'S_MP'		=> formate_url('index.php?module=messagerie&mode=newmp&a='.$row['pseudo']),
						'S_OK'		=> formate_url('action=valider&id_groupe='.$id_groupe.'&user_id='.$row['user_id'],true),
						'S_NOK'		=> formate_url('action=refuser&id_groupe='.$id_groupe.'&user_id='.$row['user_id'],true),
					));
				}
			}
		}
		// INFORMATIONS sur les membres du groupe
		$jointure = ' LEFT JOIN '.TABLE_GROUPES_INDEX.' AS i ON (u.user_id=i.user_id) ';
		$where = ' AND accepte=1 AND i.group_id='.$id_groupe;
		$pagination = 'action=groupe&groupe='.$id_groupe.'&';	
		break;
	case 'valider':
		// Validation du user dans le groupe si c'est le leader ou un fondateur qui l'a accepte
		$id_groupe = intval($_GET['id_groupe']);
		$user_id = intval($_GET['user_id']);
		$sql = 'SELECT g.user_id
				FROM '. TABLE_GROUPES .' AS g
				WHERE group_id='.$id_groupe.' AND user_id='.$user['user_id'].' LIMIT 1';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==1 || $user['level'] ==10){
			$sql = 'UPDATE '. TABLE_GROUPES_INDEX .' SET accepte=1
				WHERE group_id='.$id_groupe.' AND user_id='.$user_id.' LIMIT 1';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);		
		}
		header('location: '.formate_url('action=groupe&groupe='.$id_groupe,true));
		break;
	case 'refuser':
		// Suppression du user de la liste d'attente du groupe si c'est le leader ou un fondateur qui l'a demande
		$id_groupe = intval($_GET['id_groupe']);
		$user_id = intval($_GET['user_id']);
		$sql = 'SELECT g.user_id
				FROM '. TABLE_GROUPES .' AS g
				WHERE group_id='.$id_groupe.' AND user_id='.$user['user_id'].' LIMIT 1';
		if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
		if ($c->sql_numrows($resultat)==1 || $user['level'] ==10){
			$sql = 'DELETE FROM '. TABLE_GROUPES_INDEX .' 
				WHERE group_id='.$id_groupe.' AND user_id='.$user_id.' LIMIT 1';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);		
		}
		header('location: '.formate_url('action=groupe&groupe='.$id_groupe,true));
		break;
	default : 
		// AFFICHAGE des membres en ordre alphabetique

}

// Champs de recherche
if ($action != 'groupe' && $droits->check($module,0,'rechercher')){

	$tpl->assign_block_vars('recherche', array());
	// Liste des lettres cliquables
	$liste_lettres = array($lang['L_TOUS'],'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','123','%');
	foreach ($liste_lettres as $key)
	{
		$tpl->assign_block_vars('recherche.lettre', array(
			'LETTRE'	=> $key,
			'LIEN'		=> formate_url('action=search&chercher='.$key,true)
		));
	}
}


$tpl->set_filenames(array(
	'membres' => $root.'plugins/modules/membres/html/liste_membres.html'
));

// transformation en chaine
$chps_o = implode(',',$chps_o);
if(!preg_match('/user_id/',$chps_o)) $chps_o .= ',u.user_id';
if(!preg_match('/rang/',$chps_o)) $chps_o .= ',rang';
if(!preg_match('/msg/',$chps_o)) $chps_o .= ',msg';

$sql = 'SELECT '.$chps_o.'
		FROM '. TABLE_USERS .' AS u 
		'.$jointure.'
		WHERE u.user_id > 1 AND actif=1 
		'.$where.'
		ORDER BY '.$order.' LIMIT '.$start.','.$cf->config['membres_nbre_fpp'];
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
if ($c->sql_numrows($resultat)==0){
	$tpl->assign_block_vars('aucun_resultat', array());
}else{
	$i=0;
	$chps_o = explode(',',$chps_o);
	while($row = $c->sql_fetchrow($resultat))
	{
		// referencement
		if ($tpl->meta_description != '') $tpl->meta_description .= ', ';
		$tpl->meta_description .= $row['pseudo'];
		
		if ($i%$cf->config['membres_nbre_cols']==0) $tpl->assign_block_vars('lignes', array());
		$tpl->assign_block_vars('lignes.cellule', array(
			'AVATAR'	=> $row['avatar'],
			'RANG'		=> formate_rang($row['rang'],$row['msg']),
			'PROFILE'	=> formate_url('index.php?module=profil&user_id='.$row['user_id']),
			'PSEUDO'	=> $row['pseudo']
		));
		$i++;
		$fiche = array();
		foreach ($chps_o as $key=>$val)
		{
			if (!in_array($val,array('pseudo','avatar','u.user_id','rang')))
			{
				// valeur du champ pour le user en cours de traitement
				$md->valeur_actuelle = $row[$val];
				// formatage de l'affichage en fonction du champs
				$rep = $md->formate_affichage($md->liste_champs[$val]['nom_champs'],$md->liste_champs[$val]['type_saisie'],$md->liste_champs[$val]['param']);
				// Certains champs beneficient d'un affichage spécifique
				$rep = formate_info_user($val,$rep);
				// affichage
				if (!empty($rep)){
					$tpl->assign_block_vars('lignes.cellule.infos', array(
						'LANG'	=> $lang[$md->liste_champs[$val]['lang']],
						'INFO'	=> $rep
					));
				}
			}
		}
	}
}

// PAGINATION
include($root.'fonctions/fct_affichage.php');

$sql = 'SELECT count(u.user_id) as max FROM '. TABLE_USERS.' AS u '.$jointure.' WHERE actif=1 '.$where; 
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,49,__FILE__,__LINE__,$sql);
$row = $c->sql_fetchrow($resultat);


$tpl->assign_vars(array(
	'I_EMAIL'				=> $img['mail'],
	'I_MP'					=> $img['mp'],
	'I_OK'					=> $img['valide'],
	'I_NOK'					=> $img['invalide'],
	'LARGEUR_CELLULES'		=> round(100/$cf->config['membres_nbre_cols']),
	'PAGINATION'			=> create_pagination($start, $pagination.'start=', $row['max'], $cf->config['membres_nbre_fpp'], $lang['L_MEMBRES'])
));
?>
