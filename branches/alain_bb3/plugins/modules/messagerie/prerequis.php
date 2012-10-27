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
// Listing des tables
global $prefixe,$lang,$module,$cache, $c, $cf, $user,$users, $droits, $style_path, $style_name, $startime, $liste_plugins;
define('TABLE_MESSAGERIE',			$prefixe.'mod_messagerie');
define('TABLE_MESSAGERIE_ETAT',		$prefixe.'mod_messagerie_etat');
define('TABLE_MESSAGERIE_CONTACTS',	$prefixe.'mod_messagerie_contacts');

// Chargement des fichiers de langue si il y'en a
load_lang_mod('messagerie');

// init
require_once($root.'plugins/modules/messagerie/class_messagerie.php');
$mp = new messagerie();

// Chargement des images de ce module si il y'en a
load_images_mod('messagerie');
?>