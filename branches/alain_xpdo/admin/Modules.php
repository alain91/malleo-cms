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
load_lang('modules');

//Rechargement des versions
$reload_module = (isset($_GET['reload_module']))?intval($_GET['reload_module']):0;

$tpl->set_filenames(array(
	  'body_admin' => $root.'html/admin_gestion_modules.html'
));

require_once($root.'class/class_modules.php');
$mod = new modules($root);

// init
$mode = 'module_ajout';
$hidden = '<input type="hidden" name="action" value="ajouter" />';
$SelectModele = $SelectStyle = '';

//
// TRAITEMENTS
if (isset($_GET['action']) || isset($_POST['action']))
{
	$action = (isset($_POST['action']))?$_POST['action']:$_GET['action'];
	$mod_saisie = (isset($_POST['module']))? preg_replace("/[^a-z0-9_-]/i",'',supprimer_accents($_POST['module'])):'';
	
	switch($action)
	{
		case 'update':
			require_once($root.'class/class_plugins.php');
			$plugin = new plugins($root);
			$plugin->update_plugin($_GET['plugin'],0);
			header('location: '.$base_formate_url);	exit;
			break;
		case 'ajouter':
			if ($mod_saisie!='')$mod->ajoute_module($mod_saisie,$_POST['modele'],$_POST['style']);
			header('location: '.$base_formate_url);	exit;
			break;
		case 'supprimer':
			$mod->supprime_module($_GET['id_module']);
			header('location: '.$base_formate_url);	exit;
			break;
		case 'virtuel_ajouter':	
			if ($mod_saisie!='')$mod->ajoute_module_virtuel($mod_saisie,$_POST['virtuel'],$_POST['style'],$_POST['modele']);
			header('location: '.$base_formate_url);	exit;
			break;
		case 'virtuel_editer':	
			$mod->update_module_virtuel($_POST['style'],$_POST['modele'],$_POST['id_module']);
			header('location: '.$base_formate_url);	exit;
			break;
		case 'virtuelajout':
			$id_module = intval($_GET['id_module']);
			$sql = 'SELECT module, modele, virtuel, style FROM '.TABLE_MODULES.' WHERE id_module='.$id_module;
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,33,__FILE__,__LINE__,$sql);
			$row = $c->sql_fetchrow($resultat);
			$SelectModele = $row['modele'];
			$SelectStyle = $row['style'];
			$tpl->assign_vars(array(
				'VIRTUEL' => $row['module']
			));
			$mode = 'virtuel_ajout';
			$hidden = '<input type="hidden" name="action" value="virtuel_ajouter" />
						<input type="hidden" name="virtuel" value="'.$row['module'].'" />';		
			break;
			
		case 'virtueledit':
			$id_module = intval($_GET['id_module']);
			$sql = 'SELECT id_module,module, modele, virtuel, style FROM '.TABLE_MODULES.' WHERE id_module='.$id_module;
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,33,__FILE__,__LINE__,$sql);
			$row = $c->sql_fetchrow($resultat);
			$SelectModele = $row['modele'];
			$SelectStyle = $row['style'];
			$tpl->assign_vars(array(
				'VIRTUEL'	=> $row['virtuel'],
				'MODULE'	=> $row['module']
			));
			$mode = 'virtuel_edition';
			$hidden = '<input type="hidden" name="action" value="virtuel_editer" />
						<input type="hidden" name="id_module" value="'.$row['id_module'].'" />';		
			break;
		case 'editer':
			$mod->update_module($_POST['id_module'],$_POST['style'],$_POST['modele']);
			header('location: '.$base_formate_url);	exit;
			break;
		case 'edit':
			$id_module = intval($_GET['id_module']);
			$sql = 'SELECT module, modele,virtuel, style FROM '.TABLE_MODULES.' WHERE id_module='.$id_module;
			if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,33,__FILE__,__LINE__,$sql);
			$row = $c->sql_fetchrow($resultat);
			$SelectModele = $row['modele'];
			$SelectStyle = $row['style'];
			$tpl->assign_vars(array(
				'MODULE' => $row['module']
			));
			if (isset($_GET['virtuel'])){
				$virtuel = 'input_virtuel';
			}
			$mode = 'module_edition';
			$hidden = '<input type="hidden" name="action" value="editer" />
						<input type="hidden" name="id_module" value="'.$id_module.'" />
						<input type="hidden" name="module" value="'.$row['module'].'" />';
			break;
	}
}

//
// MODULES installes
$sql = 'SELECT id_module,module,virtuel,modele,style,titre_modele, gabaris, map, p.version 
		FROM '.TABLE_MODULES.' as m 
		LEFT JOIN '.TABLE_PLUGINS.' as p
			ON (m.module=p.plugin) 
		LEFT JOIN '.TABLE_MODELES.' as e
			ON (m.modele=e.id_modele) 
		ORDER BY m.virtuel ASC, m.module ASC';
