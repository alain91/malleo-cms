<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
//Compteur d'erreurs
$i = 0;

//
// Extensions
$phpExtnecessaires = array('gd','mbstring','mysql','dom');
$loaded_extensions = get_loaded_extensions();
foreach ($phpExtnecessaires as $extension){
	if (in_array($extension,$loaded_extensions)){
		$tpl->assign_block_vars('liste_extensions', array(
			'ICONE'	=> $img['valide'],
			'EXT'	=> $lang['L_EXT_EXPLAIN_'.$extension]
		));
	}else{
		$tpl->assign_block_vars('liste_extensions', array(
			'ICONE'	=> $img['invalide'],
			'EXT'	=> $lang['L_EXT_EXPLAIN_'.$extension]			
		));
		$i++;
	}
}


//
// PHP
if (phpVersion() > 5.0){
		$tpl->assign_block_vars('liste_versions', array(
			'ICONE'	=> $img['valide'],
			'EXT'	=> sprintf($lang['PHP'],phpVersion())
		));
}else{
		$tpl->assign_block_vars('liste_versions', array(
			'ICONE'	=> $img['invalide'],
			'EXT'	=> sprintf($lang['PHP'],phpVersion())		
		));
		$i++;
}

//
// MySQL
if (in_array('mysql',$loaded_extensions)){
	preg_match('/[1-9]\.[0-9]\.?[1-9]?[0-9]?/', mysql_get_client_info(), $match);
	$mysql_ver = (isset($match[0])) ? $match[0] : mysql_get_client_info();
	if ($mysql_ver >= 4.2){
			$tpl->assign_block_vars('liste_versions', array(
				'ICONE'	=> $img['valide'],
				'EXT'	=> sprintf($lang['MySQL'], $mysql_ver)
			));
	}else{
			$tpl->assign_block_vars('liste_versions', array(
				'ICONE'	=> $img['invalide'],
				'EXT'	=> sprintf($lang['MySQL'], $mysql_ver)	
			));
			$i++;
	}
}else{
	$tpl->assign_block_vars('liste_versions', array(
		'ICONE'	=> $img['invalide'],
		'EXT'	=> sprintf($lang['MySQL'],$lang['L_MySQL_NON_INSTALLE'])	
	));
	$i++;
}

//
// Fonctions
$disable_functions = (ini_get("disable_functions")!="" AND ini_get("disable_functions")!=false) ? array_map('trim', preg_split( "/[\s,]+/", ini_get("disable_functions"))) : array();
if(function_exists("fsockopen") AND !in_array('fsockopen', $disable_functions))
{
		$tpl->assign_block_vars('liste_fonctions', array(
			'ICONE'	=> $img['valide'],
			'EXT'	=> $lang['L_FSOCKOPEN_NON_INSTALLE']
		));
}else{
		$tpl->assign_block_vars('liste_fonctions', array(
			'ICONE'	=> $img['invalide'],
			'EXT'	=> $lang['L_FSOCKOPEN_NON_INSTALLE']
		));
}
$tpl->assign_vars(array(
	'L_EXPLAIN_VERIF_VERSIONS'	=> $lang['L_EXPLAIN_VERIF_VERSIONS'],
	'L_VERSIONS'				=> $lang['L_VERSIONS'],
	'L_EXTENSIONS'				=> $lang['L_EXTENSIONS'],
	'L_FONCTIONS'				=> $lang['L_FONCTIONS'],
	'DISABLED'					=> ($i!=0)?' disabled':'',
));

if ($i!=0) $tpl->assign_block_vars('alerte_correction', array());
?>