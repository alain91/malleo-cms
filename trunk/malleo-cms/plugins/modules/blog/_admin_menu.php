<?php

global $lang,$liste_pages;
load_lang_mod('blog');
$liste_pages[$lang['L_MENU_BLOG']][] = array('plugins/modules/blog/admin_Configuration.php',$lang['L_MENU_BLOG_CONFIGURATION']);
$liste_pages[$lang['L_MENU_BLOG']][] = array('plugins/modules/blog/admin_Categories.php',$lang['L_MENU_BLOG_CATEGORIES']);


?>