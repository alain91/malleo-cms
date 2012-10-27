<?php
define('PROTECT',true);
$root = '../../';
require_once($root.'/chargement.php');
$style_name=load_style();
$tpl->gzip = (array_key_exists('HTTP_ACCEPT_ENCODING',$_SERVER) && strstr($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip'))? true:false;
$lang=$erreur=array();
load_lang('defaut');
if (isset($_POST['texte']))
{
	include_once($root.'class/class_posting.php');
	$post=new posting();
	
	$tpl->set_filenames(array('body'=>$root.'html/tinymce_apercu.html'));
	$tpl->assign_vars(array(
		'APERCU'	=> $post->bbcode2html(stripslashes(protection_chaine($_POST['texte']))),
		'L_APERCU'	=> $lang['L_APERCU']
	));
	include_once($root.'page_haut.php');
	$tpl->contenu_page = preg_replace('/href="..\/..\/styles\//','href="styles/',$tpl->contenu_page);
	$tpl->pparse('body');
	include_once($root.'page_bas.php');
	die($tpl->contenu_page);	
}
die();
?>
