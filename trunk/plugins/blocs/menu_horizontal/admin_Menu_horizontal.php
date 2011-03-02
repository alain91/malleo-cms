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
include_once($root.'plugins/blocs/menu_horizontal/prerequis.php');

$hidden_action = 'ajouter';
$action = $select = $hidden = '';
// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	
	// controles
	if (($action == 'ajouter' || $action == 'modif') && 
		($_POST['titre']=='' || $_POST['lien']=='' || $_POST['id_parent']=='')){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):'',
				'LIEN'=>isset($_POST['lien'])?stripslashes($_POST['lien']):''));
		if ($action == 'ajouter') $action = '';
		if ($action == 'modif') $action = 'modifier';
		$_GET = $_POST;
	}	
	
	switch ($action)
	{
		case 'move':
			$sens  = ($_GET['sens']=='up')? '+':'-';
			require_once($root.'fonctions/fct_formulaires.php');
			deplacer_id_tableau(TABLE_MENUH, 'id_lien', 'ordre', 'ASC', intval($_GET['id_lien']), $sens);
			header('location: '.$base_formate_url);
			break;
		case 'ajouter':
			$sql = 'INSERT INTO '.TABLE_MENUH. ' 
			(titre_lien ,lien, switch, accesskey, image, id_parent ) 
					VALUES 
			(\''.str_replace("\'","''",protection_chaine($_POST['titre'])).'\',\''.$_POST['lien'].'\',\''.$_POST['switch'].'\',\''.$_POST['accesskey'].'\',\''.$_POST['image'].'\','.$_POST['id_parent'].')';
			if (!$resultat = $c->sql_query($sql)) message_die('Impossible d\'Ajouter des liens dans le menu','',__LINE__,__FILE__,$sql);
			header('location: '.$base_formate_url);
			break;
		case 'modifier':
			$sql = 'SELECT id_lien, titre_lien ,lien, switch, accesskey, image, id_parent 
					FROM '.TABLE_MENUH. ' WHERE id_lien='.intval($_GET['id_lien']).' LIMIT 1';
			if (!$resultat = $c->sql_query($sql)) message_die('Impossible de préparer les modifications du menu','',__LINE__,__FILE__,$sql);
			$row = $c->sql_fetchrow($resultat);
			$tpl->assign_vars(array(
				'TITRE'		=> $row['titre_lien'],
				'LIEN'		=> $row['lien'],
				'SWITCH'	=> $row['switch'],
				'ACCESSKEY'	=> $row['accesskey'],
				'IMAGE'		=> $row['image']
			));
			$select = $row['id_parent'];
			$hidden_action = 'modif';
			$hidden = '<input type="hidden" name="id_lien" value="'.$row['id_lien'].'" />';
			break;
		case 'modif':
			$sql = 'UPDATE '.TABLE_MENUH.' 
					SET 
					   titre_lien= \''.str_replace("\'","''",protection_chaine($_POST['titre'])).'\',
					   lien=\''.$_POST['lien'].'\', 
					   switch=\''.$_POST['switch'].'\', 
					   accesskey=\''.$_POST['accesskey'].'\', 
					   image=\''.$_POST['image'].'\', 
					   id_parent='.$_POST['id_parent'].'
					WHERE id_lien='.intval($_POST['id_lien']).' LIMIT 1';
			if (!$resultat = $c->sql_query($sql)) message_die('Impossible de modifier le lien dans le menu','',__LINE__,__FILE__,$sql);
			header('location: '.$base_formate_url);
			break;
		case 'supprimer':
			$sql = 'DELETE FROM '.TABLE_MENUH. ' WHERE id_lien='.intval($_GET['id_lien']);
			if (!$resultat = $c->sql_query($sql)) message_die('Impossible de Supprimer le lien dans le menu','',__LINE__,__FILE__,$sql);
			header('location: '.$base_formate_url);
			break;
	}
}

$tpl->set_filenames(array(
	  'body_admin' => $root.'plugins/blocs/menu_horizontal/html/admin.html'
));

// AFFICHAGE des liens
apercu_menu();

//APERCU
$cache->cache_tpl(PATH_CACHE_TPL_MENU, 'return monter_menu();', 0);
$tpl->set_filenames(array( 
	'apercu_mod_menuh' => PATH_CACHE_TPL_MENU
));
$tpl->assign_var_from_handle('apercu_menu_horizontal', 'apercu_mod_menuh');
	
$tpl->assign_vars(array(
	'L_AJOUTER_LIEN'	=> ($action == 'modifier')? $lang['L_MODIFIER_LIEN']:$lang['L_AJOUTER_LIEN'],

	'I_DOWN'			=> $img['down'],
	'I_UP'				=> $img['up'],
	'I_EFFACER'			=> $img['effacer'],
	'I_EDITER'			=> $img['editer'],
	
	'HIDDEN_ACTION'		=> $hidden_action,
	'HIDDEN'			=> $hidden,
	'PARENT'			=> liste_deroulante_parents($select)
));

?>