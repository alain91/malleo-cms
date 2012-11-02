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
load_lang('modeles');

if (isset($_POST['RequeteAjax']))
{
	if (preg_match('/HTML_/',$_POST['RequeteAjax']))
	{
		// BLOC HTML
		$id_bloc_html = intval(preg_replace('/HTML_/','',$_POST['RequeteAjax']));
		include_once($root.'plugins/blocs/html/mod.php');
		$tpl->pparse('HTML_'.$id_bloc_html,true);
	}elseif (file_exists($root.'plugins/blocs/'.$_POST['RequeteAjax'].'/mod.php'))
	{
		include_once($root.'plugins/blocs/'.$_POST['RequeteAjax'].'/mod.php');
		$tpl->pparse($_POST['RequeteAjax'],true);
	}
	if (trim($tpl->buffer) == '')
	{
		$tpl->set_filenames(array(
			  'body_admin' => $root.'html/admin_modele_bloc_vide.html'
		));
		$tpl->assign_vars(array(
			'L_TITRE'	=> $_POST['RequeteAjax'],
			'L_MESSAGE'	=> $lang['L_ALERTE_BLOC_VIDE']
		));
		$tpl->pparse('body_admin',true);
		echo $tpl->buffer;
	}else{
		echo $tpl->buffer;
	}
	
}elseif(isset($_POST['SaveMAP'])){

	include($root.'class/class_assemblage.php');
	$map = new Assemblage();

	$liste_blocs = explode('|',$_POST['SaveMAP']);
	foreach ($liste_blocs as $key=>$val)
	{
		if ($val != '')
		{
			$r = explode(':',$val);
			$plan[$r[0]][] = $r[1];
		}
	}
	// Enregistrement coté SQL
	$sql  = 'UPDATE '.TABLE_MODELES.' SET map=\''.serialize($plan).'\' WHERE id_modele='.$_POST['id_modele'];
	if (!$res=$c->sql_query($sql)) message_die(E_ERROR,21,__FILE__,__LINE__,$sql);
	$sql  = 'SELECT u.module 
			FROM '.TABLE_MODULES.' as u LEFT JOIN   '.TABLE_MODELES.' AS e
			ON (u.modele=e.id_modele) 
			WHERE e.id_modele='.$_POST['id_modele'];
	if (!$res=$c->sql_query($sql)) message_die(E_ERROR,21,__FILE__,__LINE__,$sql);
	if ($c->sql_numrows($res) > 0)
	{
		// Enregistrement des pages utilisant ce modèle
		while ($row = $c->sql_fetchrow($res))
		{
			$map->module = $row['module'];
			$map->Cache_Template($row['module'],true);
		}
	}
	$cache->appel_cache('listing_plugins',true);
	die($lang['SAVE_OK']);
}

?>
