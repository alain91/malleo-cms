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
//  INIT
// ----------------------------------------------------------------
global $lang;
load_lang('utilisateurs');
$cf->conf['users_par_page'] = 20;
$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_utilisateurs.html'
));
include($root.'fonctions/fct_profil.php');
include($root.'fonctions/fct_formulaires.php');
// Recuperation des champs configures dans la modelisation
include_once($root.'class/class_modelisation.php');
$md = new Modelisation();
$md->page = 'Utilisateurs'; // Nom du champs page dans la table de modélisation
// Nous ne sommes pas dans la configuration d'une liste de champs comma la config
// donc on deporte toutes les sorties de fonctions 
$md->deporter = true;
$liste_champs = $md->generer_saisie('DEPORTER'); // Lancement du generateur et Recuperation des champs configures sous la forme champs1, champs2, ...

//  ACTIONS
// ----------------------------------------------------------------
if (isset($_GET['action']) || isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
	if (isset($_POST['id']))
	{
		if (eregi('add_groupe_',$action)){
			$group_id = intval(ereg_replace('add_groupe_','',$action));
			$action = 'groupe';
		}
		$liste = ereg_replace("[^0-9,]",'',implode(',',$_POST['id']));
		if ($liste=='')$liste="''";
		$sql = '';
		switch($action)
		{
			case 'bannir': 
				if (sizeof($_POST['id'])>0){
					$sql = 'SELECT `pseudo` FROM '.TABLE_USERS.' 
									WHERE `user_id` IN ('.$liste.')';
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
					while ($row = $c->sql_fetchrow($resultat)){ 
						$droits->ban_pseudo($row['pseudo'],0);
					}
					$cache->appel_cache('listing_bannis',true);
				}
				break;			
			case 'retablir': 
				if (sizeof($_POST['id'])>0){
					$sql = 'DELETE b FROM '.TABLE_BANNIS.' AS b 
							LEFT JOIN '.TABLE_USERS.' AS u
								ON (b.pattern_ban=u.pseudo) 
							WHERE b.type_ban=0 AND u.user_id IN ('.$liste.')';
					if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
					$cache->appel_cache('listing_bannis',true);
				}
				break;
			case 'supprimer': $sql = 'DELETE FROM '.TABLE_USERS.' WHERE user_id IN ('.$liste.')';break;
			case 'activer':$sql = 'UPDATE '.TABLE_USERS.' SET level=2,actif=1 WHERE user_id IN ('.$liste.')';break;
			case 'desactiver':$sql = 'UPDATE '.TABLE_USERS.' SET level=1,actif=0 WHERE user_id IN ('.$liste.')';break;
			case 'theme':$sql = 'UPDATE '.TABLE_USERS.' SET style="'.$cf->config['default_style'].'" WHERE user_id IN ('.$liste.')';				break;
			case 'groupe':
				// On recherche les pseudos saisis qui sont deja membres
				$sql_groupe = 'SELECT `user_id` FROM '.TABLE_GROUPES_INDEX.' 
								WHERE `user_id` IN ('.$liste.') AND group_id='.$group_id;
				if (!$resultat = $c->sql_query($sql_groupe))message_die(E_ERROR,37,__FILE__,__LINE__,$sql_groupe); 
				$deja_membre = array();
				while ($row = $c->sql_fetchrow($resultat)){ $deja_membre[] = $row['user_id']; }
				// Comparaison
				$liste = array_diff_assoc($_POST['id'],$deja_membre);
				// Enregistrement
				foreach ($liste as $key=>$val){
					$sql_groupe = 'INSERT INTO '.TABLE_GROUPES_INDEX.' (group_id, user_id, accepte) 
							VALUES ('.$group_id.','.$val.',1)';
					if (!$resultat = $c->sql_query($sql_groupe))message_die(E_ERROR,42,__FILE__,__LINE__,$sql_groupe);
				}
				break;
			
		}
		if ($sql != '') if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,47,__FILE__,__LINE__,$sql); 
		affiche_message('body_admin','L_MODIFICATION_EFFECTUEE',formate_url('',true));
	}
}



//  CHERCHER
// ----------------------------------------------------------------
$chercher = $lang['L_DERNIERS'];
$param = '';
if (isset($_GET['chercher']) || isset($_POST['chercher']))
{
	$chercher = (isset($_POST['chercher']))?$_POST['chercher']:$_GET['chercher'];
	if (isset($_GET['param']) || isset($_POST['param']))
	{
		$param = (isset($_POST['param']))?$_POST['param']:$_GET['param'];
		if ($param =='')error404(60); 
	}
}