if (!$resultat=$c->sql_query($sql)) message_die(E_ERROR,17,__FILE__,__LINE__,$sql);
$liste_modules_installes = array();
$liste_mods = array();
while($row = $c->sql_fetchrow($resultat))
{
	$liste_modules_installes[] = $row['module'];
	$cat = ($row['virtuel']!=null )?$row['virtuel']:$row['module'];
	$liste_mods[$cat][] = $row;
}
foreach($liste_mods as $key=>$val){
	$file = $root.'plugins/modules/'.$key.'/infos.xml';
	$version_dir = $root.'data/versions/'.$key;
	if(!is_dir($version_dir)) mkdir($version_dir, 0777);
	if (file_exists($file)){
		$xml = simplexml_load_file($file);
		
		// Check version
		$version_officielle = '- ? -';
		if (!empty($xml->check_version) AND $reload_module == 1){
			if (($retour = fsockopen_file_get_contents($xml->check_version)) != false){
				file_put_contents($version_dir.'/version.txt', $retour);
			}
		}
		if(is_file($version_dir.'/version.txt')) $version_officielle = file_get_contents($version_dir.'/version.txt');
		
		$version_minimale = '1';
		if (isset($xml->version_min))
		{
			$version_minimale = $xml->version_min;
		}
		
		// version actuelle
		// affiche un lien UPDATE en cas de maj disponible
		$version_actuelle = ($val[0]['version'] < $xml->version)? '':$val[0]['version'];
		$tpl->assign_block_vars('liste_modules', array(
			'TITRE_MODULE'		=> $xml->libelle,
			'AUTEUR'			=> $xml->auteur,
			'SITE'				=> url_cliquable($xml->site_auteur),
			'VERSION_OFFICIELLE'=> $version_officielle,
			'VERSION_ACTUELLE'	=> $val[0]['version'],
			'U_LIEN_UPGRADE'	=> formate_url('action=update&plugin='.$key,true)
		));
		if($cf->config['version_cms'] < $version_minimale) $tpl->assign_block_vars('liste_modules.update_not_supported', array(
		'VERSION_MINIMALE'	=> $version_minimale));
		if (($val[0]['version'] < $xml->version) AND ($cf->config['version_cms'] >= $version_minimale))$tpl->assign_block_vars('liste_modules.update', array(
			'VERSION_DISPONIBLE'	=> $xml->version));
	}else{
		$tpl->assign_block_vars('liste_modules', array(
			'TITRE_MODULE'	=> $key
		));	
	}
	foreach($val as $k=>$v){
		$vedit = ($v['virtuel']!=null)?'virtueledit':'edit';
		$tpl->assign_block_vars('liste_modules.liste_modules_detail', array(
			'MODULE'		=> $v['module'],
			'S_MODULE'		=> formate_url('index.php?module='.$v['module']),
			'CSS'			=> ($v['virtuel']!=null)?'row2':'row1',
			'MODELE'		=> $v['titre_modele'],
			'STYLE'			=> ($v['style'] != null)?$v['style']:$lang['L_AUCUN_STYLE'],
			'S_DROITS'		=> formate_url('admin.php?module=admin/Permissions.php&generer&defaut&noeuds='.$v['module']),
			'S_EDIT'		=> formate_url('action='.$vedit.'&id_module='.$v['id_module'],true),
			'S_SUPP'		=> formate_url('action=supprimer&id_module='.$v['id_module'],true)
		));
		if ($v['virtuel']==null)
		{
			$tpl->assign_block_vars('liste_modules.liste_modules_detail.original', array(
				'S_DUPLIQUER'	=> formate_url('action=virtuelajout&id_module='.$v['id_module'],true)
			));
		}
	}
}

// tpl de bascule edition/ajout/virtuel/editionVirtuel
$tpl->assign_block_vars($mode, array());

// Fsockopen actif ?
$disable_functions = (ini_get("disable_functions")!="" AND ini_get("disable_functions")!=false) ? array_map('trim', preg_split( "/[\s,]+/", ini_get("disable_functions"))) : array();
if (!function_exists("fsockopen") OR in_array('fsockopen', $disable_functions))$tpl->assign_block_vars('fsockopen', array());

$tpl->assign_vars(array(
	'LISTE_STYLES'				=> $mod->lister_styles_dispos($SelectStyle),
	'LISTE_MODELES'			=> $mod->lister_modeles_dispos($SelectModele),
	'LISTE_MODULES'			=> $mod->lister_modules_dispos($liste_modules_installes),
	'I_EDIT'						=> $img['editer'],
	'I_SUPP'						=> $img['effacer'],
	'I_DUPLIQUER'				=> $img['dupliquer'],
	'I_CADENAS'					=> $img['cadenas'],
	'I_REFRESH'					=> $img['refresh'],
	'HIDDEN'						=> $hidden,
	'REALOAD_LINK'				=>	formate_url('admin.php?module=admin/Modules.php&reload_module=1')
));

?>
