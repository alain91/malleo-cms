<?php

global $lang,$liste_pages;
load_lang_mod('forum');
$liste_pages[$lang['L_MENU_FORUM']][] = array('plugins/modules/forum/admin_Configuration.php',$lang['L_MENU_FORUM_CONFIGURATION']);
$liste_pages[$lang['L_MENU_FORUM']][] = array('plugins/modules/forum/admin_Categories.php',$lang['L_MENU_FORUM_CATEGORIES']);
$liste_pages[$lang['L_MENU_FORUM']][] = array('plugins/modules/forum/admin_Configuration_Stickit.php',$lang['L_MENU_FORUM_STICKIT_CONFIGURATION']);

?>