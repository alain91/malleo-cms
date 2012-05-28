<?php
if (!defined('PROTECT'))
{
	die("Tentative de Hacking");
}
$version = (isset($_GET['langue']) && ($_GET['langue']=='en' || $_GET['langue'] == 'fr'))?$_GET['langue']:'fr';
$licence = '';
if (file_exists('../Licence_CeCILL_V2-'.$version.'.txt')){
	$licence =  nl2br(utf8_encode(file_get_contents('../Licence_CeCILL_V2-'.$version.'.txt')));
}

$tpl->assign_vars(array(
	'LICENCE'				=> $licence,
	'L_CHECK'				=> $lang['L_CHECK_LICENCE'],
	'L_LIBELLE_ETAPE1'		=> $lang['L_LIBELLE_ETAPE1'],
	'L_EXPLICATION_LICENCE'	=> $lang['L_EXPLICATION_LICENCE'],
	'L_VERSION_FR'			=> $lang['L_VERSION_FR'],
	'L_VERSION_EN'			=> $lang['L_VERSION_EN'],

));

?>