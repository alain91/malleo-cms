<?php

global $lang,$root,$cache;
load_lang_mod('profil');
$cache->ajouter_fichier_purge('profil',	$lang['DESTROY_CACHE_ADRESSES'],	$root.'cache/adresses/',0);


?>