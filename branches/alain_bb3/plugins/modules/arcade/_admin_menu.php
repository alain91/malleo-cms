<?php

global $lang,$liste_pages;
load_lang_mod('arcade');
$liste_pages[$lang['L_MENU_ARCADE']][] = array('plugins/modules/arcade/admin_arcade_configuration.php',$lang['L_MENU_ARCADE_CONFIGURER']);
$liste_pages[$lang['L_MENU_ARCADE']][] = array('plugins/modules/arcade/admin_arcade_modules.php',$lang['L_MENU_ARCADE_MODULES']);
$liste_pages[$lang['L_MENU_ARCADE']][] = array('plugins/modules/arcade/admin_arcade_categories.php',$lang['L_MENU_ARCADE_CATEGORIES']);
$liste_pages[$lang['L_MENU_ARCADE']][] = array('plugins/modules/arcade/admin_arcade_triche.php',$lang['L_MENU_ARCADE_TRICHE']);
$liste_pages[$lang['L_MENU_ARCADE']][] = array('plugins/modules/arcade/admin_arcade_installation.php',$lang['L_MENU_ARCADE_INSTALLER_JEUX']);


?>