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
global $prefixe,$lang,$module,$cache, $c, $cf, $user,$users, $droits, $style_path, $style_name, $startime, $liste_plugins;
// Listing des tables
define('TABLE_WIKI',		$prefixe.'mod_wiki');
define('TABLE_WIKI_TEXTE',	$prefixe.'mod_wiki_texte');

require_once($root.'plugins/modules/wiki/class_wiki.php');
$wiki = new Wiki();

// Chargement des images de ce module si il y'en a
load_images_mod('wiki');

// Chargement des fichiers de langue si il y'en a
load_lang_mod('wiki');

// Tag par defaut a appeller
$cf->config['wiki_page_defaut'] = 'Accueil';
$cf->config['wiki_ligne_par_page'] = 20;
?>