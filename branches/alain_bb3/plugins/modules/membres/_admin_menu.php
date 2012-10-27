<?php

global $lang,$liste_pages;
load_lang_mod('membres');
$liste_pages[$lang['L_MENU_MEMBRES']][] = array('plugins/modules/membres/admin_Configuration.php',$lang['L_MENU_MEMBRES_CONFIGURATION']);


?>