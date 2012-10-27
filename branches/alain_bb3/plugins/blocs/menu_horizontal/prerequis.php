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
// Constante de table
global $prefixe,$user,$root;
define('TABLE_MENUH',	$prefixe.'bloc_menuh');

// Chemin du fichier de cache utilisé
define('PATH_CACHE_TPL_ADMIN_MENU',	$root.'cache/bloc_menu_horizontal/menu_horizontal_admin.html');
define('PATH_CACHE_TPL_MENU',	$root.'cache/bloc_menu_horizontal/menu_horizontal_standard.html');

// listing des pages d'administration mis en cache
$cache->files_cache['listing_pages_admin'] = array($root.'cache/data/liste_pages_admin','return listes_pages_admin();',$cf->config['cache_menuh_pages_admin']);

// Fichier de langue du bloc
load_lang_bloc('menu_horizontal');
// Ensemble des fonctions utilisées par ce bloc
include_once($root.'plugins/blocs/menu_horizontal/fct_menuh.php');
?>