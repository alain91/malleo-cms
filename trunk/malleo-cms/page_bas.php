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
global $c, $startime, $lang;

$tpl->set_filenames(array('PAGE_BAS' => $root . $style_path . $style_name.'/_page_bas.html'));

$tpl->assign_vars(array(
	'INFOS_SQL' => ''
));
	
if (!defined('MESSAGE_DIE') && $user['level'] > 9)
{
	// Stats SQL
	$excuted_queries = $c->num_queries;
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$endtime = $mtime[1] + $mtime[0];
	$gentime = round(($endtime - $startime), 4);

	$tpl->assign_vars(array(
		'INFOS_SQL' => sprintf($lang['INFOS_SQL'],$gentime,$excuted_queries)
	));
}
$c->sql_close();

$tpl->pparse('PAGE_BAS');
?>