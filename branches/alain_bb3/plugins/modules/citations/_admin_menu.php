<?php

global $lang,$liste_pages;
load_lang_mod('citations');
$liste_pages[$lang['L_MENU_CITATIONS']][] = array('plugins/modules/citations/admin_Configuration.php',$lang['L_MENU_CITATIONS_CONFIGURATION']);
$liste_pages[$lang['L_MENU_CITATIONS']][] = array('plugins/modules/citations/admin_Categories.php',$lang['L_MENU_CITATIONS_CATEGORIES']);

?>