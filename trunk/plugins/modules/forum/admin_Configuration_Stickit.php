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
require($root.'plugins/modules/forum/prerequis.php');
load_lang_mod('forum');
$hidden = 'enregistrer';

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	if (($action == 'enregistrer' || $action == 'editer') && (trim($_POST['mot'])=='')
			&& (trim($_POST['couleur'])=='' || trim($_POST['image'])=='')){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR']);
		if ($action == 'enregistrer') $action = '';
		if ($action == 'editer') $action = 'edit';
		$_GET = $_POST;
	}
	switch ($action)
	{
		case 'enregistrer':
			$image 		= (empty($_POST['image']))?'null':'\''.str_replace("\'","''",protection_chaine($_POST['image'])).'\'';
			$couleur 	= (empty($_POST['couleur']))?'null':'\''.str_replace("\'","''",protection_chaine($_POST['couleur'])).'\'';
			$alternatif = (empty($_POST['alternatif']))?'null':'\''.str_replace("\'","''",protection_chaine($_POST['alternatif'])).'\'';
			$sql = 'INSERT INTO '.TABLE_FORUM_TAG.' (mot, type, image, couleur, alternatif)
					VALUES (\''.str_replace("\'","''",protection_chaine(ereg_replace('\[|\]','',$_POST['mot']))).'\',
							'.intval($_POST['type']).',
							'.$image.',
							'.$couleur.',
							'.$alternatif.')';
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,707,__FILE__,__LINE__,$sql); 
			$cache->appel_cache('listing_tags',true);
			header('location: '.$base_formate_url);
			break;
		case 'supprimer':	
			$sql = 'DELETE FROM '.TABLE_FORUM_TAG.' WHERE id_stick='.intval($_GET['id_stick']);
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1301,__FILE__,__LINE__,$sql);
			$cache->appel_cache('listing_tags',true);
			header('location: '.$base_formate_url);	
			break;			
		case 'editer' :
			$image 		= (empty($_POST['image']))?'null':'\''.str_replace("\'","''",protection_chaine($_POST['image'])).'\'';
			$couleur 	= (empty($_POST['couleur']))?'null':'\''.str_replace("\'","''",protection_chaine($_POST['couleur'])).'\'';
			$alternatif = (empty($_POST['alternatif']))?'null':'\''.str_replace("\'","''",protection_chaine($_POST['alternatif'])).'\'';
			$sql = 'UPDATE '.TABLE_FORUM_TAG.' SET 
						mot=\''.str_replace("\'","''",protection_chaine(ereg_replace('\[|\]','',$_POST['mot']))).'\', 
						type='.intval($_POST['type']).', 
						image='.$image.', 
						couleur='.$couleur.', 
						alternatif='.$alternatif.'
					WHERE id_stick='.intval($_POST['id_stick']);	
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,707,__FILE__,__LINE__,$sql); 
			$cache->appel_cache('listing_tags',true);
			header('location: '.$base_formate_url);
			break;
		case 'edit':
			$sql = 'SELECT id_stick,mot,type,image,couleur,alternatif
					FROM '.TABLE_FORUM_TAG.' 
					WHERE id_stick='.intval($_GET['id_stick']);
			if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
			$row = $c->sql_fetchrow($resultat);
			$tpl->assign_vars(array(
				'ID_STICK'		=> '<input type="hidden" name="id_stick" value="'.$row['id_stick'].'" />',
				'MOT'			=> $row['mot'],
				'SELECT_TYPE_'.$row['type'] => ' selected="selected"',
				'IMAGE'			=> $row['image'],
				'COULEUR'		=> $row['couleur'],
				'ALTERNATIF'	=> $row['alternatif']
			));
			$hidden = 'editer';
	}
}

$tpl->set_filenames(array(
	  'body_admin' => $root.'plugins/modules/forum/html/admin_stickit.html'
));

//
// Listing des forums
$sql = 'SELECT id_stick,mot,type,image,couleur,alternatif
		FROM '.TABLE_FORUM_TAG.' 
		ORDER BY id_stick ASC';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,706,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat) == 0){
	$tpl->assign_block_vars('aucun_log', array(
		'AUCUN_LOG' => $lang['L_AUCUN_LOGS'],
	));
}else{
	while($row = $c->sql_fetchrow($resultat))
	{
		$tpl->assign_block_vars('liste_tags',array(
			'MOT'			=> $row['mot'],
			'TYPE'			=> ($row['type']==0)?$lang['L_COULEUR']:$lang['L_IMAGE'],
			'IMAGE'			=> ($row['image']!='')?'<img src="'.$row['image'].'" alt="" />':'',
			'COULEUR'		=> $row['couleur'],
			'ALTERNATIF'	=> $row['alternatif'],
			'ID_STICK' 		=> $row['id_stick'],
			'U_SUPPRIMER'	=> formate_url('action=supprimer&id_stick='.$row['id_stick'],true),
			'U_EDITER'		=> formate_url('action=edit&id_stick='.$row['id_stick'],true),
		));
	}
}
$tpl->assign_vars(array(
	'HIDDEN'				=> $hidden,
	
	'I_PICKER'				=> $img['picker'],	
	'I_EDITER'				=> $img['editer'],
	'I_SUPPR' 				=> $img['effacer'],
));
?>