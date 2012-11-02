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
require($root.'plugins/blocs/liens/prerequis.php');

// --------------------------------------------------------------------------------------------
// TRAITEMENT des données
//
$editer_titre = ''; 
$editer_url = ''; 
$editer_image = ''; 
$hidden = '<input type="hidden" name="action" value="ajouter" />';
$apercu_image = '';
$titre_saisie = $lang['L_AJOUTER_LIEN'];

if (isset($_POST['action']) || isset($_GET['action']))
{
	if (isset($_POST['action'])) 
	{
		$action	= $_POST['action']; 
	}else{
		$action	= $_GET['action']; 
		$id_lien= intval($_GET['id_lien']);
	}
	
	// controles
	if (($action == 'ajouter' || $action == 'edit') && 
		($_POST['titre']=='' || $_POST['url']=='')){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):'',
				'LIEN'=>isset($_POST['url'])?stripslashes($_POST['url']):'',
				'IMAGE'=>isset($_POST['image'])?stripslashes($_POST['image']):''));
		if ($action == 'ajouter') $action = '';
		if ($action == 'edit') $action = 'editer';
		if (isset($_POST['id_lien'])) $id_lien= intval($_POST['id_lien']);
		$_GET = $_POST;
	}

	switch($action)
	{
		case 'ajouter':
			// on définit la position maximale
			$sql =  'SELECT MAX(ordre) as max FROM '.TABLE_LIENS;
			if(!($result=$c->sql_query($sql))) message_die(E_ERROR,1010,__FILE__,__LINE__,$sql);
			$row = $c->sql_fetchrow($result);
			$max = $row['max'];
			
			
			// On ajoute un lien dans notre base
			$titre	= protection_chaine($_POST['titre']); 
			$url	= protection_chaine($_POST['url']);
			$image	= protection_chaine($_POST['image']); 
		
			$sql = 'INSERT INTO '.TABLE_LIENS.' (titre,lien,ordre,vignette) 
				VALUES (\''.str_replace("\'","''",$titre).'\',\''.str_replace("\'","''",$url).'\','.($max+10).',\''.str_replace("\'","''",$image).'\')';
			if(!$c->sql_query($sql))  message_die(E_ERROR,1011,__FILE__,__LINE__,$sql);
			$cache->cache_tpl(CHEMIN_LIENS.FICHIER_LIENS, 'return creer_cache_mod_lien();', 0);
			header('location: '.$base_formate_url);
		break;
		
		case 'enregistrer':
			// Enregistrement des modifications effectuées sur la config générale	
			$cf->appel_config('MODIFIER',$_POST);
			$cache->cache_tpl(CHEMIN_LIENS.FICHIER_LIENS, 'return creer_cache_mod_lien();', 0);
			header('location: '.$base_formate_url);
			break;
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_LIENS, 'id_lien', 'ordre', 'ASC', intval($_GET['id_lien']), $sens);
			$cache->cache_tpl(CHEMIN_LIENS.FICHIER_LIENS, 'return creer_cache_mod_lien();', 0);
			header('location: '.$base_formate_url);
			break;			
		case 'supprimer':
			// supprimer le lien
			$sql = 'DELETE FROM '.TABLE_LIENS.' WHERE id_lien='.$id_lien;
			if(!$c->sql_query($sql)) message_die(E_ERROR,1012,__FILE__,__LINE__,$sql);
			$cache->cache_tpl(CHEMIN_LIENS.FICHIER_LIENS, 'return creer_cache_mod_lien();', 0);
			header('location: '.$base_formate_url);
			break;
		case 'editer':
			// préparer l'edition
			$sql = 'SELECT id_lien, titre, lien, vignette 
					FROM '. TABLE_LIENS .' 
					WHERE id_lien='.$id_lien;
			if (!($result = $c->sql_query($sql))) message_die(E_ERROR,1013,__FILE__,__LINE__,$sql);
			$row = $c->sql_fetchrow($result);
			$tpl->assign_vars(array(
				'TITRE'		=> $row['titre'],
				'LIEN'		=> $row['lien'],
				'IMAGE'		=> $row['vignette'],
			));
			$apercu_image = corriger_image_liens($row['vignette'],$row['titre']);
			$hidden = '<input type="hidden" name="id_lien" value="'.$id_lien.'" /><input type="hidden" name="action" value="edit" />';
			$titre_saisie = $lang['L_EDITER_LIEN'];
			break;
		case 'edit':
			$titre	= protection_chaine($_POST['titre']); 
			$url	= protection_chaine($_POST['url']);
			$image	= protection_chaine($_POST['image']); 
			$id_lien= intval($_POST['id_lien']);
			
			$sql = 'UPDATE '.TABLE_LIENS.' SET 
						titre=\''.str_replace("\'","''",$titre).'\',
						lien=\''.str_replace("\'","''",$url).'\',
						vignette=\''.str_replace("\'","''",$image).'\'
					WHERE id_lien='.$id_lien;
			if( !$c->sql_query($sql) ) message_die(E_ERROR,1014,__FILE__,__LINE__,$sql);
			
			$cache->cache_tpl(CHEMIN_LIENS.FICHIER_LIENS, 'return creer_cache_mod_lien();', 0);
			header('location: '.$base_formate_url);
			break;
	}
	$cf->config('LECTURE');
}

