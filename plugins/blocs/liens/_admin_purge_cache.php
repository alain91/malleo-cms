<?php

global $lang,$root,$cache;
load_lang_bloc('liens');
$cache->ajouter_fichier_purge('bloc_liens',	$lang['DESTROY_CACHE_BLOC_LIENS'],	$root.'cache/bloc_liens/',0);


?>