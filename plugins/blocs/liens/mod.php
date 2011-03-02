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
global $cache;
require($root.'plugins/blocs/liens/prerequis.php');

$tpl->set_filenames(array(
	  'liens' => $root.'plugins/blocs/liens/html/mod_liens.html'
));
$tpl->set_filenames(array(
	  'contenu' => $cache->cache_tpl(CHEMIN_LIENS.FICHIER_LIENS, 'return creer_cache_mod_lien();', 86400)
));
$tpl->assign_var_from_handle('CONTENU','contenu');

	
?>