// --------------------------------------------------------------------------------------------
//    AFFICHAGE
//

$tpl->set_filenames(array(
	'body_admin' => $root.'plugins/blocs/liens/html/admin_liens.html'
));

// nbre de liens
$s_nbre_liens = ( $cf->config['liens_nbre_liens'] == '') ? '0' : $cf->config['liens_nbre_liens'];
// liens aléatoire ?
$s_aleatoire_yes = ( $cf->config['liens_aleatoire'] == 1) ? 'checked' : '';
$s_aleatoire_no = ( $cf->config['liens_aleatoire'] == 0) ? 'checked' : '';
// Afficher le scroll ?
$s_scroll_yes = ( $cf->config['liens_scroll'] == 1) ? 'checked' : '';
$s_scroll_no = ( $cf->config['liens_scroll'] == 0) ? 'checked' : '';


// listing des liens
$sql = 'SELECT id_lien, titre, lien, vignette 
	FROM '. TABLE_LIENS .' 
	ORDER BY ordre ASC';
if ( !($result = $c->sql_query($sql)) )  message_die(E_ERROR,1015,__FILE__,__LINE__,$sql);
$nbre_liens = $c->sql_numrows($result);
if ($nbre_liens==0){
	$tpl->assign_block_vars('no_liens', array());
}else{
	$t=1;
	while ($row = $c->sql_fetchrow($result))
	{
		$image = corriger_image_liens($row['vignette'],$row['titre']);
		$titre = $row['titre'];
		$lien = '<a href="'.$row['lien'].'" target="_blank">'.substr($row['lien'],0,25).'</a>';
		$tpl->assign_block_vars('liens', array(
			'IMAGE'		=> $image,
			'TITRE' 	=> $titre,
			'LIEN'		=> $lien,
			'DESCENDRE'	=> formate_url('action=move&sens=down&id_lien='.$row['id_lien'],true),
			'MONTER'	=> formate_url('action=move&sens=up&id_lien='.$row['id_lien'],true),
			'EDITER'	=> formate_url('action=editer&id_lien='.$row['id_lien'],true),
			'SUPPR'		=> formate_url('action=supprimer&id_lien='.$row['id_lien'],true)
		));
		
		// Monter / descendre
		if ($nbre_liens>1 && $t>1) $tpl->assign_block_vars('liens.monter',array());
		if ($nbre_liens>1 && $t<$nbre_liens) $tpl->assign_block_vars('liens.descendre',array());
		$t++;
	}
}

$tpl->assign_vars(array(	
	'APERCU_IMAGE'				=> $apercu_image,
	'HIDDEN'					=> $hidden,
	'NBRE_LIENS'				=> $s_nbre_liens,
	'ALEA_YES'					=> $s_aleatoire_yes,
	'ALEA_NO'					=> $s_aleatoire_no,
	'SCROLL_YES'				=> $s_scroll_yes,
	'SCROLL_NO'					=> $s_scroll_no,	
	'L_AJOUTER_LIEN'			=> $titre_saisie,
	'I_UP'						=> $root.$img['up'],
	'I_DOWN'					=> $root.$img['down'],
	'I_EDIT'					=> $root.$img['editer'],
	'I_SUPP'					=> $root.$img['effacer']
	
	));
?>