<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Malleo ( CMS ) - Module Annonces
| Contact:  alain91 - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|------------------------------------------------------------------------------------------------------------
|  Author: Alain GANDON
|  Copyright (c) 2011, Alain GANDON All Rights Reserved
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
defined('PROTECT') OR die("Tentative de Hacking");

$id_version = 0;
global $module,$regles;

global $annonces;

require_once(dirname(__FILE__).'/prerequis.php');
// Autorisations
if (!$droits->check($module,0,'voir')){
	error404(903);
}
$mode = null;
if(isset($_GET['mode'])||isset($_POST['mode'])){
	$mode=(isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
}

// PAGE PRECISEE

if (isset($_GET['t']) && $mode != 'voir'){
	if ((strlen($down->t) > 0)){
		$mode = 'files';
	}elseif(strlen($down->t)  == 0){
		$mode = 'cat';
	}
// Enregistrement
}elseif($mode == 'idcat'){
	$down->idcat();
// Page par defaut
}elseif($mode=='idfile'){
	$down->idfile();
}

if($mode==null){$mode = 'voir';}

// Navlinks
$session->make_navlinks(array(
	$module	=> formate_url('',true),
	$mode	=> formate_url($mode.'='.$id,true),
));

// raccourcis		
switch ($mode)
{
	case 'cat':
		$down -> cat();
		break;

	case 'files' :
		$down -> files();
		break;

	case 'idfile':
		break;

	case 'idcat':
		break;

	default :
		$down -> cat();
		break;
}
if ($droits->check($module,3,'ecrire'))//si le membre a les droits d'écriture
{	
	$tpl->options_page[]= array(
			'ICONE'		=> $img['document'],
			'LIBELLE'	=> $lang['L_ADD'],
			'LIEN'		=> formate_url('mode=',true));
	$tpl->options_page[]= array(
			'ICONE'		=> $img['document'],
			'LIBELLE'	=> $lang['L_ADDC'],
			'LIEN'		=> formate_url('mode=',true));
}
$tpl->options_page[]= array(
			'ICONE'		=> $img['mini_icon_details'],
			'LIBELLE'	=> $lang['L_ALLF'],
			'LIEN'		=> formate_url('mode=files',true));
?>