<?php

global $lang,$root,$cache;
load_lang_bloc('menu_horizontal');
$cache->ajouter_fichier_purge('bloc_menu_horizontal',	$lang['DESTROY_CACHE_BLOC_MENU'],	$root.'cache/bloc_menu_horizontal/',0);


?>