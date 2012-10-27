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

require($root.'plugins/modules/profil/prerequis.php');
$tpl->set_filenames(array('body_admin' => $root.'plugins/modules/profil/html/admin_categories.html'));
$hidden_action = 'ajouter';


// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'];
	// controles
	if (($action == 'ajouter' || $action == 'editer') && $_POST['titre_cat']==''){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre_cat'])?stripslashes($_POST['titre_cat']):'',
				'MODELE'=>isset($_POST['modele'])?stripslashes($_POST['modele']):'',
				));
		if ($action == 'ajouter') $action = '';
		if ($action == 'editer') $action = 'edit';
		$_GET = $_POST;
	}
	
	switch ($action)
	{
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_PROFIL_MODELES, 'id_cat', 'ordre', 'ASC', intval($_GET['id_cat']), $sens);
			header('location: '.$base_formate_url);
			break;
		case 'ajouter':
			$titre_cat = protection_chaine($_POST['titre_cat']);
			$modele = protection_chaine($_POST['modele']);
			$sql = 'INSERT INTO '.TABLE_PROFIL_MODELES.' (titre_cat,modele) VALUES (\''.str_replace("\'","''",$titre_cat).'\',\''.str_replace("\'","''",$modele).'\')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1102,__FILE__,__LINE__,$sql); 
			header('location: '.$base_formate_url);
			break;
		case 'editer':
			$titre_cat = protection_chaine($_POST['titre_cat']);
			$modele = protection_chaine($_POST['modele']);
			$id_cat = intval($_POST['id_cat']);
			$sql = 'UPDATE '.TABLE_PROFIL_MODELES.' SET 
					titre_cat=\''.str_replace("\'","''",$titre_cat).'\',
					modele=\''.str_replace("\'","''",$modele).'\' 
					WHERE id_cat='.$id_cat;
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1104,__FILE__,__LINE__,$sql); 
			header('location: '.$base_formate_url);
			break;
		case 'supprimer':	
			$id_cat = intval($_GET['id_cat']);
			$sql = 'DELETE FROM '.TABLE_PROFIL_MODELES.' WHERE id_cat='.$id_cat;
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1103,__FILE__,__LINE__,$sql);
			$sql = 'DELETE FROM '.TABLE_PROFIL_USERS.' WHERE id_cat='.$id_cat;
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1103,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;
		case 'edit':
			$id_cat = intval($_GET['id_cat']);
			$sql = 'SELECT titre_cat, modele FROM '.TABLE_PROFIL_MODELES.' WHERE id_cat = '.$id_cat;
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1104,__FILE__,__LINE__,$sql); 
			$row = $c->sql_fetchrow($resultat);
			$tpl->assign_vars(array(
				'HIDDEN'				=> '<input type="hidden" name="id_cat" value="'.$id_cat.'" />',
				'TITRE_CAT'				=> $row['titre_cat'],
				'MODELE'				=> $row['modele']
			));
			$hidden_action = 'editer';
	}
}

//
// AFFICHAGE des Categories

$sql = 'SELECT m.id_cat,m.titre_cat,m.modele 
		FROM '.TABLE_PROFIL_MODELES.' as m 
		ORDER BY m.ordre ASC';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql); 
$nbre_cats = $c->sql_numrows($resultat);
if ( $nbre_cats == 0 )
{
	$tpl->assign_block_vars('no_cat', array());
}else{
	$t=1;
	while($row = $c->sql_fetchrow($resultat))
	{
		$tpl->assign_block_vars('categories_profil', array(
			'CATEGORIE'		=> $row['titre_cat'],
			'MODELE'		=> $post->bbcode2html($row['modele']),
			'S_SUPP'		=> formate_url('action=supprimer&id_cat='.$row['id_cat'],true),
			'S_EDIT'		=> formate_url('action=edit&id_cat='.$row['id_cat'],true),
			'S_UP'			=> formate_url('action=move&sens=up&id_cat='.$row['id_cat'],true),
			'S_DOWN'		=> formate_url('action=move&sens=down&id_cat='.$row['id_cat'],true),
		));
		
		// Monter / descendre
		if ($nbre_cats>1 && $t>1) $tpl->assign_block_vars('categories_profil.monter',array());
		if ($nbre_cats>1 && $t<$nbre_cats) $tpl->assign_block_vars('categories_profil.descendre',array());
		$t++;
	}
}

// On charge le wysiwyg
if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');

		
$tpl->assign_vars(array(
	'I_DOWN'					=> $img['down'],
	'I_UP'						=> $img['up'],
	'I_EDITER'					=> $img['editer'],
	'I_EFFACER'					=> $img['effacer'],
	'HIDDEN_ACTION'				=> $hidden_action
));

?>