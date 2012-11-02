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
define('TABLE_FORUM_CATS',			$prefixe.'mod_forum_cats');
define('TABLE_FORUM_FORUMS',		$prefixe.'mod_forum_forums');
define('TABLE_FORUM_POSTS',			$prefixe.'mod_forum_posts');
define('TABLE_FORUM_TOPICS',		$prefixe.'mod_forum_topics');
define('TABLE_FORUM_TOPICS_NONLUS',	$prefixe.'mod_forum_topics_nonlus');
define('TABLE_FORUM_TOPICS_SUIVIS',	$prefixe.'mod_forum_topics_suivis');
define('TABLE_FORUM_TOPICS_FAVORIS',$prefixe.'mod_forum_topics_favoris');
define('TABLE_FORUM_TAG', 			$prefixe.'mod_forum_tag');

// Chargement des images de ce module si il y'en a
load_images_mod('forum');

// Chargement des fichiers de langue si il y'en a
load_lang_mod('forum');

require_once($root.'plugins/modules/forum/class_forum.php');
$f = new forum();

// listing de forums mis en cache
$cache->files_cache['listing_forums'] = array($root.'cache/data/liste_forums_'.$module,'global $f; return $f->cache_liste_forums(\''.$module.'\');',$cf->config['cache_duree_forums']);
// listing des tags mis en cache 12H
$cache->files_cache['listing_tags'] = array($root.'cache/data/liste_tags','global $f; return $f->cache_liste_tags();',43200);

// Tests de cohérance des infos saisies dans la conf
if (intval($cf->config['forum_posts_par_topic']) <= 0)		$cf->config['forum_posts_par_topic'] = 20;
if (intval($cf->config['forum_topics_par_forum']) <= 0)		$cf->config['forum_topics_par_forum'] = 20;
if (intval($cf->config['forum_nbre_recents_index']) <= 0)	$cf->config['forum_nbre_recents_index'] = 8;
if (intval($cf->config['forum_nbre_recents_forum']) <= 0)	$cf->config['forum_nbre_recents_forum'] = 6;
?>