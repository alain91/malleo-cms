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
if ($module=='') $module='wiki';
require_once($root.'plugins/modules/wiki/prerequis.php');
load_lang_bloc('wiki_pages_recentes');

$tpl->set_filenames(array(
	  'wiki_pages_recentes' => $root.'plugins/blocs/wiki_pages_recentes/html/bloc_wiki_pages_recentes.html'
));

$sql = 'SELECT titre, tag  
		FROM '.TABLE_WIKI.' as w
		LEFT JOIN '.TABLE_WIKI_TEXTE.' as t
		ON (w.id_version_actuelle=t.id_version) 
		WHERE module="'.$module.'" 
		ORDER BY date DESC
		LIMIT 10';
if (!$resultat = $c->sql_query($sql))message_die(E_ERROR,702,__FILE__,__LINE__,$sql);
while($row = $c->sql_fetchrow($resultat)){
	$tpl->assign_block_vars('wiki_pages_recentes', array(
		'TITRE'	=> $row['titre'],
		'LIEN'	=> formate_url('t='.$row['tag'],true)
	));
}
?>