switch($chercher)
{
	// Partie d'un pseudo
	case 'pseudo': $sql = 'WHERE pseudo REGEXP "'.str_replace("\'","''",$param).'" ORDER BY pseudo ASC';break;
	// Partie d'un email
	case 'email': $sql = 'WHERE email REGEXP "'.str_replace("\'","''",$param).'"	ORDER BY pseudo ASC';break;
	// Partie d'un style
	case 'style': $sql = 'WHERE style REGEXP "'.str_replace("\'","''",$param).'"	ORDER BY pseudo ASC';break;
	//Commencant par un chiffre
	case '123':	$sql = 'WHERE pseudo REGEXP "^[0-9]" ORDER BY pseudo ASC';break;
	//non actives
	case 'non_actives':	$sql = 'WHERE actif=0 ORDER BY pseudo ASC';break;
	//bannis
	case 'bannis':	$sql = 'LEFT JOIN '.TABLE_BANNIS.' AS b ON (u.pseudo=b.pattern_ban) 
							WHERE type_ban=0 AND (fin_ban>'.time().' OR fin_ban=0 ) ORDER BY pseudo ASC';break;
	//Ayant + de X messages
	case 'plus_msgs': $sql = 'WHERE msg>"'.intval($param).'" ORDER BY pseudo ASC';break;
	//Ayant - de X messages
	case 'moins_msgs':$sql = 'WHERE msg<"'.intval($param).'"	ORDER BY pseudo ASC';break;
	//Ayant + de X points
	case 'plus_points':	$sql = 'WHERE points>"'.intval($param).'"	ORDER BY pseudo ASC';break;
	//Ayant - de X points
	case 'moins_points': $sql = 'WHERE points<"'.intval($param).'"	ORDER BY pseudo ASC';break;
	// Commencant ni par une lettre, ni par un chiffre
	case '%':$sql = 'WHERE pseudo REGEXP "^[^[:alnum:]]" ORDER BY pseudo ASC';break; 
	// Dernier inscrits
	case $lang['L_DERNIERS']: $sql = 'ORDER BY date_register DESC';break;
	//Commencant par la lettre specifiee
	default :$sql = 'WHERE pseudo REGEXP "^'.str_replace("\'","''",$_GET['chercher']).'" ORDER BY pseudo ASC';break;
}
$sql = 'SELECT u.user_id,pseudo,avatar,'.$liste_champs.' FROM '.TABLE_USERS.' AS u '.$sql;

// on divise la requete pour pouvoir recuperer le nombre d'enregistrements total
$sql2 = $sql;

$start = (isset($_GET['start'])&& $_GET['start']>0)? intval($_GET['start']):'0';
$sql .= ' LIMIT '.$start.','.$cf->conf['users_par_page'];


//  AFFICHAGE
// ----------------------------------------------------------------

if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
if ($c->sql_numrows($resultat) == 0)
{
	$tpl->assign_block_vars('AUCUN_RESULTAT', array());
}else{
	$tpl->assign_block_vars('reponses', array());
	// Titres de colonnes
	foreach ($md->liste_champs as $key=>$val)
	{
		$libelle = (array_key_exists($val['lang'],$lang))? $lang[$val['lang']]:$val;
		$tpl->assign_block_vars('reponses.CHAMPS', array('TITRE_CHAMPS' =>$libelle));
	}
	while($row = $c->sql_fetchrow($resultat))
	{	
		// Pseudos dans la colonne de gauche
		$pseudo = (strlen(html_to_str($row['pseudo']))>20)? str_to_html(substr(html_to_str($row['pseudo']),20)).'...': $row['pseudo'];
		$tpl->assign_block_vars('reponses.ligne', array(
			'ID'		=> $row['user_id'],
			'PSEUDO'	=> formate_pseudo($row['user_id'],$pseudo),
			'S_PROFIL'	=> formate_url('index.php?module=profile&user_id='.$row['user_id']),
			'S_EDITER'	=> formate_url('admin.php?module=admin/Utilisateurs_Profil.php&user_id='.$row['user_id']),
			'DISABLED'	=> ($row['user_id']==1)? ' disabled':''
		));
		foreach ($md->liste_champs as $key=>$val)
		{
			$md->valeur_actuelle = $row[$val['nom_champs']];
			$tpl->assign_block_vars('reponses.ligne.col', array(
				'CASE'	=> $md->formate_affichage($val['nom_champs'],$val['type_saisie'],$val['param'])
			));
		}
	}
}

// Liste des lettres cliquables
$liste_lettres = array($lang['L_DERNIERS'],'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','123','%');
foreach ($liste_lettres as $key)
{
	$tpl->assign_block_vars('lettre', array(
		'LETTRE'	=> $key,
		'LIEN'		=> formate_url('chercher='.$key,true)
	));
}

// PAGINATION
include($root.'fonctions/fct_affichage.php');

// Nbre de resultats total
if (!$resultat=$c->sql_query($sql2)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql2);
$nbre_resultats = $c->sql_numrows($resultat);


$sql = 'SELECT g.group_id, g.titre, g.description, g.icone, g.type, g.ordre, g.couleur, g.user_id, u.pseudo
		FROM '.TABLE_GROUPES.' AS g LEFT JOIN '.TABLE_USERS.' AS u 
		ON (g.user_id=u.user_id)
		WHERE type=1 AND group_id>3
		ORDER BY ordre ASC, titre ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,37,__FILE__,__LINE__,$sql); 
while($row = $c->sql_fetchrow($resultat))
{	
	$tpl->assign_block_vars('liste_groupes', array(
		'NOM_GROUPE'	=> sprintf($lang['L_AJOUTER_AU_GROUPE'],$row['titre']), 
		'ID_GROUPE'		=> $row['group_id']
	));
}

// Si la recherche comprenait un parametre optionnel, on le conserve dans l'URL pour la pagination
if ($param != '') $param = '&param='.$param;
$tpl->assign_vars(array(
	'L_CONFIRM_TRAITEMENT'		=> str_replace("'","\'",$lang['L_CONFIRM_TRAITEMENT']),
	'I_EDITER'					=> $img['editer'],
	'PAGINATION'				=> create_pagination($start, 'chercher='.$chercher.$param.'&start=', $nbre_resultats ,$cf->conf['users_par_page'],$lang['L_MEMBRE'])
));
?>