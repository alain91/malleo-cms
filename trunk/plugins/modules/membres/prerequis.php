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
load_lang_mod('membres');

// Chargement des images de ce module si il y'en a
load_images_mod('membres');

// Champs à afficher de manière obligatoire
$chps_o = array('pseudo','avatar');

if (intval($cf->config['membres_nbre_fpp'])<4)$cf->config['membres_nbre_fpp'] = 4;
if (intval($cf->config['membres_nbre_cols'])<1 || defined('IPHONE'))$cf->config['membres_nbre_cols'] = 1;
?>