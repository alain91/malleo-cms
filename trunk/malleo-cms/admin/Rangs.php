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
load_lang('rangs');
$tpl->set_filenames(array('body_admin' => $root.'html/admin_gestion_rangs.html'));
$hidden_action = 'ajouter';



// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	
	// controles
	if (($action == 'ajouter' || $action == 'editer') && 
		($_POST['titre']=='' || (!isset($_POST['special']) && $_POST['msg']==''))){
		erreur_saisie('erreur_saisie',$lang['L_TOUT_REMPLIR'],array(
				'TITRE'=>isset($_POST['titre'])?stripslashes($_POST['titre']):'',
				'IMAGE'=>isset($_POST['image'])?stripslashes($_POST['image']):'',
				'MSG'=>isset($_POST['msg'])?stripslashes($_POST['msg']):''));
		if ($action == 'ajouter') $action = '';
		if ($action == 'editer') $action = 'edit';
		$_GET = $_POST;
	}	
	
	switch ($action)
	{
		case 'ajouter':
				$msg = (isset($_POST['special']))?'NULL':intval($_POST['msg']);
				$titre = protection_chaine($_POST['titre']);
				$sql = 'INSERT INTO '.TABLE_RANGS.' (titre, image, msg) 
						VALUES (\''.str_replace("\'","''",$titre).'\',
								\''.str_replace("\'","''",$_POST['image']).'\',
								'.$msg.')';
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1104,__FILE__,__LINE__,$sql);
				$cache->appel_cache('listing_rangs',true);
				header('location: '.formate_url('',true));
				break;
		case 'supprimer':
				$sql = 'DELETE FROM '.TABLE_RANGS.' WHERE id_rang='.intval($_GET['id_rang']);
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1104,__FILE__,__LINE__,$sql); 
				$cache->appel_cache('listing_rangs',true);
				header('location: '.formate_url('',true));
				break;
		case 'editer':	
				$msg = (isset($_POST['special']))?'NULL':intval($_POST['msg']);
				$titre = protection_chaine($_POST['titre']);
				$sql = 'UPDATE '.TABLE_RANGS.' SET
						titre=\''.str_replace("\'","''",$titre).'\',
						image=\''.str_replace("\'","''",$_POST['image']).'\',
						msg='.$msg.' 
						WHERE id_rang='.intval($_POST['id_rang']);
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1104,__FILE__,__LINE__,$sql); 
				$cache->appel_cache('listing_rangs',true);
				header('location: '.formate_url('',true));
				break;
		case 'edit':
				$sql = 'SELECT titre, image, msg  FROM '.TABLE_RANGS.' WHERE id_rang='.intval($_GET['id_rang']);
				if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,1104,__FILE__,__LINE__,$sql); 
				$row = $c->sql_fetchrow($resultat);
				$tpl->assign_vars(array(
					'HIDDEN'	=> '<input type="hidden" name="id_rang" value="'.intval($_GET['id_rang']).'" />',
					'IMAGE'		=> $row['image'],
					'TITRE'		=> $row['titre'],
					'SPECIAL'	=> ($row['msg']==NULL)?' checked="checked"':'',
					'MSG'		=> $row['msg']
				));
				$hidden_action='editer';
	}
}


//
// AFFICHAGE

$sql = 'SELECT id_rang, titre, image, msg 
		FROM '.TABLE_RANGS.' 
		ORDER BY msg ASC, titre ASC';
if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1101,__FILE__,__LINE__,$sql); 
if ($c->sql_numrows($resultat)==0){
	$tpl->assign_block_vars('no_liste_rangs', array());
}else{
	while($row = $c->sql_fetchrow($resultat))
	{
		$tpl->assign_block_vars('liste_rangs', array(
			'TITRE'		=> $row['titre'],
			'MSG'		=> ($row['msg']==null)?$lang['L_RANG_SPECIAL']:$row['msg'],
			'SUPPRIMER'	=> formate_url('action=supprimer&id_rang='.$row['id_rang'],true),
			'EDITER'	=> formate_url('action=edit&id_rang='.$row['id_rang'],true),
			'IMAGE'		=> ($row['image']!='')?'<img src="'.$row['image'].'" alt="'.$row['titre'].'" />':'',
		));
	}
}

$tpl->assign_vars(array(
	'HIDDEN_ACTION'			=> $hidden_action,
	'I_EDIT'				=> $img['editer'],
	'I_SUPPR'				=> $img['effacer'],

));


?>