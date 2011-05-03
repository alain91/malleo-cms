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

function listes_pages_admin()
{
	global $lang,$liste_pages,$liste_plugins;
	$liste_pages= array();
	
	// on liste les sections/pages administratives natives
	// Config Generale
	$liste_pages[$lang['L_GENERAL']][] = array('admin/Metas.php',$lang['L_ADMIN_CONFIG_METAS']);
	$liste_pages[$lang['L_GENERAL']][] = array('admin/Config.php',$lang['L_ADMIN_CONFIG_GENERALE']);
	$liste_pages[$lang['L_GENERAL']][] = array('admin/Emails.php',$lang['L_ADMIN_CONFIG_EMAILS']);
	$liste_pages[$lang['L_GENERAL']][] = array('admin/Reglement.php',$lang['L_ADMIN_REGLEMENT']);
	$liste_pages[$lang['L_GENERAL']][] = array('admin/Profil.php',$lang['L_MENU_MEMBRES_CHOIX_CHAMPS']);
	
	// Mise en Page
	$liste_pages[$lang['L_MISE_EN_PAGE']][] = array('admin/Modules.php',$lang['L_ADMIN_GESTION_MODULES']);
	$liste_pages[$lang['L_MISE_EN_PAGE']][] = array('admin/Modeles.php',$lang['L_ADMIN_GESTION_MODELES']);
	$liste_pages[$lang['L_MISE_EN_PAGE']][] = array('plugins/blocs/menu_horizontal/admin_Menu_horizontal.php',$lang['L_MENU_HORIZONTAL']);
	$liste_pages[$lang['L_MISE_EN_PAGE']][] = array('admin/Smileys.php',$lang['L_ADMIN_GESTION_SMILEYS']);

	// Utilisateurs
	$liste_pages[$lang['L_MENU_MEMBRES']][] = array('admin/Utilisateurs.php',$lang['L_ADMIN_LISTE_MEMBRES']);
	$liste_pages[$lang['L_MENU_MEMBRES']][] = array('admin/Rangs.php',$lang['L_ADMIN_GESTION_RANGS']);
	$liste_pages[$lang['L_MENU_MEMBRES']][] = array('admin/Bannis.php',$lang['L_ADMIN_GESTION_BANNIS']);
	$liste_pages[$lang['L_MENU_MEMBRES']][] = array('admin/Bots.php',$lang['L_MENU_ROBOTS']);
	$liste_pages[$lang['L_MENU_MEMBRES']][] = array('admin/Traceur.php',$lang['L_MENU_TRACEUR']);
	
	// Groupes
	$liste_pages[$lang['L_MENU_GROUPES']][] = array('admin/Groupes.php',$lang['L_ADMIN_GESTION_GROUPES']);
	$liste_pages[$lang['L_MENU_GROUPES']][] = array('admin/Permissions.php',$lang['L_ADMIN_PERMISSIONS']);

	// On ajoute à la suite les pages administratives des modules
	$folder = "plugins/modules/";
	$dossier = @opendir($folder);
	while ($Fichier = @readdir($dossier))
	{
		if ($Fichier != "." && $Fichier != ".." && is_dir($folder.$Fichier)
			&& array_key_exists($Fichier,$liste_plugins)
			&& file_exists($folder.$Fichier.'/_admin_menu.php')) {
				include_once($folder.$Fichier.'/_admin_menu.php');
		}
	}
	closedir($dossier);
	// On ajoute à la suite les pages administratives des blocs
	$folder = "plugins/blocs/";
	$dossier = @opendir($folder);
	while ($Fichier = @readdir($dossier))
	{
		if ($Fichier != "." && $Fichier != ".." && is_dir($folder.$Fichier)
			&& array_key_exists($Fichier,$liste_plugins)
			&& file_exists($folder.$Fichier.'/_admin_menu.php')) {
				include_once($folder.$Fichier.'/_admin_menu.php');
		}
	}
	closedir($dossier);
	
	// Blocs HTML/PHP
	$liste_pages[$lang['L_MENU_BLOCS']][] = array('plugins/blocs/html/admin_html.php',$lang['L_MENU_BLOCS_HTML']);
	

	// Avance
	$liste_pages[$lang['L_AVANCE']][] = array('admin/Cache.php',$lang['L_ADMIN_CONFIG_CACHE']);
	$liste_pages[$lang['L_AVANCE']][] = array('admin/Langues.php',$lang['L_ADMIN_UPDATE_LANG_FILES']);
	$liste_pages[$lang['L_AVANCE']][] = array('admin/Purge_Cache.php',$lang['L_ADMIN_PURGE_CACHE']);	
	$liste_pages[$lang['L_AVANCE']][] = array('admin/Serveur.php',$lang['L_ADMIN_INFOS_SERVEUR']);	
	$liste_pages[$lang['L_AVANCE']][] = array('admin/Database.php',$lang['L_ADMIN_INFOS_DATABASE']);	
	$liste_pages[$lang['L_AVANCE']][] = array('admin/Fichiers.php',$lang['L_ADMIN_INFOS_FICHIERS']);	
	return $liste_pages;
}

//
// Montage du menu d'Administration
// on part du principe où le menu d'admin n'aura jamais plus d'1 sous menu

function monter_menu_admin()
{
	global $root,$cache;
	// Récupération de l'ensemble des pages d'administration en cache
	$listing_pages_admin = $cache->appel_cache('listing_pages_admin');

	$menu = $cat = '';
	foreach($listing_pages_admin as $key=>$val)
	{
		if ($cat != $key) $menu .= '<li class="menuparent"><a href="#">'.$key.'</a>';
		$nbre = sizeof($listing_pages_admin[$key]);
		if ($nbre > 0){
			$menu .= '<ul>';
			for ($i=0; $i<$nbre; $i++){
				$menu .= '<li><a href="admin.php?module='.$listing_pages_admin[$key][$i][0].'">'.$listing_pages_admin[$key][$i][1].'</a></li>';
			}
			$menu .= '</ul>';
		}
		$menu .= '</li>';
	}
	return $menu;
}

