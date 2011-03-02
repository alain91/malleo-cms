<?php

global $lang,$liste_pages;
load_lang_mod('profil');
$liste_pages[$lang['L_MENU_PROFIL']][] = array('plugins/modules/profil/admin_Categories.php',$lang['L_MENU_PROFIL_CATEGORIES']);
$liste_pages[$lang['L_MENU_PROFIL']][] = array('plugins/modules/profil/_Config_Generale.php',$lang['L_MENU_PROFILS']);


?>