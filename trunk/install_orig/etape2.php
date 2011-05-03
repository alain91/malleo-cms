<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}

$liste_dossiers_chmodes = array(
	$root.'cache/',
	$root.'config/config.php',
	$root.'data/',
	$root.'data/avatars/',
	$root.'data/files/',
	$root.'data/images/'
);
$i= 0;

foreach ($liste_dossiers_chmodes as $file){
	if (is_writable($file)){
		$tpl->assign_block_vars('liste_dossiers', array(
			'ICONE'		=> $img['valide'],
			'FICHIER'	=> ereg_replace('\.\.\/','',$file)
		));
	}else{
		$tpl->assign_block_vars('liste_dossiers', array(
			'ICONE'		=> $img['invalide'],
			'FICHIER'	=> ereg_replace('\.\.\/','',$file)
		));
		$i++;
	}
}
$tpl->assign_vars(array(
	'L_EXPLAIN_VERIF_CHMODS'	=> $lang['L_EXPLAIN_VERIF_CHMODS'],
	'L_VERSIONS'				=> $lang['L_VERSIONS'],
	'L_EXTENSIONS'				=> $lang['L_EXTENSIONS'],
	'L_LISTE_DOSSIERS'			=> $lang['L_LISTE_DOSSIERS'],
	'DISABLED'					=> ($i!=0)?' disabled':'',
));

if ($i!=0) $tpl->assign_block_vars('alerte_correction', array());
?>