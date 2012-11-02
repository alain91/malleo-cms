<?php
global $lang,$liste_pages;
load_lang_mod('annonces');
$liste_pages[$lang['L_MENU_ANNONCES']][] = array('plugins/modules/annonces/admin_Configuration.php',$lang['L_MENU_ANNONCES_CONFIGURATION']);
$liste_pages[$lang['L_MENU_ANNONCES']][] = array('plugins/modules/annonces/admin_Categories.php',$lang['L_MENU_ANNONCES_CATEGORIES']);

?>