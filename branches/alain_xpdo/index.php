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
define('PROTECT',true);
$root = './';
require_once($root.'/chargement.php');
$lang=$erreur=array();
load_lang('defaut');

// récupération du module demandé dans l'URL
$module = (!isset($_GET['module']) || empty($_GET['module'])) ? $cf->config['default_module']:$_GET['module'];
$module =  preg_replace('/[^a-z0-9_-]/i','',$module);
$base_formate_url = 'index.php?module='.$module;

// Construction des navlinks
$session->make_navlinks($lang['L_ACCUEIL'],formate_url('index.php?module='.$cf->config['default_module']));

include($root.'class/class_assemblage.php');
$map = new Assemblage();
$map->module = $module;
$map->listing_modules = $cache->appel_cache('listing_modules');

// les module demande existe-t-il ?
if (!array_key_exists($module,$map->listing_modules))
{
	error404(2);
}

// Entetes
$tpl->assign_vars(array(
	'ROOT_STYLE'		=>	$root,
	'ROOT_STYLE_ONGLETS'=>	$style_path.$style_name.'/onglets.css',
	'NOM_SITE'			=>	$cf->config['nom_site'],
	'CHARSET'			=>	$cf->config['charset'],
	'STYLE'				=>	$style_name,
));
// si un style est impose on le force
$style_name=load_style($map->listing_modules[$module]['style']);

$tpl->set_filenames(array(
  'body' => $map->Cache_Template($module)
));


// Assemblage de la page 
preg_match_all("|\{(.*)\}|U",$map->Lire_Fichier_HTML(),$modules);
for ($ListeBlocs=0;$ListeBlocs<sizeof($modules[1]);$ListeBlocs++)
{
	if ($modules[1][$ListeBlocs] == 'module')
	{
		// Si c'est un module virtuel on le charge
		if ($map->listing_modules[$module]['virtuel'] != null &&
				file_exists($root.'plugins/modules/'.$map->listing_modules[$module]['virtuel'].'/mod.php'))
		{
			include_once($root.'plugins/modules/'.$map->listing_modules[$module]['virtuel'].'/mod.php');
			$tpl->assign_var_from_handle('module',$map->listing_modules[$module]['virtuel']);
		}elseif (file_exists($root.'plugins/modules/'.$module.'/mod.php'))
		{
			// MODULE principal
			include_once($root.'plugins/modules/'.$module.'/mod.php');
			$tpl->assign_var_from_handle('module',$module);
		}else{
			message_die(E_WARNING,1,__FILE__,__LINE__);
		}
	}else{
		if (preg_match('/HTML_/',$modules[1][$ListeBlocs]))
		{
			// BLOC HTML
			$id_bloc_html = intval(preg_replace('/HTML_/','',$modules[1][$ListeBlocs]));
			include($root.'plugins/blocs/html/mod.php');
			$tpl->assign_var_from_handle('HTML_'.$id_bloc_html,'HTML_'.$id_bloc_html);
		}elseif (file_exists($root.'plugins/blocs/'.$modules[1][$ListeBlocs].'/mod.php'))
		{
			// BLOC standard				
			
			// Bloc non installé ? on s'en occupe
			if (!array_key_exists($modules[1][$ListeBlocs],$liste_plugins)){
				include_once($root.'class/class_plugins.php');
				if (!isset($plugin)) $plugin = new plugins();
				$plugin->install_plugin($modules[1][$ListeBlocs],1);
				// On redémarre car on a purge le cache afin d'intégrer les spécifications du bloc
				header('location: '.formate_url('',true));
				exit;
			}
			
			include_once($root.'plugins/blocs/'.$modules[1][$ListeBlocs].'/mod.php');
			$tpl->assign_var_from_handle($modules[1][$ListeBlocs],$modules[1][$ListeBlocs]);
		}
	}
}
// Tracé des activités sur le site
$session->tracer_page();

include_once($root.'page_haut.php');
$tpl->pparse('body');
include_once($root.'page_bas.php');
$tpl->afficher_page();
?>
