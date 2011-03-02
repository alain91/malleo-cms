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
define('TABLE_PROFIL_USERS',	$prefixe.'mod_profil_users');
define('TABLE_PROFIL_MODELES',	$prefixe.'mod_profil_modeles');

// Chargement des fichiers de langue si il y'en a
load_lang_mod('profil');

// Chargement des images de ce module si il y'en a
load_images_mod('profil');

//
// Chargement outils
include_once($root.'fonctions/fct_profil.php');

include_once($root.'plugins/modules/profil/class_profil.php');
$profil = new profil();

//
// CHARGEMENT formatage texte
include_once($root.'class/class_posting.php');
$post = new posting();

//
// initialisation de variables
if (intval($cf->config['avatar_taille_max'])==0)$cf->config['avatar_taille_max'] = 100;
if (intval($cf->config['avatar_largeur_max'])==0)$cf->config['avatar_largeur_max'] = 150;
if (intval($cf->config['avatar_hauteur_max'])==0)$cf->config['avatar_hauteur_max'] = 150;
if (intval($cf->config['avatar_taille_rep'])==0)$cf->config['avatar_taille_rep'] = 5000;

$dir_avatars = $root.'data/avatars/';
$dir_langues = $root.'lang/';
$dir_styles = $root.'styles/';


$liste_fuseaux_horaires['-12']= $lang['FUSEAU_-12'];
$liste_fuseaux_horaires['-11']= $lang['FUSEAU_-11'];
$liste_fuseaux_horaires['-10']= $lang['FUSEAU_-10'];
$liste_fuseaux_horaires['-9']=	$lang['FUSEAU_-9'];
$liste_fuseaux_horaires['-8']=	$lang['FUSEAU_-8'];
$liste_fuseaux_horaires['-7']= 	$lang['FUSEAU_-7'];
$liste_fuseaux_horaires['-6']= 	$lang['FUSEAU_-6'];
$liste_fuseaux_horaires['-5']= 	$lang['FUSEAU_-5'];
$liste_fuseaux_horaires['-4']= 	$lang['FUSEAU_-4'];
$liste_fuseaux_horaires['-3']= 	$lang['FUSEAU_-3'];
$liste_fuseaux_horaires['-2']= 	$lang['FUSEAU_-2'];
$liste_fuseaux_horaires['-1']= 	$lang['FUSEAU_-1'];
$liste_fuseaux_horaires['0']= 	$lang['FUSEAU_0'];
$liste_fuseaux_horaires['1']= 	$lang['FUSEAU_1'];
$liste_fuseaux_horaires['2']= 	$lang['FUSEAU_2'];
$liste_fuseaux_horaires['3']= 	$lang['FUSEAU_3'];
$liste_fuseaux_horaires['4']= 	$lang['FUSEAU_4'];
$liste_fuseaux_horaires['5']= 	$lang['FUSEAU_5'];
$liste_fuseaux_horaires['6']= 	$lang['FUSEAU_6'];
$liste_fuseaux_horaires['7']= 	$lang['FUSEAU_7'];
$liste_fuseaux_horaires['8']= 	$lang['FUSEAU_8'];
$liste_fuseaux_horaires['9']= 	$lang['FUSEAU_9'];
$liste_fuseaux_horaires['10']= 	$lang['FUSEAU_10'];
$liste_fuseaux_horaires['11']= 	$lang['FUSEAU_11'];
$liste_fuseaux_horaires['12']= 	$lang['FUSEAU_12'];


?>