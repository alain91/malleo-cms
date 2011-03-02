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
global $lang;
load_lang('bannis');
load_lang('time');

unset($sql,$titres);
$edit_jour=date('d');
$edit_mois=date('m');
$edit_annee=date('Y');
$hidden = '';
$action= 'ajouter';

$tpl->set_filenames(array(
	  'body_admin' => $root.'html/admin_gestion_bannis.html'
));
// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'ajouter':
			$type_ban = intval($_POST['type']);
			$pattern_ban = protection_chaine($_POST['valeur']);
			$debut_ban = time();
			$fin_ban = (isset($_POST['illimite'])) ? 0:mktime(0,0,0,intval($_POST['mois']),intval($_POST['jour']),intval($_POST['annee']));
			$raison_ban = ($_POST['raison']!='')? '"'.protection_chaine($_POST['raison']).'"' :'null';
			$sql = 'INSERT INTO '.TABLE_BANNIS.' (type_ban, pattern_ban, debut_ban, fin_ban, raison_ban) VALUES
				('.$type_ban.',"'.$pattern_ban.'",'.$debut_ban.','.$fin_ban.','.$raison_ban.')';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;
		case 'supprimer':
			$sql = 'DELETE FROM '.TABLE_BANNIS.' WHERE id_ban='.intval($_GET['id_ban']).' LIMIT 1';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;		
	}
	$cache->appel_cache('listing_bannis',true);
}

//
// types de bannis
$sql[] = 'SELECT id_ban, type_ban, pattern_ban, user_id, pseudo, debut_ban, fin_ban, raison_ban 
		  FROM '.TABLE_BANNIS.' AS b LEFT JOIN '.TABLE_USERS.' AS u ON (b.pattern_ban=u.pseudo)
		  WHERE type_ban=0 ORDER BY fin_ban DESC';
$sql[] = 'SELECT id_ban, type_ban, pattern_ban, debut_ban, fin_ban, raison_ban 
		  FROM '.TABLE_BANNIS.' WHERE type_ban=1 ORDER BY fin_ban DESC';
$sql[] = 'SELECT id_ban, type_ban, pattern_ban, debut_ban, fin_ban, raison_ban 
		  FROM '.TABLE_BANNIS.' WHERE type_ban=2  ORDER BY fin_ban DESC';

$titres = array($lang['L_PSEUDO'],$lang['L_EMAIL'],$lang['L_IP']);

for ($i=0;$i<sizeof($titres);$i++){
	// Parcours des types possibles
	$tpl->assign_block_vars('liste_types', array(
		'VALEUR_TYPE'		=> $i,
		'LIBELLE_TYPE'		=> $titres[$i]
	));
	if (!$resultat=$c->sql_query($sql[$i])) message_die(E_ERROR,17,__FILE__,__LINE__,$sql[$i]);
	if ($c->sql_numrows($resultat)==0){
		$tpl->assign_block_vars('liste_types.aucun_enregistrements', array());
	}else{
		// affichage des enregistrements
		while($row = $c->sql_fetchrow($resultat))
		{
			$tpl->assign_block_vars('liste_types.enregistrements', array(
				'PATTERN'		=> ($i==0 && $row['user_id']!='') ? formate_pseudo($row['user_id'],$row['pseudo']):$row['pattern_ban'],
				'DATE_DEBUT'	=> date('d/m/Y',$row['debut_ban']),
				'DATE_FIN'		=> ($row['fin_ban']==0)?$lang['L_DEFINITIF']:date('d/m/Y',$row['fin_ban']),
				'RAISON'		=> $row['raison_ban'],
				'S_SUPP'		=> formate_url('action=supprimer&id_ban='.$row['id_ban'],true),
			));
			if ($row['raison_ban']!=NULL)$tpl->assign_block_vars('liste_types.enregistrements.raison_ban', array());
		}
	}
}

include_once($root.'fonctions/fct_formulaires.php');

$tpl->assign_vars(array(
	'JOUR'						=> lister_chiffres(1,31,$edit_jour),
	'MOIS'						=> lister($lang['mois'],$edit_mois),
	'ANNEE'						=> lister_chiffres(date('Y'),2020,$edit_annee),
	'HIDDEN'					=> $hidden,
	'ACTION'					=> $action,
	'I_SUPP'					=> $img['effacer']
));

?>