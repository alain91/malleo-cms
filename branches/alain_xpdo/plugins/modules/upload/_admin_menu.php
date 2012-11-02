<?php

global $lang,$liste_pages;
load_lang_mod('upload');
$liste_pages[$lang['L_MENU_UPLOAD']][] = array('plugins/modules/upload/admin_Configuration.php',$lang['L_MENU_UPLOAD_CONFIGURATION']);

?>