//
// Montage du menu public

function monter_menu($parent=0,$liste = '',$tab='')
{
	global $c;
	// 1er tour : on récupère les liens dans la base
	if ($liste == '')
	{
		$sql = 'SELECT id_lien ,titre_lien ,lien ,switch ,module ,accesskey ,image ,id_parent 
		FROM '.TABLE_MENUH.'
		ORDER BY ordre ASC, id_lien ASC LIMIT 100';
		if (!$resultat = $c->sql_query($sql)) message_die('La table menuh ne répond pas','',__LINE__,__FILE__,$sql);
		while ($row = $c->sql_fetchrow($resultat))
		{
			$liste[$row['id_parent']][]= $row;
		}
	}
	$menu = '';
	foreach ($liste[$parent] as $key=>$val)
	{
		$sub = (array_key_exists($val['id_lien'],$liste))? 1:0;
		$menu .= ($val['switch'] != '')?"\r\n".'<!-- BEGIN '.$val['switch'].' -->'."\r\n":'';
		$menu .= $tab.'<li ';
		$menu .= ($sub == 1)? 'class="menuparent"':'';
		$menu .= '><a href="'.str_replace('&','&amp;',$val['lien']).'"';
		$menu .= ($val['accesskey'] != null)?' accesskey="'.$val['accesskey'].'"':'';
		$menu .= '>';
		$menu .= ($val['image'] != '')?'<img src="'.$val['image'].'" style="float:left;" alt="'.$val['titre_lien'].'" />&nbsp;':'';
		$menu .= $val['titre_lien'].'</a>';
		
		if ($sub == 1)
		{
			$tab .= " \t";
			$menu .= "\r\n".$tab.'<ul>';
			$menu .= "\r\n".$tab.monter_menu($val['id_lien'],$liste,$tab);
			$menu .= "\r\n".$tab.'</ul>';
		}
		$menu .= '</li>'."\r\n";
		$menu .= ($val['switch'] != '')?"\r\n".'<!-- END '.$val['switch'].' -->'."\r\n":'';
	}
	return $menu;
}

unset($liste_liens_menu);
$liste_liens_menu = '';

function liste_liens_menuh()
{
	global $c,$liste_liens_menu;
	if (!is_array($liste_liens_menu))
	{
		$sql = 'SELECT id_lien ,titre_lien ,lien ,switch ,module ,accesskey ,image ,id_parent 
		FROM '.TABLE_MENUH.'
		ORDER BY ordre ASC, id_lien ASC LIMIT 100';
		if (!$resultat = $c->sql_query($sql)) message_die('La table menuh ne répond pas','',__LINE__,__FILE__,$sql);
		while ($row = $c->sql_fetchrow($resultat))
		{
			$liste_liens_menu[$row['id_parent']][]= $row;
		}
	}
	return $liste_liens_menu;
}

function apercu_menu($parent=0,$liste = '',$tab='|___')
{
	global $tpl;
	if ($liste == ''){ $liste = liste_liens_menuh();}
	$t=1;
	foreach ($liste[$parent] as $key=>$val)
	{
		$sub = (array_key_exists($val['id_lien'],$liste))? 1:0;
		$tpl->assign_block_vars('ligne_menu', array(
			'TABULATION'	=> ($parent==0)?'':$tab,
			'LIEN'			=> (strlen($val['lien'])>35)?substr($val['lien'],0,35).'..':$val['lien'],
			'TITRE'			=> $val['titre_lien'],
			'ACCESSKEY'		=> $val['accesskey'],
			'IMAGE'			=> ($val['image'] != null)? '<img src="'.$val['image'].'" border="0" />':'',
			'S_UP'			=> formate_url('action=move&sens=up&id_lien='.$val['id_lien'],true),
			'S_DOWN'		=> formate_url('action=move&sens=down&id_lien='.$val['id_lien'],true),
			'S_SUPP'		=> formate_url('action=supprimer&id_lien='.$val['id_lien'],true),
			'S_EDIT'		=> formate_url('action=modifier&id_lien='.$val['id_lien'],true),
		));
		
		// Monter / descendre
		$nbre_liens = sizeof($liste[$parent]);
		if ($nbre_liens>1 && $t>1) $tpl->assign_block_vars('ligne_menu.monter',array());
		if ($nbre_liens>1 && $t<$nbre_liens) $tpl->assign_block_vars('ligne_menu.descendre',array());
		$t++;
		
		if ($sub == 1){
			apercu_menu($val['id_lien'],$liste,'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tab);
		}
	}
	return true;
}

function liste_deroulante_parents($select = '',$parent=0,$liste = '',$tab='|___')
{
	if ($liste == ''){ $liste = liste_liens_menuh();}
	$options = '';
	foreach ($liste[$parent] as $key=>$val)
	{
		$sub = (array_key_exists($val['id_lien'],$liste))? 1:0;
		$espace = ($parent==0)?'':$tab;
		$checked = ($select == $val['id_lien'])?' selected':'';
		$options .= '<option value="'.$val['id_lien'].'"'.$checked.'>'.$espace.' '.$val['titre_lien'].'</option>'."\n";
		if ($sub == 1){
			$options .= liste_deroulante_parents($select, $val['id_lien'],$liste,'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tab);
		}
	}
	return $options;
}
?>