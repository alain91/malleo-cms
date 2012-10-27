<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS )
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Distributed under the CECILL V2 License
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|
| Please read Licence_CeCILL_V2-en.txt
| SVP lisez Licence_CeCILL_V2-fr.txt
|------------------------------------------------------------------------------------------------------------
*/
if ( !defined('PROTECT'))
{
	die("Tentative de Hacking");
}

$tpl->set_filenames(array('PAGE_HAUT' => $root . $style_path . $style_name.'/_page_haut.html'));

if (((!defined('MESSAGE_DIE') && !defined('ERROR_404')) || !defined('PROTECT_ADMIN')) && (defined('PROTECT_ADMIN') || $cf->config['activer_menuh']))
{
	$tpl->assign_block_vars('menu_horizontal', array());
	include_once($root.'plugins/blocs/menu_horizontal/mod.php');
}

// Navlinks
$navlink='';
if (isset($session) && is_array($session->navlink))
{
	foreach ($session->navlink as $k=>$v)
	{
		if ($navlink!='')$navlink .= ' &raquo;';
		$navlink .='<a href="'.$v[1].'" class="navlink">'.$v[0].'</a>';
	}
}

// Options du module
if (is_array($tpl->options_page)){
	foreach($tpl->options_page as $key=>$val){
		$tpl->assign_block_vars('options_page', array(
			'ICONE'		=> $val['ICONE'],
			'LIBELLE'	=> $val['LIBELLE'],
			'LIEN'		=> $val['LIEN']			
		));
	}
}

// URL canonique
if (!empty($tpl->url_canonique)){
	$tpl->assign_block_vars('url_canonique', array());
}
// Variables globales
$tpl->assign_vars(array(
	'ROOT_STYLE'		=>	$root,
	'ROOT_STYLE_ONGLETS'=> $style_path.$style_name.'/onglets.css',
	'NOM_SITE'			=>	$cf->config['nom_site'],
	'CHARSET'			=>	$cf->config['charset'],
	'STYLE'				=>	$style_name,
	'TITRE_NAVIGATEUR'	=>	(!empty($tpl->titre_navigateur))? $tpl->titre_navigateur.' :: ':'',
	'TITRE_PAGE'		=>	(!empty($tpl->titre_page))? $tpl->titre_page:'',
	'META_DESCRIPTION'	=>	(!empty($tpl->meta_description))? substr(strip_tags(str_replace('\n',' ',str_replace('<br />',' ',$tpl->meta_description))),0,200):$cf->config['description_site'],
	'URL_CANONIQUE'		=>	(!empty($tpl->url_canonique))? 'http://'.$cf->config['adresse_site'].$cf->config['path'].$tpl->url_canonique:'',
	'NAVLINK'			=>	($navlink!='')? $navlink:'',
));

// Switchs groupes
if (is_array($user) && array_key_exists('groupes',$user)){
	foreach($user['groupes'] as $switch_groupe){
		$tpl->assign_block_vars('switch_'.preg_replace('/[^a-z0-9\-_]/i','',$switch_groupe), array());
	}
}
// Titre de la page
if ($tpl->titre_page!='')$tpl->assign_block_vars('titre_page', array());
$tpl->pparse('PAGE_HAUT');
?>
