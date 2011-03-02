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
define('TABLE_BLOG_BILLETS',	$prefixe.'mod_blog_billets');
define('TABLE_BLOG_CATS',		$prefixe.'mod_blog_cats');
define('TABLE_BLOG_COMS',		$prefixe.'mod_blog_coms');

// Chargement des fichiers de langue si il y'en a
load_lang_mod('blog');

// init
include_once($root.'plugins/modules/blog/class_blog.php');
$blog = new blog();

// Déclaration du cache
$cache->files_cache['listing_blog_cat'] = array($root.'cache/data/liste_blog_cat','global $blog; return $blog->lister_blog_cat();',$cf->config['cache_duree_blog_cat']);

// Chargement des images de ce module si il y'en a
load_images_mod('blog');

?>