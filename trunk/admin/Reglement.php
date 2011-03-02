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
if ( !defined('PROTECT_ADMIN') )
{
	die("Tentative de Hacking");
}
load_lang('register');

if (isset($_POST['reglement']))
{
	//enregistrement du réglement
	$file = @fopen(PATH_REGLEMENT, 'w+');
	@fwrite($file, stripslashes($_POST['reglement']));
	@fclose($file);	
}

$tpl->set_filenames(array(
	'body_admin' => $root.'html/admin_reglement.html'
));

$reglement = (file_exists(PATH_REGLEMENT))? @file_get_contents(PATH_REGLEMENT):$lang['REGLEMENT'];

// On charge le wysiwyg
$WYSIWYG_METHODE = 'html';
if ($cf->config['wysiwyg_editor']!='') include_once($root.'fonctions/fct_'.$cf->config['wysiwyg_editor'].'.php');


$tpl->assign_vars(array(
	'STYLE'							=>	$style_name,
	'REGLEMENT'						=>	$reglement
));
?>