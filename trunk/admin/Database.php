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
load_lang('database');

// TRAITEMENT
if (isset($_POST['action']) || isset($_GET['action']))
{
	$action = (isset($_POST['action']))? $_POST['action']:$_GET['action'] ;
	switch ($action)
	{
		case 'optimiser':
			$sql = 'OPTIMIZE TABLE `'.protection_chaine($_GET['table']).'`';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;		
		case 'reparer':
			$sql = 'REPAIR TABLE `'.protection_chaine($_GET['table']).'`';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;
		case 'collation':
		 	$sql = 'ALTER TABLE `'.protection_chaine($_GET['table']).'`  DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;
		case 'type':
		 	$sql = ' ALTER TABLE `'.protection_chaine($_GET['table']).'`  ENGINE = MyISAM';
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
			header('location: '.$base_formate_url);
			break;
	}
}


$tpl->set_filenames(array('body_admin' => $root.'html/admin_database.html'));

// Entréé: Octets
// Sortie : Ko, Mo, Go
function formate_taille_fichier($size){
	global $lang;
	if ($size < 1024){
		return sprintf($lang['L_OCTETS'],$size );
	}elseif($size < 1048576){
		return sprintf($lang['L_KOCTETS'],round(($size/1024),2));
	}elseif($size < 1073741824){
		return sprintf($lang['L_MOCTETS'],round(($size/1024/1024),2));
	}else{
		return sprintf($lang['L_GOCTETS'],round(($size/1024/1024/1024),2));
	}
}
// Taille Base
$sql = 'SHOW TABLE STATUS';
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
$taille_totale_base = $nbre_tables = 0;
while($row = $c->sql_fetchrow($resultat))
{
	$tpl->assign_block_vars('liste_tables', array(
		'NOM_TABLE'			=> $row['Name'],
		'ENREGISTREMENTS'	=> $row['Rows'],
		'COLLATION'			=> $row['Collation'],
		'TYPE'				=> $row['Engine'],
		'TAILLE_DONNEES'	=> formate_taille_fichier($row['Data_length']),
		'TAILLE_INDEX'		=> formate_taille_fichier($row['Index_length']),
		'U_OPTIMISER'		=> formate_url('action=optimiser&table='.$row['Name'],true),
		'U_REPARER'			=> formate_url('action=reparer&table='.$row['Name'],true),
		'U_COLLATION'		=> formate_url('action=collation&table='.$row['Name'],true),
		'U_TYPE'			=> formate_url('action=type&table='.$row['Name'],true),
	));
	// Bloc libres donc on peut optimiser
	if ($row['Data_free']>0){
		$tpl->assign_block_vars('liste_tables.optimiser', array());
	}
	// Collation différente de la collation par défaut donc on propose de changer
	if ($row['Collation']!='utf8_unicode_ci'){
		$tpl->assign_block_vars('liste_tables.collation', array());
	}	
	// moteur différent du moteur par défaut donc on propose de changer
	if ($row['Engine']!='MyISAM'){
		$tpl->assign_block_vars('liste_tables.type', array());
	}
	$taille_totale_base += $row['Data_length']+$row['Index_length'];
	$nbre_tables++;
}

$tpl->assign_vars(array(
	'NBRE_TABLES'				=>	sprintf($lang['L_NBRE_TABLES'],$nbre_tables),
	'TAILLE_TOTALE_BASE'		=>	formate_taille_fichier($taille_totale_base)
));

?>