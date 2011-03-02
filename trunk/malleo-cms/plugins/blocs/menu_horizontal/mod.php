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
if ( !defined('PROTECT'))
{
	die("Tentative de Hacking");
}
global $cache;
include_once($root.'plugins/blocs/menu_horizontal/prerequis.php');

//
// APPEL du menu pour les admins

if(defined('PROTECT_ADMIN'))
{
	// Appel du cache du menu ou création si besoin
	$cache->cache_tpl(PATH_CACHE_TPL_ADMIN_MENU, 'return monter_menu_admin();', $cf->config['cache_menuh_html']);
	$tpl->set_filenames(array(
		'mod_menuh' => PATH_CACHE_TPL_ADMIN_MENU
	));
	$tpl->assign_var_from_handle('menu_horizontal', 'mod_menuh');
	
	
}elseif (defined('PROTECT'))
{
	// 
	// APPEL du menu standard pour les users
	$cache->cache_tpl(PATH_CACHE_TPL_MENU, 'return monter_menu();', $cf->config['cache_menuh_html']);
	
	$tpl->set_filenames(array(
		  'mod_menuh' =>PATH_CACHE_TPL_MENU
	));
	$tpl->assign_var_from_handle('menu_horizontal', 'mod_menuh');
}
?>