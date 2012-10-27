<?php
/*
|------------------------------------------------------------------------------------------------------------
| Software: Arcade Flash pour Malleo (CMS)
| Contact:  SP - http://www.malleo-cms.com
| Support: http://www.malleo-cms.com?module=forum
|  Documentation : Support: http://www.malleo-cms.com?module=wiki
|------------------------------------------------------------------------------------------------------------
|  Author: Stephane RAJALU
|  Copyright (c) 2008-2009, Stephane RAJALU All Rights Reserved
|------------------------------------------------------------------------------------------------------------
|  License: Not Licenced / Author Copy
|  This program is distributed in the hope that it will be useful - WITHOUT 
|  ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
|  FITNESS FOR A PARTICULAR PURPOSE. 
|------------------------------------------------------------------------------------------------------------
*/

// Securite 
if ( !defined('PROTECT') ) 	die("Tentative de Hacking");
if (!$droits->check($module,0,'voir')) {  error404(1303); exit; }

// Init
require_once($root.'plugins/modules/arcade/prerequis.php');
include_once($root.'plugins/modules/messagerie/prerequis.php');
if (!isset($arcade)){
	$arcade = new arcade();
	$arcade->module=$module;
	$arcade->Get_config();
}

// Mode de fonctionnement
$arcade->select_mod_fonctionnement($arcade->mode);
?>