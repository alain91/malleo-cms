<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Arcade Flash pour Malleo (CMS)
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Not Licenced / Author Copy
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|------------------------------------------------------------------------------------------------------------
*/
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
// Listing des tables
global $prefixe,$lang,$module,$cache, $c, $cf, $user,$users, $droits, $style_path, $style_name, $startime, $liste_plugins;
define('TABLE_ARCADE_CATS',		$prefixe.'mod_arcade_cats');
define('TABLE_ARCADE_CATS_JEUX',$prefixe.'mod_arcade_cats_jeux');
define('TABLE_ARCADE_FAVORIS',	$prefixe.'mod_arcade_favoris');
define('TABLE_ARCADE_JEUX',		$prefixe.'mod_arcade_jeux');
define('TABLE_ARCADE_MODULES',	$prefixe.'mod_arcade_modules');
define('TABLE_ARCADE_SCORES',	$prefixe.'mod_arcade_scores');
define('TABLE_ARCADE_SESSIONS',	$prefixe.'mod_arcade_sessions');
define('TABLE_ARCADE_TRICHES',	$prefixe.'mod_arcade_triches');


// Chargement des fichiers de langue si il y'en a
load_lang_mod('arcade');

// Chargement des images de ce module si il y'en a
load_images_mod('arcade');

// Chargement des outils
require_once($root.'plugins/modules/arcade/class_arcade.php');
require_once($root.'plugins/modules/arcade/class_arcade_admin.php');
require_once($root.'plugins/modules/arcade/class_arcade_submit.php');
?>