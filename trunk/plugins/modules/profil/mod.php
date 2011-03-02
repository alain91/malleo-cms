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
require_once($root.'plugins/modules/profil/prerequis.php');

if (isset($_GET['user_id'])){
	$profil->user_id = $user_id = intval($_GET['user_id']);
}elseif($user['user_connecte']==1){
	$profil->user_id = $user_id = $user['user_id'];
}else{
	error404();
}
if (!$droits->check($module,0,'voir') || $user_id<2){
	error404(1030);
}

//
// Différents Modes de fonctionnements

$mode = null;
if (isset($_GET['mode']) ||isset($_POST['mode']))
{
	$mode = (isset($_POST['mode']))?$_POST['mode']:$_GET['mode'];
}
switch ($mode){
	case 'categorie':
		$tpl->set_filenames(array('profil' => $root.'plugins/modules/profil/html/edition_categorie.html'));
		include($root.'plugins/modules/profil/inc/inc_categorie.php');
		break;
	case 'configuration':
		$tpl->set_filenames(array('profil' => $root.'plugins/modules/profil/html/edition_configuration.html'));
		include($root.'plugins/modules/profil/inc/inc_configuration.php');
		break;
	case 'visu':
	default:
		include($root.'plugins/modules/profil/inc/inc_visualisation.php');
		break;
}

?>