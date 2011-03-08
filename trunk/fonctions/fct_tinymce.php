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
load_lang('tinymce');

// Chargement du fichier
include_once($root.'fonctions/fct_smileys.php');
$cache->cache_tpl($root.'cache/smileys/emotions.html', 'return creer_cache_emotions();', 43200);

$largeur_fenetre_smileys = 400;
$hauteur_fenetre_smileys = 400;

// METHODE de fonctionnement: BBcodes ou HTML
$WYSIWYG_METHODE = (isset($WYSIWYG_METHODE) && $WYSIWYG_METHODE=='html') ? 'html':'bbcodes';
$tpl->set_filenames(array('wysiwyg'=>$root.'html/tinymce_'.$WYSIWYG_METHODE.'.html'));

// Profile d'Affichage : simple ou avance
$WYSIWYG_PROFILE = (defined('IPHONE'))?'iphone':((isset($WYSIWYG_PROFILE) && $WYSIWYG_PROFILE=='simple') ? 'simple':'avance');
$tpl->assign_block_vars($WYSIWYG_PROFILE, array());

// Profile de Chargement : A l'affichage ou a la demande du user ?
$WYSIWYG_LOAD = (isset($WYSIWYG_LOAD) && $WYSIWYG_LOAD=='unload' || defined('IPHONE')) ? 'unload':'load';
$tpl->assign_block_vars($WYSIWYG_LOAD, array());

$tpl->assign_vars(array(
	'L_PHP'			=>	$lang['L_PHP'],
	'L_HTML'		=>	$lang['L_HTML'],
	'L_SQL'			=>	$lang['L_SQL'],
	'L_QUOTE'		=>	$lang['L_QUOTE'],
	'L_WIKI'		=>	$lang['L_WIKI'],
	'STYLE'			=>	$style_name,
	'PATH_SMILEYS'	=>	'cache/smileys/emotions.html',
	'LARGEUR_SMILEYS'	=>	$largeur_fenetre_smileys,
	'HAUTEUR_SMILEYS'	=>	$hauteur_fenetre_smileys,
	'ROOT'			=>	$root
));

$tpl->assign_var_from_handle('WYSIWYG','wysiwyg');
?>