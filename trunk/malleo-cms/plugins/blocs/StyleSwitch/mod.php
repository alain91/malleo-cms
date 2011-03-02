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
load_lang_bloc('StyleSwitch');

if (isset($_POST['style']) && $user['user_id'] > 1)
{
	$style = eregi_replace('[^a-z0-9_-]','',$_POST['style']);
	if (is_dir('styles/'.$style)){
		$sql = 'UPDATE '.TABLE_USERS.' SET style =\''.$style.'\' 
				WHERE user_id = \'' . $user['user_id'] . '\'' ;
		if (!$resultat = $c->sql_query($sql)) message_die(E_ERROR,1000,__FILE__,__LINE__,$sql);
		// On met a jour le cache
		$cache->appel_cache('infos_user',true);
	}
	header('Location: '.formate_url('',true));
}else{
	$tpl->set_filenames(array(
				'StyleSwitch' => $root.'plugins/blocs/StyleSwitch/html/mod_style_switch.html'
	));	
	$chemin = 'styles/';
	$liste = array();
	$liste_styles = '';
	$ch = @opendir($chemin);
	while ($style = @readdir($ch))
	{
		if ($style != '.' && $style != '..' && is_dir($chemin.$style)) {
			$liste[] = $style;
		}
	}
	@closedir($ch);
	
	sort($liste);
	foreach($liste as $key=>$style){
		$selected = ($style == $user['style'])?' selected="selected"':'';
		$liste_styles .= "\n ".'<option'.$selected.'>'.$style.'</option>';
	}	
	
	$tpl->assign_vars( array(
			'S_STYLES'		=> $liste_styles,
	));
}
?>