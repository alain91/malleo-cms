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
global $module;
$tpl->set_filenames(array(
	'messagerie_alerte_message'=> $root.'plugins/blocs/messagerie_alerte_message/html/mod_message_prive.html'
));
		
if ($user['user_id']>1 && $module != 'messagerie'){	
	// Chargement OUTILS
	require_once($root.'plugins/modules/messagerie/prerequis.php');
	if ($mp->nbre_messages_nonlu()>0){
		$mp->lister_boite_reception();		
	}
}
?>