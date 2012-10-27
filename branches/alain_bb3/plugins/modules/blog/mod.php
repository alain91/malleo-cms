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
if ( !defined('PROTECT') )
{
	die("Tentative de Hacking");
}
require_once($root.'plugins/modules/blog/prerequis.php');
if (!$droits->check($module,0,'voir'))
{
	error404(517);
	exit;
}

$mode = null;
if (isset($_GET['mode']) ||isset($_POST['mode']))
{
	$mode = (isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
}

if ($droits->check($module,0,'poster')){
	$tpl->options_page = array(
			3=>array(
			'ICONE'		=> $img['blog_nouveau_billet'],
			'LIBELLE'	=> $lang['L_NOUVEAU'],
			'LIEN'		=> formate_url('mode=saisie',true))
	);
} 

include_once($root.'class/class_posting.php');
$post = new posting();
$post->module=$module;

switch ($mode)
{
	case 'saisie': include($root.'plugins/modules/blog/inc/inc_saisie.php');break;
	case 'billet': include($root.'plugins/modules/blog/inc/inc_billet.php');break;
	case 'liste': 
	default:       include($root.'plugins/modules/blog/inc/inc_liste.php');break;
} 


?>