<?php

if ( !defined('PROTECT'))
{
	die("Tentative de Hacking");
}
$tpl->set_filenames(array('PAGE_HAUT' => $root . $style_path . $style_name.'/_page_haut.html'));

$tpl->assign_vars(array(
	'ROOT_STYLE'		=>	$root,
	'NOM_SITE'			=>	$lang['L_TITRE_SITE'],
	'CHARSET'			=>	'UTF-8',
	'STYLE'				=>	$style_name,
	'TITRE_NAVIGATEUR'	=>	(!empty($tpl->titre_navigateur))? $tpl->titre_navigateur.' :: ':'',
	'TITRE_PAGE'		=>	(!empty($tpl->titre_page))? $tpl->titre_page:'',
	'META_DESCRIPTION'	=>	'',
));

if ($tpl->titre_page!='')$tpl->assign_block_vars('titre_page', array());
$tpl->pparse('PAGE_HAUT');
?>